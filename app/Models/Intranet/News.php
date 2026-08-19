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

class News extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, SoftDeletes;

    protected $table = 'intranet_news';

    protected $fillable = [
        'title', 'slug', 'extrait', 'rubrique', 'tags', 'content',
        'created_by', 'image',
        'media_principal', 'media_principal_type',
        'date_debut', 'date_fin', 'a_la_une',
        'auteur_signature', 'source', 'temps_lecture',
        'couleur', 'vues_count',
    ];

    protected $casts = [
        'tags'        => 'array',
        'a_la_une'    => 'boolean',
        'date_debut'  => 'date',
        'date_fin'    => 'date',
        'vues_count'  => 'integer',
        'temps_lecture' => 'integer',
    ];

    protected $appends = ['media_url', 'est_actif'];

    // ── Boot : auto-slug + temps de lecture ────────────────
    protected static function booted(): void
    {
        static::saving(function (News $news) {
            if (empty($news->slug)) {
                $news->slug = static::genererSlugUnique($news->title);
            }
            if ($news->isDirty('content')) {
                $news->temps_lecture = static::calculerTempsLecture($news->content);
            }
        });
    }

    protected static function genererSlugUnique(string $title): string
    {
        $base = Str::slug($title) ?: 'article';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    protected static function calculerTempsLecture(?string $html): int
    {
        if (! $html) return 0;
        $words = str_word_count(strip_tags($html));
        return max(1, (int) ceil($words / 200)); // 200 mots/minute
    }

    // ── Relations ──────────────────────────────────────────
    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accessors ──────────────────────────────────────────
    public function getMediaUrlAttribute(): ?string
    {
        if (! $this->media_principal) return null;
        return asset('storage/' . ltrim($this->media_principal, '/'));
    }

    public function getEstActifAttribute(): bool
    {
        $today = now()->startOfDay();
        if ($this->date_debut && $this->date_debut->gt($today)) return false;
        if ($this->date_fin   && $this->date_fin->lt($today))   return false;
        return true;
    }

    public function getSignatureAffichageAttribute(): string
    {
        return $this->auteur_signature
            ?: trim(($this->auteur->prenoms ?? '') . ' ' . ($this->auteur->name ?? ''));
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeAlaUne(Builder $q): Builder
    {
        return $q->where('a_la_une', true);
    }

    public function scopeActives(Builder $q): Builder
    {
        $today = now()->toDateString();
        return $q->where(function ($w) use ($today) {
                $w->whereNull('date_debut')->orWhere('date_debut', '<=', $today);
            })
            ->where(function ($w) use ($today) {
                $w->whereNull('date_fin')->orWhere('date_fin', '>=', $today);
            });
    }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('title',    'like', "%{$terme}%")
              ->orWhere('extrait', 'like', "%{$terme}%")
              ->orWhere('content', 'like', "%{$terme}%")
              ->orWhere('rubrique', 'like', "%{$terme}%");
        });
    }
}
