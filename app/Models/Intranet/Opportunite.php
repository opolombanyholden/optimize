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

class Opportunite extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, SoftDeletes;

    protected $table = 'intranet_opportunites';

    protected $fillable = [
        'titre', 'slug', 'reference', 'description',
        'media_principal', 'media_principal_type',
        'contact_id', 'organisation_id', 'etape_id',
        'valeur', 'devise', 'probabilite',
        'date_echeance', 'date_creation_opp', 'date_cloture_reelle',
        'statut', 'raison_perte',
        'source', 'tags', 'vues_count', 'ordre_kanban',
        'notes', 'created_by', 'responsable_id',
    ];

    protected $appends = ['media_url'];

    protected $casts = [
        'date_echeance'        => 'date',
        'date_creation_opp'    => 'date',
        'date_cloture_reelle'  => 'date',
        'valeur'               => 'decimal:2',
        'probabilite'          => 'integer',
        'tags'                 => 'array',
        'vues_count'           => 'integer',
        'ordre_kanban'         => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Opportunite $o) {
            if (empty($o->slug)) {
                $o->slug = static::genererSlugUnique($o->titre);
            }
            if (empty($o->reference)) {
                $o->reference = 'OPP-' . str_pad((string)(static::max('id') + 1), 5, '0', STR_PAD_LEFT);
            }
            if (empty($o->date_creation_opp)) {
                $o->date_creation_opp = now()->toDateString();
            }
        });
    }

    protected static function genererSlugUnique(string $titre): string
    {
        $slug = Str::slug($titre) ?: 'opportunite';
        $original = $slug;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    // ── Relations ──────────────────────────────────────────
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function organisation()
    {
        return $this->belongsTo(ContactOrganisation::class, 'organisation_id');
    }

    public function etape()
    {
        return $this->belongsTo(CrmEtape::class, 'etape_id');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function interactions(): MorphMany
    {
        return $this->morphMany(CrmInteraction::class, 'interactable')->orderByDesc('date_interaction');
    }

    // ── Accessors ──────────────────────────────────────────
    public function getMediaUrlAttribute(): ?string
    {
        if (! $this->media_principal) return null;
        return asset('storage/' . ltrim($this->media_principal, '/'));
    }

    public function getValeurPondereeAttribute(): float
    {
        return (float) $this->valeur * ($this->probabilite / 100);
    }

    public function getEstGagneeAttribute(): bool
    {
        return $this->statut === 'gagnee';
    }

    public function getEstPerdueAttribute(): bool
    {
        return in_array($this->statut, ['perdue', 'abandonnee']);
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeOuvertes(Builder $q): Builder
    {
        return $q->where('statut', 'ouvert');
    }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('titre', 'like', "%{$terme}%")
              ->orWhere('reference', 'like', "%{$terme}%")
              ->orWhere('description', 'like', "%{$terme}%");
        });
    }
}
