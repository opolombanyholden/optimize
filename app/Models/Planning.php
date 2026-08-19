<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    protected $fillable = [
        'employee_id', 'date_jour',
        'heure_debut', 'heure_fin',
        'heure_debut_pause', 'heure_fin_pause',
        'heures_prevues', 'heures_reelles',
        'type_journee', 'lieu', 'notes',
        'extra_attributes', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_jour'         => 'date',
            'heures_prevues'    => 'decimal:2',
            'heures_reelles'    => 'decimal:2',
            'extra_attributes'  => 'array',
        ];
    }

    public const TYPES_JOURNEE = [
        'travail'  => 'Travail',
        'repos'    => 'Repos',
        'ferie'    => 'Jour férié',
        'conge'    => 'Congé',
        'absence'  => 'Absence',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTypeJourneeLibelleAttribute(): string
    {
        return self::TYPES_JOURNEE[$this->type_journee] ?? $this->type_journee;
    }
}
