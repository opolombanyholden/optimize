<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\ContactOrganisationRequest;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\Pays;
use App\Models\Intranet\PieceJointe;
use App\Models\Intranet\SecteurActivite;
use App\Models\Intranet\TypeDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContactOrganisationController extends Controller
{
    private const FOLDER = 'intranet/organisations';

    public function index(Request $request)
    {
        $organisations = ContactOrganisation::with(['secteur', 'pays'])
            ->withCount(['contacts', 'opportunites'])
            ->recherche($request->input('q'))
            ->type($request->input('type'))
            ->when($request->filled('secteur'),  fn($q) => $q->where('secteur_id', $request->secteur))
            ->when($request->filled('pays'),     fn($q) => $q->where('pays_id', $request->pays))
            ->when($request->filled('taille'),   fn($q) => $q->where('taille', $request->taille))
            ->when($request->filled('statut'), function ($q) use ($request) {
                if ($request->statut === 'client')   $q->clients();
                if ($request->statut === 'prospect') $q->prospects();
            })
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        $comptes = ContactOrganisation::selectRaw('type, count(*) as n')
            ->groupBy('type')->pluck('n', 'type');

        return view('intranet.organisations.index', [
            'organisations' => $organisations,
            'secteurs'      => SecteurActivite::orderBy('ordre')->get(),
            'pays'          => Pays::orderBy('ordre')->orderBy('nom')->get(),
            'comptes'       => $comptes,
            'typeActif'     => $request->input('type'),
        ]);
    }

    public function create(Request $request)
    {
        $type = $request->input('type', 'autre');
        if (!array_key_exists($type, ContactOrganisation::TYPES)) $type = 'autre';

        return view('intranet.organisations.create', [
            'organisation' => new ContactOrganisation(['est_prospect' => true, 'type' => $type, 'statut' => 1]),
            'secteurs'     => SecteurActivite::orderBy('ordre')->get(),
            'pays'         => Pays::orderBy('ordre')->orderBy('nom')->get(),
            'users'        => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'      => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'  => [],
            'ciblesGroupes'=> [],
            'previewDocumentsParType' => $this->previewDocumentsParType(),
        ]);
    }

    /**
     * Retourne la liste des documents attendus par type d'organisation
     * (pour prévisualisation JS dans les formulaires create/edit).
     */
    private function previewDocumentsParType(): array
    {
        $preview = [];
        foreach (array_keys(ContactOrganisation::TYPES) as $typeOrg) {
            $preview[$typeOrg] = TypeDocument::actif()
                ->with(['exigences' => fn($q) => $q->where('type_organisation', $typeOrg)])
                ->whereHas('exigences', fn($q) => $q->where('type_organisation', $typeOrg))
                ->orderBy('ordre')->orderBy('libelle')
                ->get()
                ->map(fn($t) => [
                    'libelle'     => $t->libelle,
                    'icone'       => $t->icone,
                    'obligatoire' => $t->estObligatoirePour($typeOrg),
                ])->values()->all();
        }
        return $preview;
    }

    public function store(ContactOrganisationRequest $request)
    {
        $orga = DB::transaction(function () use ($request) {
            $data = $request->safe()->except([
                'logo', 'media_principal', 'pieces_jointes', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags']       = $request->tagsArray();
            $data['created_by'] = auth()->id();

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store(self::FOLDER, 'public');
            }
            if ($request->hasFile('media_principal')) {
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $orga = ContactOrganisation::create($data);

            if ($request->hasFile('pieces_jointes')) {
                $orga->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $orga->id);
            }

            $this->synchroniserPublication($orga, $request);
            return $orga;
        });

        return redirect()->route('intranet.organisations.show', $orga)
            ->with('success', 'Organisation créée.');
    }

    public function show(ContactOrganisation $organisation)
    {
        $organisation->load([
            'secteur', 'pays',
            'contacts.pays',
            'opportunites.etape',
            'interactions.realisateur',
            'publication.cibles',
            'piecesJointes',
            'documents.typeDocument', 'documents.uploader',
        ]);

        if (auth()->check()) {
            $organisation->enregistrerVue();
            $organisation->increment('vues_count');
        }

        return view('intranet.organisations.show', compact('organisation'));
    }

    public function edit(ContactOrganisation $organisation)
    {
        $organisation->load(['publication.cibles', 'piecesJointes', 'documents.typeDocument']);
        $ciblesUsers   = $organisation->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $organisation->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return view('intranet.organisations.edit', [
            'organisation' => $organisation,
            'secteurs'     => SecteurActivite::orderBy('ordre')->get(),
            'pays'         => Pays::orderBy('ordre')->orderBy('nom')->get(),
            'users'        => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'      => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'  => $ciblesUsers,
            'ciblesGroupes'=> $ciblesGroupes,
            'previewDocumentsParType' => $this->previewDocumentsParType(),
        ]);
    }

    public function update(ContactOrganisationRequest $request, ContactOrganisation $organisation)
    {
        DB::transaction(function () use ($request, $organisation) {
            $data = $request->safe()->except([
                'logo', 'media_principal', 'pieces_jointes', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags'] = $request->tagsArray();

            if ($request->hasFile('logo')) {
                if ($organisation->logo) Storage::disk('public')->delete($organisation->logo);
                $data['logo'] = $request->file('logo')->store(self::FOLDER, 'public');
            }
            if ($request->hasFile('media_principal')) {
                if ($organisation->media_principal) Storage::disk('public')->delete($organisation->media_principal);
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $organisation->update($data);

            if ($request->hasFile('pieces_jointes')) {
                $organisation->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $organisation->id);
            }

            $this->synchroniserPublication($organisation, $request);
        });

        return redirect()->route('intranet.organisations.show', $organisation)
            ->with('success', 'Organisation mise à jour.');
    }

    public function destroyPieceJointe(ContactOrganisation $organisation, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $organisation->id && $piece->attachable_type === ContactOrganisation::class, 404);
        $organisation->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    private function categorieMedia(?string $mime): string
    {
        if (! $mime) return 'document';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'document';
    }

    private function synchroniserPublication(ContactOrganisation $orga, ContactOrganisationRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) {
            $cibles[] = ['type' => 'user', 'id' => (int) $userId];
        }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) {
            $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId];
        }

        $orga->publier(
            $request->input('visibilite', 'prive'),
            $cibles,
            [
                'likes_actifs'        => $request->boolean('likes_actifs'),
                'commentaires_actifs' => $request->boolean('commentaires_actifs'),
                'publie_le'           => now(),
            ]
        );
    }

    public function destroy(ContactOrganisation $organisation)
    {
        $organisation->delete();
        return redirect()->route('intranet.organisations.index')
            ->with('success', 'Organisation supprimée.');
    }
}
