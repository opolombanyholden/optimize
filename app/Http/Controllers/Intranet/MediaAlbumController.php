<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\MediaAlbum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaAlbumController extends Controller
{
    private const FOLDER = 'intranet/mediatheque/albums';

    public function store(Request $request)
    {
        $request->validate([
            'nom'         => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id'   => ['nullable', 'exists:intranet_media_albums,id'],
            'couverture'  => ['nullable', 'image', 'max:5120'],
            'icone'       => ['nullable', 'string', 'max:50'],
            'couleur'     => ['nullable', 'string', 'max:20'],
        ]);

        $data = $request->only(['nom', 'description', 'parent_id', 'icone', 'couleur']);
        $data['created_by'] = auth()->id();

        if ($request->hasFile('couverture')) {
            $data['couverture'] = $request->file('couverture')->store(self::FOLDER, 'public');
        }

        $album = MediaAlbum::create($data);

        return redirect()->route('intranet.mediatheque.index', ['album_id' => $album->id])
            ->with('success', 'Album créé.');
    }

    public function update(Request $request, MediaAlbum $album)
    {
        $request->validate([
            'nom'         => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id'   => ['nullable', 'exists:intranet_media_albums,id'],
            'couverture'  => ['nullable', 'image', 'max:5120'],
            'icone'       => ['nullable', 'string', 'max:50'],
            'couleur'     => ['nullable', 'string', 'max:20'],
        ]);

        $data = $request->only(['nom', 'description', 'parent_id', 'icone', 'couleur']);

        if ($request->hasFile('couverture')) {
            if ($album->couverture) Storage::disk('public')->delete($album->couverture);
            $data['couverture'] = $request->file('couverture')->store(self::FOLDER, 'public');
        }

        $album->update($data);

        return redirect()->route('intranet.mediatheque.index', ['album_id' => $album->id])
            ->with('success', 'Album modifié.');
    }

    public function destroy(MediaAlbum $album)
    {
        // Détacher les médias (ils restent, juste sans album)
        $album->medias()->update(['album_id' => null]);
        // Détacher les sous-albums
        $album->enfants()->update(['parent_id' => $album->parent_id]);

        if ($album->couverture) Storage::disk('public')->delete($album->couverture);
        $album->delete();

        return redirect()->route('intranet.mediatheque.index', ['album_id' => $album->parent_id])
            ->with('success', 'Album supprimé.');
    }
}
