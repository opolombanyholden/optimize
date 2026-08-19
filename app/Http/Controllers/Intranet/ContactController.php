<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\ContactRequest;
use App\Models\Intranet\Contact;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\Pays;
use App\Models\Intranet\PieceJointe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    private const FOLDER = 'intranet/contacts';

    public function index(Request $request)
    {
        $contacts = Contact::with(['organisation', 'pays'])
            ->recherche($request->input('q'))
            ->when($request->filled('organisation'), fn($q) => $q->where('organisation_id', $request->organisation))
            ->when($request->filled('etiquette'),    fn($q) => $q->where('etiquette', $request->etiquette))
            ->when($request->filled('pays'),         fn($q) => $q->where('pays_id', $request->pays))
            ->when($request->boolean('favoris'),     fn($q) => $q->favoris())
            ->orderByDesc('est_favori')
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('intranet.contacts.index', [
            'contacts'      => $contacts,
            'organisations' => ContactOrganisation::orderBy('nom')->get(['id', 'nom']),
            'pays'          => Pays::orderBy('ordre')->orderBy('nom')->get(),
        ]);
    }

    public function create(Request $request)
    {
        return view('intranet.contacts.create', [
            'contact'       => new Contact(['langue' => 'fr']),
            'organisations' => ContactOrganisation::orderBy('nom')->get(['id', 'nom']),
            'pays'          => Pays::orderBy('ordre')->orderBy('nom')->get(),
            'users'         => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'       => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'   => [],
            'ciblesGroupes' => [],
        ]);
    }

    public function store(ContactRequest $request)
    {
        $contact = DB::transaction(function () use ($request) {
            $data = $request->safe()->except([
                'photo', 'media_principal', 'pieces_jointes', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags']       = $request->tagsArray();
            $data['created_by'] = auth()->id();

            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store(self::FOLDER, 'public');
            }
            if ($request->hasFile('media_principal')) {
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $contact = Contact::create($data);

            if ($request->hasFile('pieces_jointes')) {
                $contact->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $contact->id);
            }

            $this->synchroniserPublication($contact, $request);
            return $contact;
        });

        return redirect()->route('intranet.contacts.show', $contact)
            ->with('success', 'Contact créé.');
    }

    public function show(Contact $contact)
    {
        $contact->load(['organisation.secteur', 'pays', 'opportunites.etape', 'interactions.realisateur', 'publication.cibles', 'piecesJointes']);

        if (auth()->check()) {
            $contact->enregistrerVue();
            $contact->increment('vues_count');
        }

        return view('intranet.contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        $contact->load(['publication.cibles', 'piecesJointes']);
        $ciblesUsers   = $contact->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $contact->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return view('intranet.contacts.edit', [
            'contact'       => $contact,
            'organisations' => ContactOrganisation::orderBy('nom')->get(['id', 'nom']),
            'pays'          => Pays::orderBy('ordre')->orderBy('nom')->get(),
            'users'         => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'       => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'   => $ciblesUsers,
            'ciblesGroupes' => $ciblesGroupes,
        ]);
    }

    public function update(ContactRequest $request, Contact $contact)
    {
        DB::transaction(function () use ($request, $contact) {
            $data = $request->safe()->except([
                'photo', 'media_principal', 'pieces_jointes', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags'] = $request->tagsArray();

            if ($request->hasFile('photo')) {
                if ($contact->photo) Storage::disk('public')->delete($contact->photo);
                $data['photo'] = $request->file('photo')->store(self::FOLDER, 'public');
            }
            if ($request->hasFile('media_principal')) {
                if ($contact->media_principal) Storage::disk('public')->delete($contact->media_principal);
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $contact->update($data);

            if ($request->hasFile('pieces_jointes')) {
                $contact->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $contact->id);
            }

            $this->synchroniserPublication($contact, $request);
        });

        return redirect()->route('intranet.contacts.show', $contact)
            ->with('success', 'Contact mis à jour.');
    }

    public function destroyPieceJointe(Contact $contact, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $contact->id && $piece->attachable_type === Contact::class, 404);
        $contact->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    private function categorieMedia(?string $mime): string
    {
        if (! $mime) return 'document';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'document';
    }

    private function synchroniserPublication(Contact $contact, ContactRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) {
            $cibles[] = ['type' => 'user', 'id' => (int) $userId];
        }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) {
            $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId];
        }

        $contact->publier(
            $request->input('visibilite', 'prive'),
            $cibles,
            [
                'likes_actifs'        => $request->boolean('likes_actifs'),
                'commentaires_actifs' => $request->boolean('commentaires_actifs'),
                'publie_le'           => now(),
            ]
        );
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('intranet.contacts.index')
            ->with('success', 'Contact supprimé.');
    }

    public function toggleFavori(Contact $contact)
    {
        $contact->update(['est_favori' => ! $contact->est_favori]);
        return back();
    }
}
