<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CongeSolde extends Model
{
    protected $table = 'conges_soldes';

    protected $fillable = [
        'employee_id', 'annee', 'type_conge',
        'droit_annuel', 'report_n_moins_1', 'acquis_periode',
        'pris_periode', 'en_attente',
        'date_debut_acquisition', 'date_fin_acquisition',
        'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'droit_annuel'           => 'decimal:2',
            'report_n_moins_1'       => 'decimal:2',
            'acquis_periode'         => 'decimal:2',
            'pris_periode'           => 'decimal:2',
            'en_attente'             => 'decimal:2',
            'solde_disponible'       => 'decimal:2',
            'date_debut_acquisition' => 'date',
            'date_fin_acquisition'   => 'date',
            'extra_attributes'       => 'array',
        ];
    }

    public const TYPES = [
        'annuel'      => 'Congés annuels',
        'rtt'         => 'RTT',
        'anciennete'  => 'Congés ancienneté',
        'exceptionnel'=> 'Congés exceptionnels',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTypeLibelleAttribute(): string
    {
        return self::TYPES[$this->type_conge] ?? $this->type_conge;
    }
}
