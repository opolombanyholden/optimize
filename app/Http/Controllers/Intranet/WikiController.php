<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\PieceJointe;
use App\Models\Intranet\WikiArticle;
use App\Models\Intranet\WikiCategorie;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WikiController extends Controller
{
    private const FOLDER = 'intranet/wiki';

    public function index(Request $request)
    {
        $categorieId = $request->input('categorie');
        $categorie   = $categorieId ? WikiCategorie::findOrFail($categorieId) : null;

        // Catégories racines ou enfants
        $sousCategories = WikiCategorie::withCount('articles')
            ->where('parent_id', $categorieId)
            ->orderBy('ordre')
            ->get();

        $articles = WikiArticle::with(['categorie', 'auteur'])
            ->recherche($request->input('q'))
            ->when($categorieId, fn($q) => $q->where('categorie_id', $categorieId))
            ->when($request->boolean('epingles'), fn($q) => $q->epingles())
            ->orderByDesc('is_epingle')
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'articles'   => WikiArticle::count(),
            'categories' => WikiCategorie::count(),
        ];

        return view('intranet.wiki.index', compact('articles', 'sousCategories', 'categorie', 'stats'));
    }

    public function create(Request $request)
    {
        return view('intranet.wiki.create', $this->formData(new WikiArticle([
            'categorie_id' => $request->input('categorie'),
        ])));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre'        => 'required|string|max:255',
            'extrait'      => 'nullable|string|max:500',
            'contenu'      => 'nullable|string',
            'categorie_id' => 'nullable|exists:intranet_wiki_categories,id',
            'parent_id'    => 'nullable|exists:intranet_wiki_articles,id',
            'tags'         => 'nullable|string',
            'is_epingle'   => 'nullable|boolean',
            'media_principal' => 'nullable|file|max:51200',
            'pieces_jointes'  => 'nullable|array',
            'visibilite'      => 'nullable|in:public,prive,brouillon',
            'likes_actifs'    => 'nullable|boolean',
            'commentaires_actifs' => 'nullable|boolean',
            'cibles_users'    => 'nullable|array',
            'cibles_groupes'  => 'nullable|array',
        ]);

        $article = DB::transaction(function () use ($request) {
            $data = $request->only([
                'titre', 'extrait', 'contenu', 'categorie_id', 'parent_id', 'is_epingle',
            ]);
            $data['tags']       = $this->parseTags($request->input('tags'));
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
            $data['is_public']  = $request->input('visibilite') === 'public';

            if ($request->hasFile('media_principal')) {
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'document';
            }

            $article = WikiArticle::create($data);

            if ($request->hasFile('pieces_jointes')) {
                $article->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $article->id);
            }

            $this->syncPublication($article, $request);
            return $article;
        });

        return redirect()->route('intranet.wiki.show', $article)->with('success', 'Article créé.');
    }

    public function show(WikiArticle $wiki)
    {
        $article = $wiki;
        $article->load(['categorie', 'auteur', 'editeur', 'parent', 'enfants', 'publication.cibles', 'piecesJointes', 'commentaires.user']);
        $article->increment('vues');

        // Table des matières : articles enfants dans la même catégorie
        $tableDesMatieres = collect();
        if ($article->categorie) {
            $tableDesMatieres = WikiArticle::where('categorie_id', $article->categorie_id)
                ->orderBy('ordre')
                ->get(['id', 'titre', 'slug']);
        }

        return view('intranet.wiki.show', compact('article', 'tableDesMatieres'));
    }

    public function edit(WikiArticle $wiki)
    {
        $wiki->load(['publication.cibles', 'piecesJointes']);
        return view('intranet.wiki.edit', $this->formData($wiki));
    }

    public function update(Request $request, WikiArticle $wiki)
    {
        $request->validate([
            'titre'        => 'required|string|max:255',
            'extrait'      => 'nullable|string|max:500',
            'contenu'      => 'nullable|string',
            'categorie_id' => 'nullable|exists:intranet_wiki_categories,id',
            'parent_id'    => 'nullable|exists:intranet_wiki_articles,id',
            'tags'         => 'nullable|string',
            'is_epingle'   => 'nullable|boolean',
            'media_principal' => 'nullable|file|max:51200',
            'pieces_jointes'  => 'nullable|array',
            'visibilite'      => 'nullable|in:public,prive,brouillon',
        ]);

        DB::transaction(function () use ($request, $wiki) {
            $data = $request->only(['titre', 'extrait', 'contenu', 'categorie_id', 'parent_id']);
            $data['tags']       = $this->parseTags($request->input('tags'));
            $data['is_epingle'] = $request->boolean('is_epingle');
            $data['is_public']  = $request->input('visibilite') === 'public';
            $data['updated_by'] = auth()->id();
            $data['version']    = $wiki->version + 1;

            if ($request->hasFile('media_principal')) {
                if ($wiki->media_principal) Storage::disk('public')->delete($wiki->media_principal);
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'document';
            }

            $wiki->update($data);

            if ($request->hasFile('pieces_jointes')) {
                $wiki->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $wiki->id);
            }

            $this->syncPublication($wiki, $request);
        });

        return redirect()->route('intranet.wiki.show', $wiki)->with('success', 'Article mis à jour (v' . $wiki->fresh()->version . ').');
    }

    public function destroy(WikiArticle $wiki)
    {
        $wiki->delete();
        return redirect()->route('intranet.wiki.index')->with('success', 'Article supprimé.');
    }

    public function destroyPieceJointe(WikiArticle $wiki, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $wiki->id && $piece->attachable_type === WikiArticle::class, 404);
        $wiki->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    // ── Catégories (via modale) ─────────────────────────
    public function storeCategorie(Request $request)
    {
        $request->validate(['nom' => 'required|string|max:255', 'parent_id' => 'nullable|exists:intranet_wiki_categories,id',
            'icone' => 'nullable|string|max:50', 'couleur' => 'nullable|string|max:20', 'description' => 'nullable|string']);
        $data = $request->only(['nom', 'description', 'parent_id', 'icone', 'couleur']);
        $data['slug'] = Str::slug($data['nom']);
        WikiCategorie::create($data);
        return back()->with('success', 'Catégorie créée.');
    }

    public function destroyCategorie(WikiCategorie $categorie)
    {
        $categorie->articles()->update(['categorie_id' => $categorie->parent_id]);
        $categorie->enfants()->update(['parent_id' => $categorie->parent_id]);
        $categorie->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }

    // ══════════════════════════════════════════════════════
    private function formData(WikiArticle $article): array
    {
        $article->loadMissing('publication.cibles');
        $ciblesUsers   = $article->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $article->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return [
            'article'       => $article,
            'categories'    => WikiCategorie::orderBy('ordre')->get(),
            'articles'      => WikiArticle::orderBy('titre')->get(['id', 'titre']),
            'users'         => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'       => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'   => $ciblesUsers,
            'ciblesGroupes' => $ciblesGroupes,
        ];
    }

    private function parseTags(?string $raw): array
    {
        if (! $raw) return [];
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    private function syncPublication(WikiArticle $article, Request $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $id) { $cibles[] = ['type' => 'user', 'id' => (int) $id]; }
        foreach ((array) $request->input('cibles_groupes', []) as $id) { $cibles[] = ['type' => 'groupe', 'id' => (int) $id]; }
        $article->publier($request->input('visibilite', 'public'), $cibles, [
            'likes_actifs' => $request->boolean('likes_actifs'),
            'commentaires_actifs' => $request->boolean('commentaires_actifs'),
            'publie_le' => now(),
        ]);
    }
}
