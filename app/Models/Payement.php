<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payement extends Model
{
    use SoftDeletes;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paie_id', 'employee_id', 'date_payement', 'montant', 'mode_payement', 'reference_payement', 'statut'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('payement')
            ->setDescriptionForEvent(fn(string $event) => "Règlement de paie {$event}");
    }

    protected $fillable = [
        'paie_id', 'employee_id', 'date_payement', 'montant',
        'mode_payement', 'reference_payement', 'banque', 'iban',
        'justificatif_url', 'execute_par',
        'extra_attributes', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_payement'    => 'date',
            'montant'          => 'decimal:2',
            'extra_attributes' => 'array',
        ];
    }

    public const MODES = [
        'virement'      => 'Virement bancaire',
        'cheque'        => 'Chèque',
        'especes'       => 'Espèces',
        'mobile_money'  => 'Mobile Money',
    ];

    public function paie() { return $this->belongsTo(Paie::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function executeur() { return $this->belongsTo(User::class, 'execute_par'); }

    public function getModeLibelleAttribute(): string
    {
        return self::MODES[$this->mode_payement] ?? $this->mode_payement;
    }
}
