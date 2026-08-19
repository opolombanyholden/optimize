<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\NewsRequest;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\News;
use App\Models\Intranet\PieceJointe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    private const FOLDER = 'intranet/news';

    public function index(Request $request)
    {
        $base = News::with(['auteur', 'publication'])
            ->withCount(['likes', 'commentaires'])
            ->recherche($request->input('q'))
            ->when($request->filled('rubrique'), fn($q) => $q->where('rubrique', $request->rubrique))
            ->when($request->filled('statut'), function ($q) use ($request) {
                if ($request->statut === 'aune')   $q->alaUne();
                if ($request->statut === 'actives') $q->actives();
            });

        // Article à la une (top 1, sorti de la liste paginée)
        $aLaUne = (clone $base)->alaUne()
            ->orderByDesc('created_at')
            ->first();

        $news = $base
            ->when($aLaUne, fn($q) => $q->where('id', '!=', $aLaUne->id))
            ->orderByDesc('created_at')
            ->paginate(9)
            ->withQueryString();

        $rubriques = News::query()
            ->whereNotNull('rubrique')
            ->distinct()
            ->orderBy('rubrique')
            ->pluck('rubrique');

        return view('intranet.news.index', compact('news', 'aLaUne', 'rubriques'));
    }

    public function create()
    {
        return view('intranet.news.create', [
            'news'    => new News(['couleur' => '#7C3AED']),
            'users'   => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes' => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
        ]);
    }

    public function store(NewsRequest $request)
    {
        $news = DB::transaction(function () use ($request) {
            $data = $request->safe()->only([
                'title', 'extrait', 'rubrique', 'content',
                'auteur_signature', 'source',
                'date_debut', 'date_fin', 'a_la_une', 'couleur',
            ]);
            $data['created_by'] = auth()->id();
            $data['tags']       = $request->tagsArray();

            if ($request->hasFile('media_principal')) {
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $news = News::create($data);

            if ($request->hasFile('pieces_jointes')) {
                $news->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $news->id);
            }

            $this->synchroniserPublication($news, $request);

            return $news;
        });

        return redirect()
            ->route('intranet.news.show', $news)
            ->with('success', 'Article publié avec succès.');
    }

    public function show(News $news)
    {
        $news->load([
            'auteur', 'publication.cibles', 'piecesJointes',
            'commentaires.user', 'commentaires.reponses.user',
        ]);

        if (auth()->check()) {
            $news->enregistrerVue();
            $news->increment('vues_count');
        }

        // Articles connexes (même rubrique)
        $relatedNews = News::where('id', '!=', $news->id)
            ->when($news->rubrique, fn($q) => $q->where('rubrique', $news->rubrique))
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        return view('intranet.news.show', compact('news', 'relatedNews'));
    }

    public function edit(News $news)
    {
        $news->load(['publication.cibles', 'piecesJointes']);

        $ciblesUsers   = $news->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $news->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return view('intranet.news.edit', [
            'news'           => $news,
            'users'          => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'        => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'    => $ciblesUsers,
            'ciblesGroupes'  => $ciblesGroupes,
        ]);
    }

    public function update(NewsRequest $request, News $news)
    {
        DB::transaction(function () use ($request, $news) {
            $data = $request->safe()->only([
                'title', 'extrait', 'rubrique', 'content',
                'auteur_signature', 'source',
                'date_debut', 'date_fin', 'a_la_une', 'couleur',
            ]);
            $data['tags'] = $request->tagsArray();

            if ($request->hasFile('media_principal')) {
                if ($news->media_principal) {
                    Storage::disk('public')->delete($news->media_principal);
                }
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $news->update($data);

            if ($request->hasFile('pieces_jointes')) {
                $news->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $news->id);
            }

            $this->synchroniserPublication($news, $request);
        });

        return redirect()
            ->route('intranet.news.show', $news)
            ->with('success', 'Article mis à jour.');
    }

    public function destroy(News $news)
    {
        $news->delete();
        return redirect()
            ->route('intranet.news.index')
            ->with('success', 'Article supprimé.');
    }

    public function destroyPieceJointe(News $news, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $news->id && $piece->attachable_type === News::class, 404);
        $news->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    // ══════════════════════════════════════════════════════
    private function categorieMedia(?string $mime): string
    {
        if (! $mime) return 'document';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'document';
    }

    private function synchroniserPublication(News $news, NewsRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) {
            $cibles[] = ['type' => 'user', 'id' => (int) $userId];
        }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) {
            $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId];
        }

        $news->publier(
            $request->input('visibilite', 'public'),
            $cibles,
            [
                'likes_actifs'        => $request->boolean('likes_actifs'),
                'commentaires_actifs' => $request->boolean('commentaires_actifs'),
                'publie_le'           => $request->input('publie_le') ?: now(),
                'expire_le'           => $request->input('expire_le'),
            ]
        );
    }
}
