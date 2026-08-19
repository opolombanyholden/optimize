<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\RessourceRequest;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\PieceJointe;
use App\Models\Intranet\Ressource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RessourceController extends Controller
{
    private const FOLDER = 'intranet/ressources';

    public function index(Request $request)
    {
        $parentId = $request->input('dossier');
        $parent   = $parentId ? Ressource::findOrFail($parentId) : null;

        // Si recherche, chercher partout
        if ($request->filled('q')) {
            $items = Ressource::with(['auteur', 'enfants'])
                ->withCount('enfants')
                ->recherche($request->input('q'))
                ->when($request->filled('categorie'), fn($q) => $q->where('categorie', $request->categorie))
                ->orderByDesc('type')
                ->orderBy('titre')
                ->paginate(30)
                ->withQueryString();
        } else {
            $items = Ressource::with(['auteur', 'enfants'])
                ->withCount('enfants')
                ->where('parent_id', $parentId)
                ->when($request->filled('categorie'), fn($q) => $q->where('categorie', $request->categorie))
                ->orderByDesc('type')
                ->orderBy('titre')
                ->paginate(30)
                ->withQueryString();
        }

        $categories = Ressource::whereNotNull('categorie')
            ->distinct()->orderBy('categorie')->pluck('categorie');

        // Stats
        $stats = [
            'dossiers' => Ressource::query()->dossiers()->count(),
            'fichiers' => Ressource::query()->fichiers()->count(),
            'taille'   => $this->tailleHumaine((int) Ressource::query()->fichiers()->sum('taille')),
        ];

        return view('intranet.ressources.index', compact('items', 'parent', 'categories', 'stats'));
    }

    public function create(Request $request)
    {
        $type   = $request->input('type', 'fichier');
        $parent = $request->input('parent') ? Ressource::find($request->input('parent')) : null;

        return view('intranet.ressources.create', [
            'ressource'    => new Ressource(['type' => $type, 'parent_id' => $parent?->id]),
            'type'         => $type,
            'parent'       => $parent,
            'dossiers'     => Ressource::query()->dossiers()->orderBy('titre')->get(['id', 'titre', 'parent_id']),
            'users'        => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'      => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'  => [],
            'ciblesGroupes'=> [],
        ]);
    }

    public function store(RessourceRequest $request)
    {
        $ressource = DB::transaction(function () use ($request) {
            $data = $request->safe()->except([
                'fichier', 'pieces_jointes', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags']       = $request->tagsArray();
            $data['created_by'] = auth()->id();

            if ($request->input('type') === 'fichier' && $request->hasFile('fichier')) {
                $file = $request->file('fichier');
                $data['chemin']       = $file->store(self::FOLDER, 'public');
                $data['nom_original'] = $file->getClientOriginalName();
                $data['mime_type']    = $file->getMimeType();
                $data['taille']       = $file->getSize();
            }

            // Image d'aperçu pour dossier
            if ($request->hasFile('apercu')) {
                $data['chemin'] = $request->file('apercu')->store(self::FOLDER . '/apercu', 'public');
            }

            $ressource = Ressource::create($data);

            if ($request->hasFile('pieces_jointes')) {
                $ressource->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $ressource->id);
            }

            $this->synchroniserPublication($ressource, $request);

            return $ressource;
        });

        return redirect()->route('intranet.ressources.show', $ressource)
            ->with('success', $ressource->est_dossier ? 'Dossier créé.' : 'Fichier ajouté.');
    }

    public function show(Ressource $ressource)
    {
        if ($ressource->est_dossier) {
            // Rediriger vers l'index avec le dossier ouvert
            return redirect()->route('intranet.ressources.index', ['dossier' => $ressource->id]);
        }

        $ressource->load(['auteur', 'parent', 'publication.cibles', 'piecesJointes', 'commentaires.user']);

        if (auth()->check()) {
            $ressource->enregistrerVue();
            $ressource->increment('vues_count');
        }

        return view('intranet.ressources.show', compact('ressource'));
    }

    public function download(Ressource $ressource)
    {
        abort_if($ressource->est_dossier || ! $ressource->chemin, 404);
        $ressource->increment('telechargements_count');

        return Storage::disk('public')->download(
            $ressource->chemin,
            $ressource->nom_original ?? basename($ressource->chemin)
        );
    }

    public function edit(Ressource $ressource)
    {
        $ressource->load(['publication.cibles', 'piecesJointes']);
        $ciblesUsers   = $ressource->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $ressource->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return view('intranet.ressources.edit', [
            'ressource'    => $ressource,
            'type'         => $ressource->type,
            'parent'       => $ressource->parent,
            'dossiers'     => Ressource::query()->dossiers()->where('id', '!=', $ressource->id)->orderBy('titre')->get(['id', 'titre', 'parent_id']),
            'users'        => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'      => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'  => $ciblesUsers,
            'ciblesGroupes'=> $ciblesGroupes,
        ]);
    }

    public function update(RessourceRequest $request, Ressource $ressource)
    {
        DB::transaction(function () use ($request, $ressource) {
            $data = $request->safe()->except([
                'fichier', 'pieces_jointes', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags'] = $request->tagsArray();

            if ($request->hasFile('fichier')) {
                if ($ressource->chemin) Storage::disk('public')->delete($ressource->chemin);
                $file = $request->file('fichier');
                $data['chemin']       = $file->store(self::FOLDER, 'public');
                $data['nom_original'] = $file->getClientOriginalName();
                $data['mime_type']    = $file->getMimeType();
                $data['taille']       = $file->getSize();
                $data['version']      = $ressource->version + 1;
            }

            // Image d'aperçu dossier
            if ($request->hasFile('apercu')) {
                if ($ressource->est_dossier && $ressource->chemin) Storage::disk('public')->delete($ressource->chemin);
                $data['chemin'] = $request->file('apercu')->store(self::FOLDER . '/apercu', 'public');
            }

            $ressource->update($data);

            if ($request->hasFile('pieces_jointes')) {
                $ressource->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $ressource->id);
            }

            $this->synchroniserPublication($ressource, $request);
        });

        return redirect()->route('intranet.ressources.show', $ressource)
            ->with('success', 'Ressource mise à jour.');
    }

    public function destroy(Ressource $ressource)
    {
        if ($ressource->chemin) Storage::disk('public')->delete($ressource->chemin);
        $ressource->delete();
        return redirect()->route('intranet.ressources.index', ['dossier' => $ressource->parent_id])
            ->with('success', 'Supprimé.');
    }

    /**
     * Déplacer un fichier dans un dossier (drag & drop AJAX).
     */
    public function deplacer(Request $request, Ressource $ressource)
    {
        $request->validate([
            'parent_id' => ['nullable', 'exists:intranet_ressources,id'],
        ]);

        // Vérifier que la cible est bien un dossier (ou null = racine)
        if ($request->parent_id) {
            $target = Ressource::find($request->parent_id);
            abort_if($target && !$target->est_dossier, 422, 'La cible n\'est pas un dossier.');
            abort_if($target && $target->id === $ressource->id, 422, 'Impossible de déplacer dans lui-même.');
        }

        $ressource->update(['parent_id' => $request->parent_id]);
        return response()->json(['ok' => true]);
    }

    public function destroyPieceJointe(Ressource $ressource, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $ressource->id && $piece->attachable_type === Ressource::class, 404);
        $ressource->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    // ══════════════════════════════════════════════════════
    private function tailleHumaine(int $b): string
    {
        if ($b >= 1073741824) return number_format($b / 1073741824, 2) . ' Go';
        if ($b >= 1048576)    return number_format($b / 1048576, 2) . ' Mo';
        if ($b >= 1024)       return number_format($b / 1024, 2) . ' Ko';
        return $b . ' o';
    }

    private function synchroniserPublication(Ressource $ressource, RessourceRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) {
            $cibles[] = ['type' => 'user', 'id' => (int) $userId];
        }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) {
            $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId];
        }

        $ressource->publier(
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
