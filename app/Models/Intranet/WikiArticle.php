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

class WikiArticle extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, SoftDeletes;

    protected $table = 'intranet_wiki_articles';

    protected $fillable = [
        'titre', 'slug', 'extrait', 'contenu',
        'categorie_id', 'parent_id', 'ordre',
        'is_public', 'is_epingle', 'tags',
        'media_principal', 'media_principal_type',
        'temps_lecture', 'version', 'vues',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_public'  => 'boolean',
        'is_epingle' => 'boolean',
        'tags'       => 'array',
        'vues'       => 'integer',
        'version'    => 'integer',
        'temps_lecture' => 'integer',
        'ordre'      => 'integer',
    ];

    protected $appends = ['media_url'];

    protected static function booted(): void
    {
        static::saving(function (WikiArticle $a) {
            if (empty($a->slug)) {
                $base = Str::slug($a->titre) ?: 'article';
                $slug = $base; $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $a->id ?? 0)->exists()) { $slug = $base . '-' . $i++; }
                $a->slug = $slug;
            }
            if ($a->isDirty('contenu')) {
                $words = str_word_count(strip_tags($a->contenu ?? ''));
                $a->temps_lecture = max(1, (int) ceil($words / 200));
            }
        });
    }

    // Relations
    public function categorie() { return $this->belongsTo(WikiCategorie::class, 'categorie_id'); }
    public function auteur()    { return $this->belongsTo(User::class, 'created_by'); }
    public function editeur()   { return $this->belongsTo(User::class, 'updated_by'); }
    public function parent()    { return $this->belongsTo(WikiArticle::class, 'parent_id'); }
    public function enfants()   { return $this->hasMany(WikiArticle::class, 'parent_id')->orderBy('ordre'); }

    // Accessors
    public function getMediaUrlAttribute(): ?string
    {
        if (! $this->media_principal) return null;
        return asset('storage/' . ltrim($this->media_principal, '/'));
    }

    // Scopes
    public function scopeEpingles(Builder $q): Builder { return $q->where('is_epingle', true); }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('titre', 'like', "%{$terme}%")
              ->orWhere('extrait', 'like', "%{$terme}%")
              ->orWhere('contenu', 'like', "%{$terme}%");
        });
    }
}
