<?php

namespace App\Models\Intranet;

use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use App\Traits\Intranet\HasLikes;
use App\Traits\Intranet\HasPiecesJointes;
use App\Traits\Intranet\HasPublication;
use App\Traits\Intranet\HasVues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ressource extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, SoftDeletes;

    protected $table = 'intranet_ressources';

    protected $fillable = [
        'slug', 'titre', 'description', 'type',
        'chemin', 'nom_original', 'mime_type', 'taille',
        'categorie', 'tags', 'icone',
        'is_public', 'acces_restreint',
        'parent_id', 'version',
        'telechargements_count', 'vues_count',
        'created_by',
    ];

    protected $casts = [
        'is_public'       => 'boolean',
        'acces_restreint' => 'boolean',
        'tags'            => 'array',
        'taille'          => 'integer',
        'version'         => 'integer',
        'telechargements_count' => 'integer',
        'vues_count'      => 'integer',
    ];

    protected $appends = ['fichier_url', 'taille_humaine', 'icone_affichee', 'est_dossier'];

    protected static function booted(): void
    {
        static::creating(function (Ressource $r) {
            if (empty($r->slug)) {
                $r->slug = static::genererSlugUnique($r->titre);
            }
        });
    }

    protected static function genererSlugUnique(string $base): string
    {
        $slug = Str::slug($base) ?: 'ressource';
        $original = $slug;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    // ── Relations ──────────────────────────────────────────
    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent()
    {
        return $this->belongsTo(Ressource::class, 'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(Ressource::class, 'parent_id')->orderByDesc('type')->orderBy('titre');
    }

    public function dossiers()
    {
        return $this->enfants()->where('type', 'dossier');
    }

    public function fichiers()
    {
        return $this->enfants()->where('type', 'fichier');
    }

    // ── Accessors ──────────────────────────────────────────
    public function getEstDossierAttribute(): bool
    {
        return $this->type === 'dossier';
    }

    public function getFichierUrlAttribute(): ?string
    {
        if (! $this->chemin) return null;
        return asset('storage/' . ltrim($this->chemin, '/'));
    }

    public function getTailleHumaineAttribute(): string
    {
        $b = $this->taille;
        if ($b >= 1073741824) return number_format($b / 1073741824, 2) . ' Go';
        if ($b >= 1048576)    return number_format($b / 1048576, 2) . ' Mo';
        if ($b >= 1024)       return number_format($b / 1024, 2) . ' Ko';
        return $b . ' o';
    }

    public function getIconeAfficheeAttribute(): string
    {
        if ($this->icone) return $this->icone;
        if ($this->type === 'dossier') return 'fa-folder';

        return match (true) {
            str_starts_with($this->mime_type ?? '', 'image/')       => 'fa-image',
            str_starts_with($this->mime_type ?? '', 'video/')       => 'fa-video',
            str_starts_with($this->mime_type ?? '', 'audio/')       => 'fa-music',
            in_array($this->mime_type, ['application/pdf'])         => 'fa-file-pdf',
            str_contains($this->mime_type ?? '', 'spreadsheet')     => 'fa-file-excel',
            str_contains($this->mime_type ?? '', 'presentation')    => 'fa-file-powerpoint',
            str_contains($this->mime_type ?? '', 'word')            => 'fa-file-word',
            default => 'fa-file',
        };
    }

    public function getCouleurIconeAttribute(): string
    {
        if ($this->type === 'dossier') return '#F59E0B';
        return match (true) {
            str_starts_with($this->mime_type ?? '', 'image/')       => '#7C3AED',
            str_starts_with($this->mime_type ?? '', 'video/')       => '#DC2626',
            in_array($this->mime_type, ['application/pdf'])         => '#DC2626',
            str_contains($this->mime_type ?? '', 'spreadsheet')     => '#16A34A',
            str_contains($this->mime_type ?? '', 'presentation')    => '#D97706',
            str_contains($this->mime_type ?? '', 'word')            => '#2563EB',
            default => '#64748B',
        };
    }

    /**
     * Retourne le fil d'ariane (breadcrumb) complet.
     */
    public function getCheminCompletAttribute(): array
    {
        $path = [];
        $current = $this;
        while ($current) {
            array_unshift($path, $current);
            $current = $current->parent;
        }
        return $path;
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeRacine(Builder $q): Builder
    {
        return $q->whereNull('parent_id');
    }

    public function scopeDossiers(Builder $q): Builder
    {
        return $q->where('type', 'dossier');
    }

    public function scopeFichiers(Builder $q): Builder
    {
        return $q->where('type', 'fichier');
    }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('titre',        'like', "%{$terme}%")
              ->orWhere('description', 'like', "%{$terme}%")
              ->orWhere('nom_original','like', "%{$terme}%")
              ->orWhere('categorie',   'like', "%{$terme}%");
        });
    }
}
