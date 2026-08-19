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

class Rapport extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, SoftDeletes;

    protected $table = 'intranet_rapports';

    protected $fillable = [
        'slug', 'titre', 'extrait', 'type', 'reference', 'contenu', 'statut',
        'evenement_id', 'projet_id', 'phase_id', 'tache_id', 'activite_id',
        'date_document',
        'media_principal', 'media_principal_type',
        'tags', 'participants', 'vues_count', 'created_by',
        'valideur_id', 'statut_validation', 'commentaire_validation',
        'valide_le', 'soumis_le',
    ];

    protected $casts = [
        'date_document' => 'date',
        'valide_le'     => 'datetime',
        'soumis_le'     => 'datetime',
        'tags'          => 'array',
        'participants'  => 'array',
        'vues_count'    => 'integer',
    ];

    protected $appends = ['media_url', 'type_libelle', 'statut_couleur'];

    protected static function booted(): void
    {
        static::creating(function (Rapport $r) {
            if (empty($r->slug)) {
                $base = Str::slug($r->titre) ?: 'rapport';
                $slug = $base; $i = 2;
                while (static::where('slug', $slug)->exists()) { $slug = $base . '-' . $i++; }
                $r->slug = $slug;
            }
            if (empty($r->reference)) {
                $prefix = match ($r->type) {
                    'cr' => 'CR', 'pv' => 'PV', default => 'RAP',
                };
                $year = now()->year;
                $count = static::whereYear('created_at', $year)->where('type', $r->type)->count() + 1;
                $r->reference = "{$prefix}-{$year}-" . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function auteur()     { return $this->belongsTo(User::class, 'created_by'); }
    public function evenement()  { return $this->belongsTo(Evenement::class, 'evenement_id'); }
    public function projet()     { return $this->belongsTo(Projet::class, 'projet_id'); }
    public function phase()      { return $this->belongsTo(ProjetPhase::class, 'phase_id'); }
    public function tache()      { return $this->belongsTo(Tache::class, 'tache_id'); }
    public function activite()   { return $this->belongsTo(Activite::class, 'activite_id'); }
    public function valideur()   { return $this->belongsTo(User::class, 'valideur_id'); }

    // ── Workflow de validation ─────────────────────────
    public function soumettre(User $valideur): void
    {
        $this->update([
            'statut'            => 'en_revision',
            'valideur_id'       => $valideur->id,
            'statut_validation' => 'en_attente',
            'soumis_le'         => now(),
        ]);
    }

    public function approuver(?string $commentaire = null): void
    {
        $this->update([
            'statut'                => 'valide',
            'statut_validation'     => 'approuve',
            'commentaire_validation'=> $commentaire,
            'valide_le'             => now(),
        ]);
    }

    public function rejeter(string $commentaire): void
    {
        $this->update([
            'statut'                => 'brouillon',
            'statut_validation'     => 'rejete',
            'commentaire_validation'=> $commentaire,
            'valide_le'             => now(),
        ]);
    }

    public function demanderRevisions(string $commentaire): void
    {
        $this->update([
            'statut'                => 'en_revision',
            'statut_validation'     => 'revisions_demandees',
            'commentaire_validation'=> $commentaire,
        ]);
    }

    public function getEstSoumisAttribute(): bool
    {
        return ! is_null($this->soumis_le) && $this->statut_validation === 'en_attente';
    }

    public function getValidationLibelleAttribute(): ?string
    {
        return match ($this->statut_validation) {
            'en_attente'           => 'En attente de validation',
            'approuve'             => 'Approuvé',
            'rejete'               => 'Rejeté',
            'revisions_demandees'  => 'Révisions demandées',
            default                => null,
        };
    }

    public function getValidationCouleurAttribute(): string
    {
        return match ($this->statut_validation) {
            'en_attente'          => '#F59E0B',
            'approuve'            => '#16A34A',
            'rejete'              => '#DC2626',
            'revisions_demandees' => '#D97706',
            default               => '#94A3B8',
        };
    }

    public function getMediaUrlAttribute(): ?string
    {
        if (! $this->media_principal) return null;
        return asset('storage/' . ltrim($this->media_principal, '/'));
    }

    public function getTypeLibelleAttribute(): string
    {
        return match ($this->type) {
            'rapport' => 'Rapport', 'cr' => 'Compte rendu',
            'pv' => 'Procès-verbal', 'note' => 'Note', 'memo' => 'Mémo',
            default => ucfirst((string) $this->type),
        };
    }

    public function getTypeCouleurAttribute(): string
    {
        return match ($this->type) {
            'rapport' => '#4F46E5', 'cr' => '#0891B2',
            'pv' => '#7C3AED', 'note' => '#F59E0B', 'memo' => '#059669',
            default => '#64748B',
        };
    }

    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            'brouillon' => '#94A3B8', 'en_revision' => '#F59E0B',
            'valide' => '#16A34A', 'publie' => '#4F46E5',
            default => '#64748B',
        };
    }

    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'brouillon' => 'Brouillon', 'en_revision' => 'En révision',
            'valide' => 'Validé', 'publie' => 'Publié',
            default => ucfirst((string) $this->statut),
        };
    }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('titre', 'like', "%{$terme}%")
              ->orWhere('reference', 'like', "%{$terme}%")
              ->orWhere('contenu', 'like', "%{$terme}%");
        });
    }
}
