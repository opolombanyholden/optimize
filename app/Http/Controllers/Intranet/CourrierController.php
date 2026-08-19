<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\CourrierRequest;
use App\Models\Intranet\Courrier;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\PieceJointe;
use App\Models\Intranet\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourrierController extends Controller
{
    private const FOLDER = 'intranet/courriers';

    public function index(Request $request)
    {
        $courriers = Courrier::with(['auteur', 'assigne', 'serviceDestinataire'])
            ->recherche($request->input('q'))
            ->when($request->filled('type'),    fn($q) => $q->where('type', $request->type))
            ->when($request->filled('statut'),  fn($q) => $q->where('statut', $request->statut))
            ->when($request->filled('assigne'), fn($q) => $q->where('assigne_a', $request->assigne))
            ->when($request->boolean('mes_courriers'), fn($q) => $q->assignesAUser(auth()->id()))
            ->when($request->boolean('urgents'),       fn($q) => $q->where('urgent', true))
            ->when($request->boolean('en_retard'),     fn($q) => $q->where('echeance_traitement', '<', now())->whereNotIn('statut', ['traite', 'archive']))
            ->orderByDesc('urgent')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Statistiques pour cartes en haut
        $stats = [
            'total'      => Courrier::count(),
            'a_traiter'  => Courrier::enAttente()->count(),
            'mes_courriers' => Courrier::assignesAUser(auth()->id())->count(),
            'en_retard'  => Courrier::where('echeance_traitement', '<', now())
                                    ->whereNotIn('statut', ['traite', 'archive'])
                                    ->count(),
        ];

        return view('intranet.courriers.index', [
            'courriers'    => $courriers,
            'stats'        => $stats,
            'utilisateurs' => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']),
        ]);
    }

    public function create()
    {
        return view('intranet.courriers.create', [
            'courrier'      => new Courrier(['type' => 'entrant', 'statut' => 'recu', 'date_reception' => now()->toDateString()]),
            'services'      => Service::orderBy('nom')->get(),
            'utilisateurs'  => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'users'         => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'       => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'   => [],
            'ciblesGroupes' => [],
        ]);
    }

    public function store(CourrierRequest $request)
    {
        $courrier = DB::transaction(function () use ($request) {
            $data = $request->safe()->except([
                'media_principal', 'pieces_jointes',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['created_by'] = auth()->id();

            if ($request->hasFile('media_principal')) {
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $courrier = Courrier::create($data);

            if ($request->hasFile('pieces_jointes')) {
                $courrier->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $courrier->id);
            }

            $courrier->ajouterTraitement('cree');

            // Si assigné directement à la création, journaliser
            if ($courrier->assigne_a) {
                $courrier->update([
                    'assigne_le'  => now(),
                    'assigne_par' => auth()->id(),
                ]);
                $courrier->ajouterTraitement('assigne', null, ['nouveau_assigne' => $courrier->assigne_a]);
            }

            $this->synchroniserPublication($courrier, $request);

            return $courrier;
        });

        return redirect()->route('intranet.courriers.show', $courrier)
            ->with('success', 'Courrier créé.');
    }

    public function show(Courrier $courrier)
    {
        $courrier->load([
            'auteur', 'assigne', 'assignePar', 'traitePar',
            'serviceDestinataire', 'serviceExpediteur',
            'piecesJointes', 'publication.cibles',
            'commentaires.user',
            'traitements.user',
        ]);

        if (auth()->check()) {
            $courrier->enregistrerVue();
            $courrier->increment('vues_count');
        }

        return view('intranet.courriers.show', [
            'courrier'     => $courrier,
            'utilisateurs' => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']),
        ]);
    }

    public function edit(Courrier $courrier)
    {
        $courrier->load(['publication.cibles', 'piecesJointes']);
        $ciblesUsers   = $courrier->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $courrier->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return view('intranet.courriers.edit', [
            'courrier'      => $courrier,
            'services'      => Service::orderBy('nom')->get(),
            'utilisateurs'  => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'users'         => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'       => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'   => $ciblesUsers,
            'ciblesGroupes' => $ciblesGroupes,
        ]);
    }

    public function update(CourrierRequest $request, Courrier $courrier)
    {
        DB::transaction(function () use ($request, $courrier) {
            $data = $request->safe()->except([
                'media_principal', 'pieces_jointes',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);

            if ($request->hasFile('media_principal')) {
                if ($courrier->media_principal) Storage::disk('public')->delete($courrier->media_principal);
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $courrier->update($data);

            if ($request->hasFile('pieces_jointes')) {
                $courrier->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $courrier->id);
            }

            $this->synchroniserPublication($courrier, $request);
        });

        return redirect()->route('intranet.courriers.show', $courrier)
            ->with('success', 'Courrier mis à jour.');
    }

    public function destroy(Courrier $courrier)
    {
        $courrier->delete();
        return redirect()->route('intranet.courriers.index')
            ->with('success', 'Courrier supprimé.');
    }

    public function destroyPieceJointe(Courrier $courrier, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $courrier->id && $piece->attachable_type === Courrier::class, 404);
        $courrier->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    // ─── ASSIGNATION ───────────────────────────────────────
    public function assigner(Request $request, Courrier $courrier)
    {
        $request->validate([
            'assigne_a'   => ['required', 'exists:users,id'],
            'commentaire' => ['nullable', 'string'],
        ]);

        $user = User::find($request->input('assigne_a'));
        $courrier->assignerA($user, $request->input('commentaire'));

        return back()->with('success', "Courrier assigné à {$user->prenoms} {$user->name}.");
    }

    // ─── TRAITEMENT (clôture) ──────────────────────────────
    // Seul l'initiateur (created_by) ou un super-admin peut clôturer.
    // Un assigné peut ajouter PJ/notes mais pas valider la clôture.
    public function traiter(Request $request, Courrier $courrier)
    {
        $user = auth()->user();
        $estInitiateur = $courrier->created_by === $user->id;
        $estSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        if (! $estInitiateur && ! $estSuperAdmin) {
            return back()->withErrors([
                '_cloture' => 'Seul l\'initiateur du courrier peut valider sa clôture.',
            ]);
        }

        $request->validate([
            'commentaire' => ['nullable', 'string'],
        ]);
        $courrier->marquerTraite($request->input('commentaire'));
        return back()->with('success', 'Courrier marqué comme traité.');
    }

    // ─── AJOUT DE PIÈCES JOINTES DEPUIS LA VUE SHOW ───────
    // Autorisé pour : initiateur, assigné, ou utilisateur avec update:courrier
    public function ajouterPiecesJointes(Request $request, Courrier $courrier)
    {
        $user = auth()->user();
        $autorise = $courrier->created_by === $user->id
                 || $courrier->assigne_a === $user->id
                 || $user->can('update:courrier')
                 || ($user->hasRole('super-admin') ?? false);

        if (! $autorise) {
            return back()->withErrors(['_pj' => 'Vous n\'êtes pas autorisé à ajouter des pièces sur ce courrier.']);
        }

        $request->validate([
            'pieces_jointes'   => ['required', 'array'],
            'pieces_jointes.*' => ['file', 'max:51200'],
        ], [
            'pieces_jointes.required' => 'Sélectionnez au moins un fichier.',
        ]);

        $courrier->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $courrier->id);
        $courrier->ajouterTraitement('piece_jointe_ajoutee', count($request->file('pieces_jointes')) . ' pièce(s) ajoutée(s).');

        return back()->with('success', 'Pièces jointes ajoutées.');
    }

    public function rouvrir(Courrier $courrier)
    {
        $courrier->update([
            'statut'     => 'en_traitement',
            'traite_le'  => null,
            'traite_par' => null,
        ]);
        $courrier->ajouterTraitement('rouvert');
        return back()->with('success', 'Courrier rouvert.');
    }

    // ─── ACCUSÉ DE RÉCEPTION ───────────────────────────────
    public function accuserReception(Request $request, Courrier $courrier)
    {
        $request->validate([
            'methode' => ['required', 'in:email,courrier,fax,en_main_propre,autre'],
            'scan'    => ['nullable', 'file', 'max:51200', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $scanPath = null;
        if ($request->hasFile('scan')) {
            $scanPath = $request->file('scan')->store(self::FOLDER . '/ar', 'public');
        }

        $courrier->enregistrerAccuseReception($request->input('methode'), $scanPath);
        return back()->with('success', 'Accusé de réception enregistré.');
    }

    // ─── NOTATION ──────────────────────────────────────────
    public function ajouterNotation(Request $request, Courrier $courrier)
    {
        $request->validate([
            'commentaire' => ['required', 'string'],
        ]);

        // Commentaire polymorphique
        $courrier->commenter($request->input('commentaire'));
        // Journal d'action
        $courrier->ajouterTraitement('commente', $request->input('commentaire'));

        return back()->with('success', 'Notation ajoutée.');
    }

    // ══════════════════════════════════════════════════════
    private function categorieMedia(?string $mime): string
    {
        if (! $mime) return 'document';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'document';
    }

    private function synchroniserPublication(Courrier $courrier, CourrierRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) {
            $cibles[] = ['type' => 'user', 'id' => (int) $userId];
        }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) {
            $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId];
        }

        $courrier->publier(
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
