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

class Annonce extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, SoftDeletes;

    protected $table = 'intranet_annonces';

    protected $fillable = [
        'title', 'slug', 'extrait', 'categorie', 'content',
        'created_by', 'is_public', 'is_urgent',
        'image', 'media_principal', 'media_principal_type',
        'date_debut', 'date_fin', 'epingle', 'couleur', 'vues_count',
    ];

    protected $casts = [
        'is_public'  => 'boolean',
        'is_urgent'  => 'boolean',
        'epingle'    => 'boolean',
        'date_debut' => 'date',
        'date_fin'   => 'date',
        'vues_count' => 'integer',
    ];

    protected $appends = ['media_url', 'est_actif', 'est_expire'];

    // ── Boot : génération automatique du slug ──────────────
    protected static function booted(): void
    {
        static::creating(function (Annonce $annonce) {
            if (empty($annonce->slug)) {
                $annonce->slug = static::genererSlugUnique($annonce->title);
            }
        });

        static::updating(function (Annonce $annonce) {
            if ($annonce->isDirty('title') && empty($annonce->getOriginal('slug'))) {
                $annonce->slug = static::genererSlugUnique($annonce->title);
            }
        });
    }

    protected static function genererSlugUnique(string $title): string
    {
        $base = Str::slug($title) ?: 'annonce';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
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

    public function getEstExpireAttribute(): bool
    {
        return $this->date_fin && $this->date_fin->lt(now()->startOfDay());
    }

    // ── Scopes ─────────────────────────────────────────────
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

    public function scopeUrgentes(Builder $q): Builder
    {
        return $q->where('is_urgent', true);
    }

    public function scopeEpinglees(Builder $q): Builder
    {
        return $q->where('epingle', true);
    }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('title',    'like', "%{$terme}%")
              ->orWhere('extrait', 'like', "%{$terme}%")
              ->orWhere('content', 'like', "%{$terme}%");
        });
    }
}
