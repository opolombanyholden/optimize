<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Objectif extends Model
{
    use SoftDeletes;

    protected $table = 'intranet_objectifs';

    protected $fillable = [
        'code', 'titre', 'description', 'couleur', 'icone', 'ponderation',
        'portee', 'date_debut', 'date_fin', 'statut',
        'parent_id', 'responsable_id', 'service_id', 'created_by',
    ];

    protected $casts = [
        'date_debut'  => 'date',
        'date_fin'    => 'date',
        'ponderation' => 'integer',
    ];

    // ── Relations ────────────────────────────────────────

    public function kpi()           { return $this->hasMany(Kpi::class, 'objectif_id'); }
    public function evaluations()   { return $this->hasMany(Evaluation::class, 'objectif_id'); }
    public function parent()        { return $this->belongsTo(Objectif::class, 'parent_id'); }
    public function sousObjectifs() { return $this->hasMany(Objectif::class, 'parent_id'); }
    public function auteur()        { return $this->belongsTo(User::class, 'created_by'); }
    public function responsable()   { return $this->belongsTo(User::class, 'responsable_id'); }
    public function service()       { return $this->belongsTo(Service::class, 'service_id'); }
    public function projets()       { return $this->hasMany(Projet::class, 'objectif_id'); }
    public function taches()        { return $this->hasMany(Tache::class, 'objectif_id'); }
    public function plansAction()   { return $this->hasMany(PlanAction::class, 'objectif_id'); }

    // ── Accessors ────────────────────────────────────────

    public function getCouleurAffichageAttribute(): string
    {
        return $this->couleur ?? '#7C3AED';
    }

    public function getIconeAffichageAttribute(): string
    {
        return $this->icone ?? 'fa-bullseye';
    }

    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            'actif'        => '#0891B2',
            'atteint'      => '#16A34A',
            'non_atteint'  => '#DC2626',
            'abandonne'    => '#94A3B8',
            default        => '#64748B',
        };
    }

    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'actif'       => 'En cours',
            'atteint'     => 'Atteint',
            'non_atteint' => 'Non atteint',
            'abandonne'   => 'Abandonné',
            default       => ucfirst($this->statut),
        };
    }

    public function getPorteeLibelleAttribute(): string
    {
        return match ($this->portee) {
            'organisation' => 'Organisation',
            'service'      => 'Service',
            'equipe'       => 'Équipe',
            'individuel'   => 'Individuel',
            default        => ucfirst($this->portee ?? '—'),
        };
    }

    /**
     * Progression calculée depuis :
     *   1. Sous-objectifs (moyenne pondérée si ponderation > 0, sinon moyenne simple)
     *   2. Sinon depuis les KPI rattachés (moyenne progression)
     *   3. Sinon depuis les projets liés (moyenne avancement)
     */
    public function getProgressionAttribute(): int
    {
        // Niveau 1 : sous-objectifs
        $sousObj = $this->sousObjectifs;
        if ($sousObj->isNotEmpty()) {
            $totalPond = $sousObj->sum('ponderation');
            if ($totalPond > 0) {
                $somme = $sousObj->sum(fn($o) => $o->progression * ($o->ponderation ?? 0));
                return (int) round($somme / $totalPond);
            }
            return (int) round($sousObj->avg(fn($o) => $o->progression));
        }

        // Niveau 2 : KPI
        $kpi = $this->kpi;
        if ($kpi->isNotEmpty()) {
            return (int) round($kpi->avg(fn($k) => $k->progression));
        }

        // Niveau 3 : projets liés
        $projets = $this->projets;
        if ($projets->isNotEmpty()) {
            return (int) round($projets->avg(fn($p) => $p->avancement_real));
        }

        return $this->statut === 'atteint' ? 100 : 0;
    }

    public function estEnRetard(): bool
    {
        return $this->statut === 'actif' && $this->date_fin && $this->date_fin->isPast();
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeActif($q)        { return $q->where('statut', 'actif'); }
    public function scopeStrategique($q)  { return $q->whereNull('parent_id'); }
    public function scopeOperationnel($q) { return $q->whereNotNull('parent_id'); }
}
