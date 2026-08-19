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

class Evenement extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, SoftDeletes;

    protected $table = 'intranet_evenements';

    protected $fillable = [
        'titre', 'slug', 'extrait', 'description',
        'date_debut', 'date_fin', 'journee_entiere',
        'lieu', 'lieu_url', 'est_visio', 'lien_visio',
        'media_principal', 'media_principal_type', 'couleur',
        'statut', 'is_public', 'type_evenement_id', 'created_by',
        'recurrence', 'recurrence_jusqu_au', 'parent_recurrence_id',
        'capacite_max', 'inscription_requise', 'rappel_minutes', 'vues_count',
    ];

    protected $casts = [
        'date_debut'           => 'datetime',
        'date_fin'             => 'datetime',
        'journee_entiere'      => 'boolean',
        'is_public'            => 'boolean',
        'est_visio'            => 'boolean',
        'inscription_requise'  => 'boolean',
        'recurrence_jusqu_au'  => 'date',
        'capacite_max'         => 'integer',
        'rappel_minutes'       => 'integer',
        'vues_count'           => 'integer',
    ];

    protected $appends = ['media_url', 'couleur_affichee'];

    // ── Boot : auto-slug ───────────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (Evenement $e) {
            if (empty($e->slug)) {
                $e->slug = static::genererSlugUnique($e->titre);
            }
        });
    }

    protected static function genererSlugUnique(string $titre): string
    {
        $base = Str::slug($titre) ?: 'evenement';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    // ── Relations ──────────────────────────────────────────
    public function type()
    {
        return $this->belongsTo(TypeEvenement::class, 'type_evenement_id');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'intranet_evenement_participants', 'evenement_id', 'user_id')
            ->withPivot('statut', 'reponse', 'repondu_le')
            ->withTimestamps();
    }

    public function participantsConfirmes()
    {
        return $this->participants()->wherePivot('statut', 'confirme');
    }

    public function invitesExternes()
    {
        return $this->hasMany(EvenementInviteExterne::class, 'evenement_id');
    }

    // Récurrence : enfants/parent
    public function occurrences()
    {
        return $this->hasMany(Evenement::class, 'parent_recurrence_id');
    }

    public function evenementParent()
    {
        return $this->belongsTo(Evenement::class, 'parent_recurrence_id');
    }

    // ── Accessors ──────────────────────────────────────────
    public function getMediaUrlAttribute(): ?string
    {
        if (! $this->media_principal) return null;
        return asset('storage/' . ltrim($this->media_principal, '/'));
    }

    public function getCouleurAffichee(): string
    {
        return $this->getCouleurAfficheeAttribute();
    }

    public function getCouleurAfficheeAttribute(): string
    {
        return $this->couleur ?: ($this->type?->couleur ?? '#7C3AED');
    }

    public function getEstPasseAttribute(): bool
    {
        return $this->date_fin && $this->date_fin->lt(now());
    }

    public function getEstEnCoursAttribute(): bool
    {
        return $this->date_debut && $this->date_fin
            && $this->date_debut->lte(now())
            && $this->date_fin->gte(now());
    }

    public function getDureeMinutesAttribute(): int
    {
        if (!$this->date_debut || !$this->date_fin) return 0;
        return (int) $this->date_debut->diffInMinutes($this->date_fin);
    }

    public function getDureeHumainAttribute(): string
    {
        $min = $this->duree_minutes;
        if ($min < 60) return $min . ' min';
        $h = intdiv($min, 60);
        $r = $min % 60;
        return $r ? "{$h}h{$r}" : "{$h}h";
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeAVenir(Builder $q): Builder
    {
        return $q->where('date_debut', '>=', now())
                 ->where('statut', '!=', 'annule');
    }

    public function scopePasses(Builder $q): Builder
    {
        return $q->where('date_fin', '<', now());
    }

    public function scopeEntreDates(Builder $q, $debut, $fin): Builder
    {
        return $q->where(function ($w) use ($debut, $fin) {
            $w->whereBetween('date_debut', [$debut, $fin])
              ->orWhereBetween('date_fin', [$debut, $fin])
              ->orWhere(function ($w2) use ($debut, $fin) {
                  $w2->where('date_debut', '<=', $debut)
                     ->where('date_fin',   '>=', $fin);
              });
        });
    }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('titre',       'like', "%{$terme}%")
              ->orWhere('extrait',     'like', "%{$terme}%")
              ->orWhere('description', 'like', "%{$terme}%")
              ->orWhere('lieu',        'like', "%{$terme}%");
        });
    }

    // ── Helpers récurrence ─────────────────────────────────
    /**
     * Génère les occurrences récurrentes à partir de l'événement parent.
     */
    public function genererOccurrences(): int
    {
        if ($this->recurrence === 'aucune' || ! $this->recurrence_jusqu_au) {
            return 0;
        }

        $count = 0;
        $courant = $this->date_debut->copy();
        $duree   = $this->date_debut->diffInMinutes($this->date_fin);
        $limite  = $this->recurrence_jusqu_au->copy()->endOfDay();

        while (true) {
            $courant = match ($this->recurrence) {
                'quotidienne'  => $courant->copy()->addDay(),
                'hebdomadaire' => $courant->copy()->addWeek(),
                'mensuelle'    => $courant->copy()->addMonth(),
                'annuelle'     => $courant->copy()->addYear(),
                default        => $courant->copy()->addDay(),
            };

            if ($courant->gt($limite)) break;

            static::create([
                'titre'                => $this->titre,
                'extrait'              => $this->extrait,
                'description'          => $this->description,
                'date_debut'           => $courant,
                'date_fin'             => $courant->copy()->addMinutes($duree),
                'journee_entiere'      => $this->journee_entiere,
                'lieu'                 => $this->lieu,
                'lieu_url'             => $this->lieu_url,
                'est_visio'            => $this->est_visio,
                'lien_visio'           => $this->lien_visio,
                'media_principal'      => $this->media_principal,
                'media_principal_type' => $this->media_principal_type,
                'couleur'              => $this->couleur,
                'statut'               => $this->statut,
                'is_public'            => $this->is_public,
                'type_evenement_id'    => $this->type_evenement_id,
                'created_by'           => $this->created_by,
                'recurrence'           => 'aucune',
                'parent_recurrence_id' => $this->id,
                'capacite_max'         => $this->capacite_max,
                'inscription_requise'  => $this->inscription_requise,
                'rappel_minutes'       => $this->rappel_minutes,
            ]);
            $count++;
            if ($count > 365) break; // garde-fou
        }

        return $count;
    }
}
