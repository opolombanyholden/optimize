<?php

namespace App\Traits\Intranet;

use App\Models\Intranet\PieceJointe;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasPiecesJointes
{
    public function piecesJointes()
    {
        return $this->morphMany(PieceJointe::class, 'attachable')->orderBy('ordre');
    }

    public function images()
    {
        return $this->piecesJointes()->where('categorie', 'image');
    }

    public function videos()
    {
        return $this->piecesJointes()->where('categorie', 'video');
    }

    public function documents()
    {
        return $this->piecesJointes()->where('categorie', 'document');
    }

    /**
     * Attache un fichier uploadé à l'entité courante.
     *
     * @param  UploadedFile  $file
     * @param  string        $folder  Dossier dans storage/app/public/
     * @param  int           $ordre
     */
    public function attacherFichier(UploadedFile $file, string $folder, int $ordre = 0): PieceJointe
    {
        $path = $file->store($folder, 'public');

        return $this->piecesJointes()->create([
            'nom_original' => $file->getClientOriginalName(),
            'chemin'       => $path,
            'type_mime'    => $file->getMimeType(),
            'taille'       => $file->getSize(),
            'categorie'    => PieceJointe::categoriserMime($file->getMimeType()),
            'ordre'        => $ordre,
            'created_by'   => auth()->id(),
        ]);
    }

    /**
     * Attache plusieurs fichiers uploadés.
     */
    public function attacherFichiers(array $files, string $folder): void
    {
        foreach (array_values(array_filter($files)) as $i => $file) {
            if ($file instanceof UploadedFile) {
                $this->attacherFichier($file, $folder, $i);
            }
        }
    }

    /**
     * Supprime une pièce jointe (fichier + enregistrement).
     */
    public function detacherFichier(int $pieceJointeId): bool
    {
        $pj = $this->piecesJointes()->find($pieceJointeId);
        if (! $pj) return false;

        Storage::disk('public')->delete($pj->chemin);
        return (bool) $pj->delete();
    }

    /**
     * Supprime toutes les pièces jointes liées (fichiers + enregistrements).
     */
    public function detacherToutesPiecesJointes(): void
    {
        foreach ($this->piecesJointes as $pj) {
            Storage::disk('public')->delete($pj->chemin);
            $pj->delete();
        }
    }
}
