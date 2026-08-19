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
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contact extends Model
{
    use SoftDeletes, HasPiecesJointes, HasVues, HasPublication, HasLikes, HasCommentaires;

    protected $table = 'intranet_contacts';

    protected $fillable = [
        'slug', 'civilite', 'nom', 'prenoms', 'email', 'telephone', 'mobile',
        'poste', 'photo', 'media_principal', 'media_principal_type', 'organisation_id',
        'linkedin', 'twitter', 'site_web',
        'adresse', 'pays_id', 'ville', 'langue', 'source',
        'tags', 'etiquette', 'categorie', 'date_naissance',
        'derniere_interaction_le', 'est_favori', 'vues_count',
        'notes', 'created_by',
    ];

    protected $casts = [
        'tags'                    => 'array',
        'est_favori'              => 'boolean',
        'date_naissance'          => 'date',
        'derniere_interaction_le' => 'datetime',
        'vues_count'              => 'integer',
    ];

    protected $appends = ['nom_complet', 'photo_url', 'media_url'];

    protected static function booted(): void
    {
        static::creating(function (Contact $c) {
            if (empty($c->slug)) {
                $c->slug = static::genererSlugUnique(trim(($c->prenoms ?? '') . ' ' . $c->nom));
            }
        });
    }

    protected static function genererSlugUnique(string $base): string
    {
        $slug = Str::slug($base) ?: 'contact';
        $original = $slug;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    // ── Relations ──────────────────────────────────────────
    public function organisation()
    {
        return $this->belongsTo(ContactOrganisation::class, 'organisation_id');
    }

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'pays_id');
    }

    public function opportunites()
    {
        return $this->hasMany(Opportunite::class, 'contact_id');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function interactions(): MorphMany
    {
        return $this->morphMany(CrmInteraction::class, 'interactable')->orderByDesc('date_interaction');
    }

    // ── Accessors ──────────────────────────────────────────
    public function getNomCompletAttribute(): string
    {
        return trim(($this->civilite ? $this->civilite . ' ' : '') . ($this->prenoms ?? '') . ' ' . $this->nom);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) return null;
        return asset('storage/' . ltrim($this->photo, '/'));
    }

    public function getMediaUrlAttribute(): ?string
    {
        if (! $this->media_principal) return null;
        return asset('storage/' . ltrim($this->media_principal, '/'));
    }

    public function getInitialesAttribute(): string
    {
        return strtoupper(substr($this->prenoms ?? $this->nom, 0, 1) . substr($this->nom, 0, 1));
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeFavoris(Builder $q): Builder
    {
        return $q->where('est_favori', true);
    }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('nom',         'like', "%{$terme}%")
              ->orWhere('prenoms',   'like', "%{$terme}%")
              ->orWhere('email',     'like', "%{$terme}%")
              ->orWhere('poste',     'like', "%{$terme}%")
              ->orWhere('telephone', 'like', "%{$terme}%");
        });
    }
}
