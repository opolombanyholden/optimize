<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Modification budgétaire : transfert d'un montant entre 2 BudgetSource.
 * Conforme au schéma initial : table `modifs` avec budget_emission_id / budget_reception_id (FK vers budget_sources).
 *
 * Workflow : Brouillon (0) → Soumise (1) → Approuvée (2) → Appliquée (3) OU Rejetée (4).
 * L'application transfère effectivement le montant entre les deux BudgetSource.
 */
class Modif extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'modifs';

    protected $fillable = [
        'comment', 'date', 'montant',
        'budget_emission_id', 'budget_reception_id',
        'status',
        'soumis_par', 'soumis_at',
        'approuve_par', 'approuve_at',
        'applique_par', 'applique_at',
        'motif_rejet', 'extra',
    ];

    protected $casts = [
        'date'        => 'date',
        'montant'     => 'decimal:2',
        'soumis_at'   => 'datetime',
        'approuve_at' => 'datetime',
        'applique_at' => 'datetime',
        'extra'       => 'array',
    ];

    public const STATUTS = [
        0 => 'Brouillon',
        1 => 'Soumise',
        2 => 'Approuvée',
        3 => 'Appliquée',
        4 => 'Rejetée',
    ];

    public const STATUT_COULEURS = [
        0 => 'secondary',
        1 => 'info',
        2 => 'warning',
        3 => 'success',
        4 => 'danger',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['comment', 'montant', 'budget_emission_id', 'budget_reception_id', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('modif')
            ->setDescriptionForEvent(fn(string $event) => "Modification budgétaire {$event}");
    }

    public function budgetEmission()  { return $this->belongsTo(BudgetSource::class, 'budget_emission_id'); }
    public function budgetReception() { return $this->belongsTo(BudgetSource::class, 'budget_reception_id'); }
    public function soumetteur()      { return $this->belongsTo(User::class, 'soumis_par'); }
    public function approbateur()     { return $this->belongsTo(User::class, 'approuve_par'); }
    public function applicateur()     { return $this->belongsTo(User::class, 'applique_par'); }

    public function getStatusLibelleAttribute(): string  { return self::STATUTS[$this->status] ?? '—'; }
    public function getStatusCouleurAttribute(): string  { return self::STATUT_COULEURS[$this->status] ?? 'secondary'; }

    public function getEstModifiableAttribute(): bool  { return in_array($this->status, [0, 4]); }
    public function getEstSoumissibleAttribute(): bool { return $this->status === 0; }
    public function getEstApprouvableAttribute(): bool { return $this->status === 1; }
    public function getEstRejetableAttribute(): bool   { return $this->status === 1; }
    public function getEstApplicableAttribute(): bool  { return $this->status === 2; }
}
