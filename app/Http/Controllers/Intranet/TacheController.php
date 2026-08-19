<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\TacheRequest;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\PieceJointe;
use App\Models\Intranet\Priorite;
use App\Models\Intranet\Projet;
use App\Models\Intranet\Statut;
use App\Models\Intranet\Tache;
use App\Models\Intranet\TacheValideur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TacheController extends Controller
{
    private const FOLDER = 'intranet/taches';

    public function index(Request $request)
    {
        $taches = Tache::with(['statut', 'priorite', 'projet', 'responsable', 'auteur'])
            ->withCount('activites')
            ->recherche($request->input('q'))
            ->when($request->filled('projet'),   fn($q) => $q->where('projet_id', $request->projet))
            ->when($request->filled('statut'),   fn($q) => $q->where('statut_id', $request->statut))
            ->when($request->filled('priorite'), fn($q) => $q->where('priorite_id', $request->priorite))
            ->when($request->boolean('mes_taches'),   fn($q) => $q->assigneesA(auth()->id()))
            ->when($request->boolean('en_retard'),    fn($q) => $q->enRetard())
            ->when($request->boolean('a_valider'),    fn($q) => $q->aValider(auth()->id()))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'      => Tache::count(),
            'mes_taches' => Tache::query()->assigneesA(auth()->id())->enCours()->count(),
            'en_retard'  => Tache::query()->enRetard()->count(),
            'a_valider'  => Tache::query()->aValider(auth()->id())->count(),
        ];

        return view('intranet.taches.index', [
            'taches'    => $taches,
            'stats'     => $stats,
            'projets'   => Projet::orderBy('nom')->get(['id', 'nom']),
            'statuts'   => Statut::orderBy('id')->get(),
            'priorites' => Priorite::orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('intranet.taches.create', $this->formData(new Tache(['avancement' => 0, 'statut_validation' => 'non_soumis'])));
    }

    public function store(TacheRequest $request)
    {
        $tache = DB::transaction(function () use ($request) {
            $data = $request->safe()->except([
                'assignes', 'valideurs_users', 'valideurs_groupes',
                'media_principal', 'pieces_jointes', 'justificatifs',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['created_by'] = auth()->id();

            if ($request->hasFile('media_principal')) {
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'document';
            }

            $tache = Tache::create($data);
            $tache->ajouterHistorique('cree');

            // Assignés
            if ($request->filled('assignes')) {
                $tache->assignes()->sync($request->input('assignes'));
            }

            // Valideurs
            $this->synchroniserValideurs($tache, $request);

            // PJ
            if ($request->hasFile('pieces_jointes')) {
                $tache->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $tache->id);
            }

            $this->synchroniserPublication($tache, $request);

            // Cascade vers phase et projet
            $tache->refresh();
            $tache->phase?->recalculerAvancement();
            $tache->projet?->recalculerAvancement();

            return $tache;
        });

        return redirect()->route('intranet.taches.show', $tache)->with('success', 'Tâche créée.');
    }

    public function show(Tache $tache)
    {
        $tache->load([
            'statut', 'priorite', 'projet', 'phase', 'objectif', 'responsable', 'auteur', 'evaluateur',
            'assignes', 'activites', 'checklist',
            'valideurs', 'historique.user',
            'publication.cibles', 'piecesJointes', 'commentaires.user',
        ]);

        $tache->increment('vues_count');

        return view('intranet.taches.show', compact('tache'));
    }

    public function edit(Tache $tache)
    {
        $tache->load(['publication.cibles', 'piecesJointes', 'assignes', 'valideurs']);
        return view('intranet.taches.edit', $this->formData($tache));
    }

    public function update(TacheRequest $request, Tache $tache)
    {
        DB::transaction(function () use ($request, $tache) {
            $data = $request->safe()->except([
                'assignes', 'valideurs_users', 'valideurs_groupes',
                'media_principal', 'pieces_jointes', 'justificatifs',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);

            if ($request->hasFile('media_principal')) {
                if ($tache->media_principal) Storage::disk('public')->delete($tache->media_principal);
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'document';
            }

            $tache->update($data);
            $tache->ajouterHistorique('modifie');

            if ($request->has('assignes')) {
                $tache->assignes()->sync($request->input('assignes', []));
            }

            $this->synchroniserValideurs($tache, $request);

            if ($request->hasFile('pieces_jointes')) {
                $tache->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $tache->id);
            }

            $this->synchroniserPublication($tache, $request);

            // Répercuter sur la phase + projet (cascade avancement & coûts)
            $tache->refresh();
            $tache->phase?->recalculerAvancement();
            $tache->projet?->recalculerAvancement();
        });

        return redirect()->route('intranet.taches.show', $tache)->with('success', 'Tâche mise à jour.');
    }

    public function destroy(Tache $tache)
    {
        $tache->delete();
        return redirect()->route('intranet.taches.index')->with('success', 'Tâche supprimée.');
    }

    public function destroyPieceJointe(Tache $tache, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $tache->id && $piece->attachable_type === Tache::class, 404);
        $tache->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    /**
     * Retourne phases + jalons d'un projet en JSON (pour le select dynamique).
     */
    public function phasesJalonsProjet(Projet $projet)
    {
        return response()->json([
            'phases' => \App\Models\Intranet\ProjetPhase::where('projet_id', $projet->id)
                ->orderBy('ordre')
                ->get(['id', 'nom', 'code_wbs']),
            'jalons' => \App\Models\Intranet\Jalon::where('projet_id', $projet->id)
                ->orderBy('date_prevue')
                ->get(['id', 'titre']),
        ]);
    }

    // ─── SOUMISSION POUR VALIDATION ────────────────────
    public function soumettre(Request $request, Tache $tache)
    {
        // Justificatifs lors de la soumission
        if ($request->hasFile('justificatifs')) {
            $tache->attacherFichiers($request->file('justificatifs'), self::FOLDER . '/' . $tache->id . '/justificatifs');
            $tache->ajouterHistorique('piece_jointe_ajoutee', 'Pièces justificatives ajoutées lors de la soumission');
        }

        $action = $tache->statut_validation === 'rejete' || $tache->statut_validation === 'revisions' ? 'resoumis' : 'soumis';
        $tache->update([
            'statut_validation' => 'soumis',
            'soumis_le'         => now(),
            'motif_rejet'       => null,
        ]);
        $tache->ajouterHistorique($action, $request->input('commentaire'));

        return back()->with('success', 'Tâche soumise pour validation.');
    }

    // ─── ACTIONS DU VALIDEUR ───────────────────────────
    public function approuver(Request $request, Tache $tache)
    {
        abort_unless($tache->estValideur(), 403, 'Vous n\'êtes pas valideur de cette tâche.');
        $tache->approuverTache($request->input('commentaire'));
        return back()->with('success', 'Tâche approuvée.');
    }

    public function rejeter(Request $request, Tache $tache)
    {
        abort_unless($tache->estValideur(), 403);
        $request->validate(['motif' => 'required|string']);
        $tache->rejeterTache($request->input('motif'));
        return back()->with('success', 'Tâche rejetée.');
    }

    public function demanderRevisions(Request $request, Tache $tache)
    {
        abort_unless($tache->estValideur(), 403);
        $request->validate(['commentaire' => 'required|string']);
        $tache->demanderRevisionsTache($request->input('commentaire'));
        return back()->with('success', 'Révisions demandées.');
    }

    public function evaluer(Request $request, Tache $tache)
    {
        abort_unless($tache->estValideur(), 403);
        $request->validate([
            'note'         => 'required|integer|min:1|max:5',
            'appreciation' => 'nullable|string',
        ]);
        $tache->evaluerTache($request->input('note'), $request->input('appreciation'));
        return back()->with('success', 'Évaluation enregistrée.');
    }

    // ══════════════════════════════════════════════════════
    private function formData(Tache $tache): array
    {
        $tache->loadMissing(['publication.cibles', 'valideurs']);
        $ciblesUsers   = $tache->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $tache->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        // Phases et jalons du projet sélectionné (si applicable)
        $phases = collect();
        $jalons = collect();
        if ($tache->projet_id) {
            $phases = \App\Models\Intranet\ProjetPhase::where('projet_id', $tache->projet_id)
                ->orderBy('ordre')->get(['id', 'nom', 'code_wbs']);
            $jalons = \App\Models\Intranet\Jalon::where('projet_id', $tache->projet_id)
                ->orderBy('date_prevue')->get(['id', 'titre', 'date_prevue']);
        }

        return [
            'tache'             => $tache,
            'projets'           => Projet::orderBy('nom')->get(['id', 'nom']),
            'statuts'           => Statut::orderBy('id')->get(),
            'priorites'         => Priorite::orderBy('id')->get(),
            'phases'            => $phases,
            'jalons'            => $jalons,
            'utilisateurs'      => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'users'             => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'           => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'       => $ciblesUsers,
            'ciblesGroupes'     => $ciblesGroupes,
            'assignesIds'       => $tache->exists ? $tache->assignes->pluck('id')->all() : [],
            'validateursUsersIds'   => $tache->exists ? $tache->validateursUsers->pluck('valideur_id')->all() : [],
            'validateursGroupesIds' => $tache->exists ? $tache->validateursGroupes->pluck('valideur_id')->all() : [],
        ];
    }

    private function synchroniserValideurs(Tache $tache, TacheRequest $request): void
    {
        $tache->valideurs()->delete();

        foreach ((array) $request->input('valideurs_users', []) as $userId) {
            TacheValideur::create([
                'tache_id'      => $tache->id,
                'valideur_type' => 'user',
                'valideur_id'   => (int) $userId,
            ]);
        }
        foreach ((array) $request->input('valideurs_groupes', []) as $groupeId) {
            TacheValideur::create([
                'tache_id'      => $tache->id,
                'valideur_type' => 'groupe',
                'valideur_id'   => (int) $groupeId,
            ]);
        }
    }

    private function synchroniserPublication(Tache $tache, TacheRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) { $cibles[] = ['type' => 'user', 'id' => (int) $userId]; }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) { $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId]; }
        $tache->publier($request->input('visibilite', 'prive'), $cibles, [
            'likes_actifs' => $request->boolean('likes_actifs'),
            'commentaires_actifs' => $request->boolean('commentaires_actifs'),
            'publie_le' => now(),
        ]);
    }
}
