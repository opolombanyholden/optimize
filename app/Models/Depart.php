<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Depart extends Model
{
    use SoftDeletes;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['employee_id', 'type_depart', 'motif', 'date_notification', 'date_effet', 'indemnite_depart', 'statut'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('depart')
            ->setDescriptionForEvent(fn(string $event) => "Départ {$event}");
    }

    protected $fillable = [
        'employee_id', 'type_depart', 'motif', 'description',
        'date_notification', 'date_effet', 'date_solde_tout_compte',
        'preavis_effectue', 'indemnite_depart', 'solde_conges_paye',
        'certificat_travail_url', 'attestation_pole_emploi_url',
        'entretien_sortie_effectue', 'notes_entretien_sortie',
        'traite_par', 'fichiersjoin', 'extra_attributes', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_notification'         => 'date',
            'date_effet'                => 'date',
            'date_solde_tout_compte'    => 'date',
            'preavis_effectue'          => 'boolean',
            'entretien_sortie_effectue' => 'boolean',
            'indemnite_depart'          => 'decimal:2',
            'solde_conges_paye'         => 'decimal:2',
            'fichiersjoin'              => 'array',
            'extra_attributes'          => 'array',
        ];
    }

    public const TYPES = [
        'demission'                => 'Démission',
        'licenciement'             => 'Licenciement',
        'retraite'                 => 'Retraite',
        'fin_contrat'              => 'Fin de contrat (CDD)',
        'deces'                    => 'Décès',
        'rupture_conventionnelle'  => 'Rupture conventionnelle',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function traitant()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function getTypeLibelleAttribute(): string
    {
        return self::TYPES[$this->type_depart] ?? $this->type_depart;
    }
}
