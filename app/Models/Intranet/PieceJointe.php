<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PieceJointe extends Model
{
    protected $table = 'intranet_pieces_jointes';

    protected $fillable = [
        'attachable_type', 'attachable_id',
        'nom_original', 'chemin', 'type_mime', 'taille',
        'categorie', 'ordre', 'created_by',
    ];

    protected $casts = [
        'taille' => 'integer',
        'ordre'  => 'integer',
    ];

    protected $appends = ['url', 'taille_humaine', 'icone'];

    // ── Relations ─────────────────────────────────────────
    public function attachable()
    {
        return $this->morphTo();
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────
    public function getUrlAttribute(): string
    {
        return asset('storage/' . ltrim($this->chemin, '/'));
    }

    public function getTailleHumaineAttribute(): string
    {
        $bytes = $this->taille;
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' Go';
        if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' Mo';
        if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' Ko';
        return $bytes . ' o';
    }

    public function getIconeAttribute(): string
    {
        return match ($this->categorie) {
            'image'    => 'fa-image',
            'video'    => 'fa-video',
            'audio'    => 'fa-music',
            'document' => 'fa-file-lines',
            default    => 'fa-paperclip',
        };
    }

    /**
     * Détermine la catégorie d'un fichier à partir de son MIME.
     */
    public static function categoriserMime(?string $mime): string
    {
        if (! $mime) return 'autre';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (str_starts_with($mime, 'audio/')) return 'audio';
        if (in_array($mime, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain', 'text/csv',
        ])) return 'document';
        return 'autre';
    }
}
