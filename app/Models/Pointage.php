<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Pointage extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'pointages';

    protected $fillable = [
        'employee_id', 'date',
        'h_normales', 'h_sup', 'h_nuit', 'h_dimanche',
        'statut', 'motif', 'notes',
        'saisi_par', 'valide_par', 'valide_at',
        'extra_attributes',
        'mode_pointage', 'ip_address', 'user_agent',
        'heure_entree', 'heure_sortie',
        'requires_validation_n1', 'validation_n1_par', 'validation_n1_at',
        'validation_n1_decision', 'validation_n1_commentaire',
    ];

    protected function casts(): array
    {
        return [
            'date'                   => 'date',
            'h_normales'             => 'decimal:2',
            'h_sup'                  => 'decimal:2',
            'h_nuit'                 => 'decimal:2',
            'h_dimanche'             => 'decimal:2',
            'valide_at'              => 'datetime',
            'extra_attributes'       => 'array',
            'requires_validation_n1' => 'boolean',
            'validation_n1_at'       => 'datetime',
        ];
    }

    public const MODES = [
        'manuel'        => 'Saisie manuelle',
        'qr_code'       => 'Scan QR personnel',
        'qr_generique'  => 'Scan QR générique + PIN',
        'intranet'      => 'Intranet entreprise',
        'teletravail'   => 'Télétravail',
    ];

    public function getModeLibelleAttribute(): string
    {
        return self::MODES[$this->mode_pointage] ?? '—';
    }

    public function validationN1Par()
    {
        return $this->belongsTo(User::class, 'validation_n1_par');
    }

    public function scopeEnAttenteValidationN1($q)
    {
        return $q->where('requires_validation_n1', true)
                 ->whereNull('validation_n1_at');
    }

    public const STATUTS = [
        0 => 'Brouillon',
        1 => 'Validé',
        2 => 'Reporté en paie',
    ];

    public const STATUT_COULEURS = [
        0 => 'secondary',
        1 => 'success',
        2 => 'primary',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['employee_id', 'date', 'h_normales', 'h_sup', 'h_nuit', 'h_dimanche', 'statut'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('pointage')
            ->setDescriptionForEvent(fn($event) => "Pointage {$event}");
    }

    public function employee()    { return $this->belongsTo(Employee::class); }
    public function saisiPar()    { return $this->belongsTo(User::class, 'saisi_par'); }
    public function validePar()   { return $this->belongsTo(User::class, 'valide_par'); }

    public function getTotalAttribute(): float
    {
        return (float) $this->h_normales + (float) $this->h_sup
             + (float) $this->h_nuit + (float) $this->h_dimanche;
    }

    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? '—';
    }

    public function getStatutCouleurAttribute(): string
    {
        return self::STATUT_COULEURS[$this->statut] ?? 'secondary';
    }

    public function getEstModifiableAttribute(): bool
    {
        return $this->statut === 0;
    }

    public function scopeValide($q) { return $q->where('statut', 1); }
    public function scopePourPeriode($q, string $debut, string $fin)
    {
        return $q->whereBetween('date', [$debut, $fin]);
    }

    /**
     * Agrège les heures sup d'un employé sur une période (utilisé par PaieCalculator).
     * On ne prend que les pointages validés (statut=1) et on convertit en montant
     * via la rubrique HSUP_25 = heures × tarif horaire × 1.25.
     * Retourne le nombre d'heures sup totales (pas le montant).
     */
    public static function totalHeuresSup(int $employeeId, string $debut, string $fin): float
    {
        return (float) self::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$debut, $fin])
            ->whereIn('statut', [1, 2])
            ->sum('h_sup');
    }
}
