<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationPerformance extends Model
{
    use SoftDeletes;

    protected $table = 'evaluations_performance';

    protected $fillable = [
        'employee_id', 'evaluateur_id', 'periode',
        'date_evaluation', 'date_entretien',
        'note_globale', 'competences_evaluees',
        'points_forts', 'axes_amelioration', 'objectifs_periode_suivante',
        'commentaire_employe', 'commentaire_manager',
        'plan_developpement_individuel',
        'signature_employe', 'signature_manager',
        'fichiersjoin', 'extra_attributes', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_evaluation'      => 'date',
            'date_entretien'       => 'date',
            'signature_employe'    => 'boolean',
            'signature_manager'    => 'boolean',
            'competences_evaluees' => 'array',
            'fichiersjoin'         => 'array',
            'extra_attributes'     => 'array',
        ];
    }

    public const STATUTS = [
        0 => 'Brouillon',
        1 => 'Soumis à l\'employé',
        2 => 'Validé par employé',
        3 => 'Clôturé',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluateur()
    {
        return $this->belongsTo(User::class, 'evaluateur_id');
    }

    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? '—';
    }
}
