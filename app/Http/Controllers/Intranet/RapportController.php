<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\RapportRequest;
use App\Models\Intranet\Activite;
use App\Models\Intranet\Evenement;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\PieceJointe;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetPhase;
use App\Models\Intranet\Rapport;
use App\Models\Intranet\Tache;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RapportController extends Controller
{
    private const FOLDER = 'intranet/rapports';

    public function index(Request $request)
    {
        $rapports = Rapport::with(['auteur', 'evenement', 'projet'])
            ->recherche($request->input('q'))
            ->when($request->filled('type'),   fn($q) => $q->where('type', $request->type))
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->when($request->boolean('mes_rapports'), fn($q) => $q->where('created_by', auth()->id()))
            ->orderByDesc('date_document')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'        => Rapport::count(),
            'brouillons'   => Rapport::where('statut', 'brouillon')->count(),
            'publies'      => Rapport::where('statut', 'publie')->count(),
            'a_valider'    => Rapport::where('valideur_id', auth()->id())->where('statut_validation', 'en_attente')->count(),
        ];

        return view('intranet.rapports.index', compact('rapports', 'stats'));
    }

    public function create()
    {
        return view('intranet.rapports.create', $this->formData(new Rapport([
            'type' => 'cr', 'statut' => 'brouillon', 'date_document' => now()->toDateString(),
        ])));
    }

    private function formData(Rapport $rapport, array $extra = []): array
    {
        $rapport->loadMissing('publication.cibles');
        $ciblesUsers   = $rapport->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $rapport->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return array_merge([
            'rapport'      => $rapport,
            'evenements'   => Evenement::orderByDesc('date_debut')->limit(50)->get(['id', 'titre', 'date_debut']),
            'projets'      => Projet::orderBy('nom')->get(['id', 'nom']),
            'phases'       => ProjetPhase::orderBy('nom')->get(['id', 'nom', 'projet_id']),
            'taches'       => Tache::orderBy('titre')->get(['id', 'titre', 'projet_id']),
            'activites'    => Activite::orderBy('titre')->get(['id', 'titre']),
            'utilisateurs' => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'users'        => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'      => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'  => $ciblesUsers,
            'ciblesGroupes'=> $ciblesGroupes,
        ], $extra);
    }

    public function store(RapportRequest $request)
    {
        $rapport = DB::transaction(function () use ($request) {
            $data = $request->safe()->except([
                'media_principal', 'pieces_jointes', 'tags', 'participants',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags']         = $request->tagsArray();
            $data['participants'] = $request->input('participants', []);
            $data['created_by']   = auth()->id();

            if ($request->hasFile('media_principal')) {
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $rapport = Rapport::create($data);

            if ($request->hasFile('pieces_jointes')) {
                $rapport->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $rapport->id);
            }

            $this->synchroniserPublication($rapport, $request);
            return $rapport;
        });

        return redirect()->route('intranet.rapports.show', $rapport)
            ->with('success', 'Rapport créé.');
    }

    public function show(Rapport $rapport)
    {
        $rapport->load(['auteur', 'evenement', 'projet', 'phase', 'tache', 'activite', 'valideur', 'publication.cibles', 'piecesJointes', 'commentaires.user']);
        if (auth()->check()) { $rapport->enregistrerVue(); $rapport->increment('vues_count'); }

        // Charger les participants (users)
        $participantsUsers = collect();
        if (!empty($rapport->participants)) {
            $participantsUsers = User::whereIn('id', $rapport->participants)->get();
        }

        return view('intranet.rapports.show', compact('rapport', 'participantsUsers'));
    }

    public function edit(Rapport $rapport)
    {
        $rapport->load(['publication.cibles', 'piecesJointes']);
        return view('intranet.rapports.edit', $this->formData($rapport));
    }

    public function update(RapportRequest $request, Rapport $rapport)
    {
        DB::transaction(function () use ($request, $rapport) {
            $data = $request->safe()->except([
                'media_principal', 'pieces_jointes', 'tags', 'participants',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags']         = $request->tagsArray();
            $data['participants'] = $request->input('participants', []);

            if ($request->hasFile('media_principal')) {
                if ($rapport->media_principal) Storage::disk('public')->delete($rapport->media_principal);
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $rapport->update($data);

            if ($request->hasFile('pieces_jointes')) {
                $rapport->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $rapport->id);
            }

            $this->synchroniserPublication($rapport, $request);
        });

        return redirect()->route('intranet.rapports.show', $rapport)
            ->with('success', 'Rapport mis à jour.');
    }

    public function destroy(Rapport $rapport)
    {
        if ($rapport->media_principal) Storage::disk('public')->delete($rapport->media_principal);
        $rapport->delete();
        return redirect()->route('intranet.rapports.index')->with('success', 'Rapport supprimé.');
    }

    // ─── WORKFLOW DE VALIDATION ────────────────────────
    public function soumettre(Request $request, Rapport $rapport)
    {
        $request->validate(['valideur_id' => 'required|exists:users,id']);
        $valideur = User::find($request->valideur_id);
        $rapport->soumettre($valideur);
        return back()->with('success', "Rapport soumis à {$valideur->prenoms} {$valideur->name} pour validation.");
    }

    public function approuver(Request $request, Rapport $rapport)
    {
        abort_unless($rapport->valideur_id === auth()->id(), 403, 'Vous n\'êtes pas le valideur désigné.');
        $rapport->approuver($request->input('commentaire'));
        return back()->with('success', 'Rapport approuvé.');
    }

    public function rejeter(Request $request, Rapport $rapport)
    {
        abort_unless($rapport->valideur_id === auth()->id(), 403);
        $request->validate(['commentaire' => 'required|string']);
        $rapport->rejeter($request->input('commentaire'));
        return back()->with('success', 'Rapport rejeté.');
    }

    public function demanderRevisions(Request $request, Rapport $rapport)
    {
        abort_unless($rapport->valideur_id === auth()->id(), 403);
        $request->validate(['commentaire' => 'required|string']);
        $rapport->demanderRevisions($request->input('commentaire'));
        return back()->with('success', 'Révisions demandées.');
    }

    public function destroyPieceJointe(Rapport $rapport, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $rapport->id && $piece->attachable_type === Rapport::class, 404);
        $rapport->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    private function categorieMedia(?string $mime): string
    {
        if (! $mime) return 'document';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'document';
    }

    private function synchroniserPublication(Rapport $rapport, RapportRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) { $cibles[] = ['type' => 'user', 'id' => (int) $userId]; }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) { $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId]; }
        $rapport->publier($request->input('visibilite', 'prive'), $cibles, [
            'likes_actifs' => $request->boolean('likes_actifs'),
            'commentaires_actifs' => $request->boolean('commentaires_actifs'),
            'publie_le' => now(),
        ]);
    }
}
