<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\OpportuniteRequest;
use App\Models\Intranet\Contact;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Intranet\CrmEtape;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\Opportunite;
use App\Models\Intranet\PieceJointe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OpportuniteController extends Controller
{
    private const FOLDER = 'intranet/opportunites';

    public function index(Request $request)
    {
        $opportunites = Opportunite::with(['contact', 'organisation', 'etape', 'responsable'])
            ->recherche($request->input('q'))
            ->when($request->filled('etape'),       fn($q) => $q->where('etape_id', $request->etape))
            ->when($request->filled('responsable'), fn($q) => $q->where('responsable_id', $request->responsable))
            ->when($request->filled('statut'),      fn($q) => $q->where('statut', $request->statut))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('intranet.opportunites.index', [
            'opportunites' => $opportunites,
            'etapes'       => CrmEtape::orderBy('ordre')->get(),
            'responsables' => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']),
        ]);
    }

    public function pipeline(Request $request)
    {
        $etapes = CrmEtape::with(['opportunites' => function ($q) use ($request) {
            $q->with(['contact', 'organisation', 'responsable'])
              ->when($request->filled('responsable'), fn($q) => $q->where('responsable_id', $request->responsable))
              ->when($request->boolean('mes_opp'),    fn($q) => $q->where('responsable_id', auth()->id()))
              ->orderBy('ordre_kanban');
        }])->orderBy('ordre')->get();

        return view('intranet.opportunites.pipeline', [
            'etapes'       => $etapes,
            'responsables' => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']),
        ]);
    }

    public function moveStage(Request $request, Opportunite $opportunite)
    {
        $request->validate([
            'etape_id'      => ['required', 'exists:intranet_crm_etapes,id'],
            'ordre_kanban'  => ['nullable', 'integer'],
        ]);

        $etape = CrmEtape::find($request->etape_id);
        $data  = ['etape_id' => $etape->id, 'ordre_kanban' => $request->ordre_kanban ?? 0];

        if ($etape->est_gagnee) {
            $data['statut'] = 'gagnee';
            $data['date_cloture_reelle'] = now()->toDateString();
        } elseif ($etape->est_perdue) {
            $data['statut'] = 'perdue';
            $data['date_cloture_reelle'] = now()->toDateString();
        } else {
            $data['statut'] = 'ouvert';
        }

        $opportunite->update($data);
        return response()->json(['ok' => true]);
    }

    public function create()
    {
        return view('intranet.opportunites.create', [
            'opportunite'   => new Opportunite([
                'devise'      => 'XAF',
                'probabilite' => 25,
                'statut'      => 'ouvert',
            ]),
            'etapes'        => CrmEtape::orderBy('ordre')->get(),
            'contacts'      => Contact::orderBy('nom')->get(['id', 'nom', 'prenoms', 'email']),
            'organisations' => ContactOrganisation::orderBy('nom')->get(['id', 'nom']),
            'responsables'  => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']),
            'users'         => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'       => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'   => [],
            'ciblesGroupes' => [],
        ]);
    }

    public function store(OpportuniteRequest $request)
    {
        $opp = DB::transaction(function () use ($request) {
            $data = $request->safe()->except([
                'media_principal', 'pieces_jointes', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags']       = $request->tagsArray();
            $data['created_by'] = auth()->id();

            if ($request->hasFile('media_principal')) {
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $opp = Opportunite::create($data);

            if ($request->hasFile('pieces_jointes')) {
                $opp->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $opp->id);
            }

            $this->synchroniserPublication($opp, $request);
            return $opp;
        });

        return redirect()->route('intranet.opportunites.show', $opp)
            ->with('success', 'Opportunité créée.');
    }

    public function show(Opportunite $opportunite)
    {
        $opportunite->load(['contact', 'organisation', 'etape', 'responsable', 'auteur', 'interactions.realisateur', 'publication.cibles', 'piecesJointes']);

        if (auth()->check()) {
            $opportunite->enregistrerVue();
            $opportunite->increment('vues_count');
        }

        return view('intranet.opportunites.show', compact('opportunite'));
    }

    public function edit(Opportunite $opportunite)
    {
        $opportunite->load(['publication.cibles', 'piecesJointes']);
        $ciblesUsers   = $opportunite->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $opportunite->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return view('intranet.opportunites.edit', [
            'opportunite'   => $opportunite,
            'etapes'        => CrmEtape::orderBy('ordre')->get(),
            'contacts'      => Contact::orderBy('nom')->get(['id', 'nom', 'prenoms', 'email']),
            'organisations' => ContactOrganisation::orderBy('nom')->get(['id', 'nom']),
            'responsables'  => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']),
            'users'         => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'       => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'   => $ciblesUsers,
            'ciblesGroupes' => $ciblesGroupes,
        ]);
    }

    public function update(OpportuniteRequest $request, Opportunite $opportunite)
    {
        DB::transaction(function () use ($request, $opportunite) {
            $data = $request->safe()->except([
                'media_principal', 'pieces_jointes', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags'] = $request->tagsArray();

            if ($request->hasFile('media_principal')) {
                if ($opportunite->media_principal) Storage::disk('public')->delete($opportunite->media_principal);
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $opportunite->update($data);

            if ($request->hasFile('pieces_jointes')) {
                $opportunite->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $opportunite->id);
            }

            $this->synchroniserPublication($opportunite, $request);
        });

        return redirect()->route('intranet.opportunites.show', $opportunite)
            ->with('success', 'Opportunité mise à jour.');
    }

    public function destroy(Opportunite $opportunite)
    {
        $opportunite->delete();
        return redirect()->route('intranet.opportunites.index')
            ->with('success', 'Opportunité supprimée.');
    }

    public function destroyPieceJointe(Opportunite $opportunite, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $opportunite->id && $piece->attachable_type === Opportunite::class, 404);
        $opportunite->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    private function categorieMedia(?string $mime): string
    {
        if (! $mime) return 'document';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'document';
    }

    private function synchroniserPublication(Opportunite $opportunite, OpportuniteRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) {
            $cibles[] = ['type' => 'user', 'id' => (int) $userId];
        }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) {
            $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId];
        }

        $opportunite->publier(
            $request->input('visibilite', 'prive'),
            $cibles,
            [
                'likes_actifs'        => $request->boolean('likes_actifs'),
                'commentaires_actifs' => $request->boolean('commentaires_actifs'),
                'publie_le'           => now(),
            ]
        );
    }
}
