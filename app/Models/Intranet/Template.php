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

class Template extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, SoftDeletes;

    protected $table = 'intranet_templates';

    protected $fillable = [
        'slug', 'titre', 'description', 'contenu', 'format',
        'fichier_modele', 'nom_original', 'mime_type', 'taille',
        'categorie_id', 'is_public', 'variables', 'tags',
        'utilisations', 'vues_count', 'created_by',
    ];

    protected $casts = [
        'is_public'    => 'boolean',
        'variables'    => 'array',
        'tags'         => 'array',
        'utilisations' => 'integer',
        'vues_count'   => 'integer',
        'taille'       => 'integer',
    ];

    protected $appends = ['fichier_url', 'taille_humaine', 'icone', 'est_html'];

    protected static function booted(): void
    {
        static::creating(function (Template $t) {
            if (empty($t->slug)) {
                $base = Str::slug($t->titre) ?: 'template';
                $slug = $base; $i = 2;
                while (static::where('slug', $slug)->exists()) { $slug = $base . '-' . $i++; }
                $t->slug = $slug;
            }
        });
    }

    // Relations
    public function categorie() { return $this->belongsTo(TemplateCategorie::class, 'categorie_id'); }
    public function auteur() { return $this->belongsTo(User::class, 'created_by'); }

    // Accessors
    public function getFichierUrlAttribute(): ?string
    {
        if (! $this->fichier_modele) return null;
        return asset('storage/' . ltrim($this->fichier_modele, '/'));
    }

    public function getTailleHumaineAttribute(): string
    {
        $b = $this->taille ?? 0;
        if ($b >= 1048576) return number_format($b / 1048576, 2) . ' Mo';
        if ($b >= 1024) return number_format($b / 1024, 2) . ' Ko';
        return $b . ' o';
    }

    public function getIconeAttribute(): string
    {
        return $this->categorie?->icone ?? 'fa-file';
    }

    public function getCouleurAttribute(): string
    {
        return $this->categorie?->couleur ?? '#7C3AED';
    }

    public function getEstHtmlAttribute(): bool
    {
        return $this->format === 'html';
    }

    /**
     * Extrait les variables {{xxx}} du contenu HTML.
     */
    public function detecterVariables(): array
    {
        if (! $this->contenu) return [];
        preg_match_all('/\{\{(\w+)\}\}/', strip_tags($this->contenu), $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Remplace les variables {{xxx}} par les valeurs fournies et retourne le HTML.
     */
    public function genererDocument(array $valeurs): string
    {
        $html = $this->contenu ?? '';
        foreach ($valeurs as $key => $value) {
            $html = str_replace('{{' . $key . '}}', e($value), $html);
        }
        return $html;
    }

    // Scopes
    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('titre', 'like', "%{$terme}%")
              ->orWhere('description', 'like', "%{$terme}%");
        });
    }
}
