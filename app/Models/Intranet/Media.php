<?php

namespace App\Models\Intranet;

use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use App\Traits\Intranet\HasLikes;
use App\Traits\Intranet\HasPublication;
use App\Traits\Intranet\HasVues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, SoftDeletes;

    protected $table = 'intranet_media';

    protected $fillable = [
        'slug', 'titre', 'description', 'type',
        'fichier', 'url_externe', 'plateforme', 'embed_id', 'thumbnail_url',
        'nom_original', 'mime_type', 'taille',
        'dossier', 'album', 'album_id', 'tags',
        'largeur', 'hauteur', 'duree_secondes',
        'is_public', 'telechargements_count', 'vues_count',
        'created_by',
    ];

    protected $casts = [
        'is_public'             => 'boolean',
        'tags'                  => 'array',
        'taille'                => 'integer',
        'largeur'               => 'integer',
        'hauteur'               => 'integer',
        'duree_secondes'        => 'integer',
        'telechargements_count' => 'integer',
        'vues_count'            => 'integer',
    ];

    protected $appends = ['fichier_url', 'taille_humaine', 'duree_humaine', 'icone', 'couleur', 'est_externe', 'embed_url', 'apercu_url'];

    protected static function booted(): void
    {
        static::creating(function (Media $m) {
            if (empty($m->slug)) {
                $m->slug = static::genererSlugUnique($m->titre);
            }
        });
    }

    protected static function genererSlugUnique(string $base): string
    {
        $slug = Str::slug($base) ?: 'media';
        $original = $slug;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function albumRelation()
    {
        return $this->belongsTo(MediaAlbum::class, 'album_id');
    }

    public function getFichierUrlAttribute(): ?string
    {
        if (! $this->fichier) return null;
        return asset('storage/' . ltrim($this->fichier, '/'));
    }

    public function getTailleHumaineAttribute(): string
    {
        $b = $this->taille ?? 0;
        if ($b >= 1073741824) return number_format($b / 1073741824, 2) . ' Go';
        if ($b >= 1048576)    return number_format($b / 1048576, 2) . ' Mo';
        if ($b >= 1024)       return number_format($b / 1024, 2) . ' Ko';
        return $b . ' o';
    }

    public function getDureeHumaineAttribute(): ?string
    {
        if (! $this->duree_secondes) return null;
        return sprintf('%d:%02d', intdiv($this->duree_secondes, 60), $this->duree_secondes % 60);
    }

    public function getDimensionsAttribute(): ?string
    {
        if (! $this->largeur || ! $this->hauteur) return null;
        return "{$this->largeur} × {$this->hauteur}";
    }

    public function getIconeAttribute(): string
    {
        return match ($this->type) {
            'image' => 'fa-image', 'video' => 'fa-video',
            'audio' => 'fa-music', 'document' => 'fa-file-lines',
            default => 'fa-file',
        };
    }

    public function getCouleurAttribute(): string
    {
        return match ($this->type) {
            'image' => '#7C3AED', 'video' => '#DC2626',
            'audio' => '#F59E0B', 'document' => '#0891B2',
            default => '#64748B',
        };
    }

    public function getEstImageAttribute(): bool { return $this->type === 'image'; }

    public function getEstExterneAttribute(): bool
    {
        return ! empty($this->url_externe);
    }

    /**
     * Retourne l'URL embed iframe pour les vidéos externes.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if (! $this->embed_id) return null;

        return match ($this->plateforme) {
            'youtube'     => "https://www.youtube.com/embed/{$this->embed_id}",
            'dailymotion' => "https://www.dailymotion.com/embed/video/{$this->embed_id}",
            'vimeo'       => "https://player.vimeo.com/video/{$this->embed_id}",
            default       => null,
        };
    }

    /**
     * Retourne la miniature de la vidéo externe.
     */
    public function getApercuUrlAttribute(): ?string
    {
        if ($this->thumbnail_url) return $this->thumbnail_url;
        if (! $this->embed_id) return null;

        return match ($this->plateforme) {
            'youtube'     => "https://img.youtube.com/vi/{$this->embed_id}/hqdefault.jpg",
            'dailymotion' => "https://www.dailymotion.com/thumbnail/video/{$this->embed_id}",
            'vimeo'       => null, // nécessite un appel API
            default       => null,
        };
    }

    /**
     * Parse une URL vidéo et extrait la plateforme + l'ID embed.
     */
    public static function parseVideoUrl(string $url): ?array
    {
        // YouTube : youtube.com/watch?v=ID, youtu.be/ID, youtube.com/embed/ID
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return ['plateforme' => 'youtube', 'embed_id' => $m[1]];
        }

        // Dailymotion : dailymotion.com/video/ID, dai.ly/ID
        if (preg_match('/(?:dailymotion\.com\/video\/|dai\.ly\/)([a-zA-Z0-9]+)/', $url, $m)) {
            return ['plateforme' => 'dailymotion', 'embed_id' => $m[1]];
        }

        // Vimeo : vimeo.com/ID
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return ['plateforme' => 'vimeo', 'embed_id' => $m[1]];
        }

        return ['plateforme' => 'autre', 'embed_id' => null];
    }
    public function getEstVideoAttribute(): bool { return $this->type === 'video'; }

    public function scopeImages(Builder $q): Builder { return $q->where('type', 'image'); }
    public function scopeVideos(Builder $q): Builder { return $q->where('type', 'video'); }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('titre', 'like', "%{$terme}%")
              ->orWhere('description', 'like', "%{$terme}%")
              ->orWhere('nom_original', 'like', "%{$terme}%")
              ->orWhere('album', 'like', "%{$terme}%");
        });
    }

    public static function typeDepuisMime(?string $mime): string
    {
        if (! $mime) return 'autre';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (str_starts_with($mime, 'audio/')) return 'audio';
        return 'document';
    }
}
