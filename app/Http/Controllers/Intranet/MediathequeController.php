<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\MediaRequest;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\Media;
use App\Models\Intranet\MediaAlbum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediathequeController extends Controller
{
    private const FOLDER = 'intranet/mediatheque';

    public function index(Request $request)
    {
        $albumId      = $request->input('album_id');
        $currentAlbum = $albumId ? MediaAlbum::findOrFail($albumId) : null;

        // Sous-albums du niveau courant
        $sousAlbums = MediaAlbum::withCount('medias')
            ->where('parent_id', $albumId)
            ->orderBy('nom')
            ->get();

        // Médias du niveau courant (ou tous si recherche)
        if ($request->filled('q')) {
            $medias = Media::with(['auteur', 'albumRelation'])
                ->withCount(['likes', 'commentaires'])
                ->recherche($request->input('q'))
                ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
                ->orderByDesc('created_at')
                ->paginate(24)
                ->withQueryString();
        } else {
            $medias = Media::with(['auteur', 'albumRelation'])
                ->withCount(['likes', 'commentaires'])
                ->where('album_id', $albumId)
                ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
                ->orderByDesc('created_at')
                ->paginate(24)
                ->withQueryString();
        }

        $stats = [
            'images' => Media::query()->images()->count(),
            'videos' => Media::query()->videos()->count(),
            'total'  => Media::count(),
            'albums' => MediaAlbum::count(),
        ];

        return view('intranet.mediatheque.index', compact('medias', 'sousAlbums', 'currentAlbum', 'stats'));
    }

    public function create(Request $request)
    {
        $albumId = $request->input('album_id');

        return view('intranet.mediatheque.create', [
            'media'        => new Media(['album_id' => $albumId]),
            'albumId'      => $albumId,
            'albums'       => MediaAlbum::orderBy('nom')->get(['id', 'nom']),
            'users'        => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'      => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'  => [],
            'ciblesGroupes'=> [],
        ]);
    }

    public function store(MediaRequest $request)
    {
        $media = DB::transaction(function () use ($request) {
            $data = $request->safe()->except([
                'fichier', 'url_externe', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags']       = $request->tagsArray();
            $data['created_by'] = auth()->id();

            // ── Vidéo externe (YouTube, Dailymotion, Vimeo…) ──
            if ($request->filled('url_externe')) {
                $url   = $request->input('url_externe');
                $parsed = Media::parseVideoUrl($url);

                $data['url_externe']   = $url;
                $data['plateforme']    = $parsed['plateforme'] ?? 'autre';
                $data['embed_id']      = $parsed['embed_id'] ?? null;
                $data['type']          = 'video';
                $data['mime_type']     = 'video/external';

                // Miniature automatique
                if ($parsed['plateforme'] === 'youtube' && $parsed['embed_id']) {
                    $data['thumbnail_url'] = "https://img.youtube.com/vi/{$parsed['embed_id']}/hqdefault.jpg";
                } elseif ($parsed['plateforme'] === 'dailymotion' && $parsed['embed_id']) {
                    $data['thumbnail_url'] = "https://www.dailymotion.com/thumbnail/video/{$parsed['embed_id']}";
                }

                if (empty($data['titre'])) {
                    $data['titre'] = 'Vidéo ' . ucfirst($data['plateforme']);
                }

            // ── Fichier uploadé ──
            } elseif ($request->hasFile('fichier')) {
                $file = $request->file('fichier');
                $mime = $file->getMimeType();

                $data['fichier']      = $file->store(self::FOLDER, 'public');
                $data['nom_original'] = $file->getClientOriginalName();
                $data['mime_type']    = $mime;
                $data['taille']       = $file->getSize();
                $data['type']         = Media::typeDepuisMime($mime);

                if (empty($data['titre'])) {
                    $data['titre'] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                }

                if (str_starts_with($mime, 'image/')) {
                    $path = Storage::disk('public')->path($data['fichier']);
                    $info = @getimagesize($path);
                    if ($info) {
                        $data['largeur'] = $info[0];
                        $data['hauteur'] = $info[1];
                    }
                }
            }

            $media = Media::create($data);
            $this->synchroniserPublication($media, $request);
            return $media;
        });

        return redirect()->route('intranet.mediatheque.show', $media)
            ->with('success', 'Média ajouté.');
    }

    public function show(Media $mediatheque)
    {
        $media = $mediatheque;
        $media->load(['auteur', 'publication.cibles', 'commentaires.user']);

        if (auth()->check()) {
            $media->enregistrerVue();
            $media->increment('vues_count');
        }

        return view('intranet.mediatheque.show', compact('media'));
    }

    public function edit(Media $mediatheque)
    {
        $media = $mediatheque;
        $media->load('publication.cibles');
        $ciblesUsers   = $media->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $media->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return view('intranet.mediatheque.edit', [
            'media'        => $media,
            'albums'       => MediaAlbum::orderBy('nom')->get(['id', 'nom']),
            'users'        => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'      => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'  => $ciblesUsers,
            'ciblesGroupes'=> $ciblesGroupes,
        ]);
    }

    public function update(MediaRequest $request, Media $mediatheque)
    {
        $media = $mediatheque;
        DB::transaction(function () use ($request, $media) {
            $data = $request->safe()->except([
                'fichier', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags'] = $request->tagsArray();

            if ($request->hasFile('fichier')) {
                if ($media->fichier) Storage::disk('public')->delete($media->fichier);
                $file = $request->file('fichier');
                $mime = $file->getMimeType();
                $data['fichier']      = $file->store(self::FOLDER, 'public');
                $data['nom_original'] = $file->getClientOriginalName();
                $data['mime_type']    = $mime;
                $data['taille']       = $file->getSize();
                $data['type']         = Media::typeDepuisMime($mime);

                if (str_starts_with($mime, 'image/')) {
                    $path = Storage::disk('public')->path($data['fichier']);
                    $info = @getimagesize($path);
                    if ($info) {
                        $data['largeur'] = $info[0];
                        $data['hauteur'] = $info[1];
                    }
                }
            }

            $media->update($data);
            $this->synchroniserPublication($media, $request);
        });

        return redirect()->route('intranet.mediatheque.show', $media)
            ->with('success', 'Média mis à jour.');
    }

    public function destroy(Media $mediatheque)
    {
        $media = $mediatheque;
        if ($media->fichier) Storage::disk('public')->delete($media->fichier);
        $media->delete();
        return redirect()->route('intranet.mediatheque.index')
            ->with('success', 'Média supprimé.');
    }

    /**
     * Upload multiple : envoyer plusieurs fichiers en un coup vers un album.
     * Crée un Media par fichier, attribue les valeurs par défaut, hérite des paramètres
     * de visibilité du formulaire.
     */
    public function storeMultiple(Request $request)
    {
        $request->validate([
            'album_id'   => ['nullable', 'exists:intranet_media_albums,id'],
            'fichiers'   => ['required', 'array'],
            'fichiers.*' => ['file', 'max:102400'],  // 100 Mo max par fichier
            'visibilite' => ['nullable', 'in:public,prive,groupes'],
        ]);

        $count = 0;
        DB::transaction(function () use ($request, &$count) {
            foreach ($request->file('fichiers') as $file) {
                $mime = $file->getMimeType();
                $data = [
                    'titre'        => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'album_id'     => $request->input('album_id'),
                    'fichier'      => $file->store(self::FOLDER, 'public'),
                    'nom_original' => $file->getClientOriginalName(),
                    'mime_type'    => $mime,
                    'taille'       => $file->getSize(),
                    'type'         => Media::typeDepuisMime($mime),
                    'created_by'   => auth()->id(),
                ];

                if (str_starts_with($mime, 'image/')) {
                    $path = Storage::disk('public')->path($data['fichier']);
                    $info = @getimagesize($path);
                    if ($info) {
                        $data['largeur'] = $info[0];
                        $data['hauteur'] = $info[1];
                    }
                }

                $media = Media::create($data);

                // Publication uniforme pour tout le lot
                if (method_exists($media, 'publication')) {
                    $media->publication()->create([
                        'visibilite'           => $request->input('visibilite', 'public'),
                        'likes_actifs'         => true,
                        'commentaires_actifs'  => true,
                        'created_by'           => auth()->id(),
                    ]);
                }

                $count++;
            }
        });

        $album = $request->input('album_id');
        $redirect = $album
            ? redirect()->route('intranet.mediatheque.albums.show', $album)
            : redirect()->route('intranet.mediatheque.index');

        return $redirect->with('success', $count . ' média(s) téléversé(s).');
    }

    public function download(Media $mediatheque)
    {
        $media = $mediatheque;
        abort_if(! $media->fichier, 404);
        $media->increment('telechargements_count');
        return Storage::disk('public')->download($media->fichier, $media->nom_original ?? basename($media->fichier));
    }

    // ══════════════════════════════════════════════════════
    private function synchroniserPublication(Media $media, MediaRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) {
            $cibles[] = ['type' => 'user', 'id' => (int) $userId];
        }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) {
            $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId];
        }

        $media->publier(
            $request->input('visibilite', 'public'),
            $cibles,
            [
                'likes_actifs'        => $request->boolean('likes_actifs'),
                'commentaires_actifs' => $request->boolean('commentaires_actifs'),
                'publie_le'           => now(),
            ]
        );
    }
}
