<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanAction extends Model
{
    use SoftDeletes;

    protected $table = 'intranet_plans_action';

    protected $fillable = [
        'titre', 'description', 'objectif_id', 'responsable_id',
        'date_debut', 'date_echeance', 'date_realisation',
        'statut', 'priorite', 'avancement',
        'resultats_attendus', 'moyens_requis',
        'budget_estime', 'budget_reel',
        'created_by',
    ];

    protected $casts = [
        'date_debut'       => 'date',
        'date_echeance'    => 'date',
        'date_realisation' => 'date',
        'budget_estime'    => 'decimal:2',
        'budget_reel'      => 'decimal:2',
        'avancement'       => 'integer',
    ];

    // ── Relations ────────────────────────────────────────

    public function objectif()    { return $this->belongsTo(Objectif::class, 'objectif_id'); }
    public function responsable() { return $this->belongsTo(User::class, 'responsable_id'); }
    public function auteur()      { return $this->belongsTo(User::class, 'created_by'); }

    // ── Accessors ────────────────────────────────────────

    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            'planifie'  => '#94A3B8',
            'en_cours'  => '#0891B2',
            'realise'   => '#16A34A',
            'reporte'   => '#F59E0B',
            'annule'    => '#DC2626',
            default     => '#64748B',
        };
    }

    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'planifie'  => 'Planifié',
            'en_cours'  => 'En cours',
            'realise'   => 'Réalisé',
            'reporte'   => 'Reporté',
            'annule'    => 'Annulé',
            default     => ucfirst($this->statut),
        };
    }

    public function getPrioriteCouleurAttribute(): string
    {
        return match ($this->priorite) {
            'basse'    => '#94A3B8',
            'normale'  => '#0891B2',
            'haute'    => '#F59E0B',
            'urgente'  => '#DC2626',
            default    => '#64748B',
        };
    }

    public function estEnRetard(): bool
    {
        return ! in_array($this->statut, ['realise', 'annule'])
            && $this->date_echeance
            && $this->date_echeance->isPast();
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeActif($q)    { return $q->whereIn('statut', ['planifie', 'en_cours']); }
    public function scopeRealise($q)  { return $q->where('statut', 'realise'); }
}
