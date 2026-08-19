<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Paie extends Model
{
    use SoftDeletes;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'employee_id', 'debut', 'fin',
                'salaire_base', 'brut', 'cotisations_salariales',
                'cotisations_patronales', 'irpp', 'net_imposable',
                'avances', 'retenues', 'net_a_payer', 'statut',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('paie')
            ->setDescriptionForEvent(fn(string $event) => "Bulletin de paie {$event}");
    }

    protected $table = 'gpaies';

    protected $fillable = [
        'label', 'introduction', 'description', 'debut', 'fin',
        'employee_id', 'salaire_base', 'primes', 'indemnites',
        'heures_sup', 'brut', 'cotisations_salariales',
        'cotisations_patronales', 'irpp', 'net_imposable',
        'net_a_payer', 'avances', 'retenues',
        'fichiersjoin', 'statut', 'extra_attributes',
        // ANPI bulletin fields
        'numero_bulletin', 'mode_reglement', 'compte_bancaire', 'part_impots',
        'cumul_annuel_brut', 'cumul_annuel_net_imposable',
        'cumul_annuel_cotisations_sal', 'cumul_annuel_cotisations_pat',
        'cumul_annuel_irpp', 'cumul_annuel_net_a_payer',
        'brut_conges_mois', 'brut_conges_cumul',
        'nb_jrs_acquis_mois', 'nb_jrs_acquis_annee',
        'reste_a_prendre', 'acquis_n_moins_1',
        'snapshot_employe',
        'campagne_paie_id',
    ];

    protected function casts(): array
    {
        return [
            'debut' => 'datetime',
            'fin' => 'datetime',
            'extra_attributes' => 'array',
            'snapshot_employe' => 'array',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function rubriques()
    {
        return $this->belongsToMany(Rubrique::class, 'gpaie_rubrique', 'paie_id', 'rubrique_id')
            ->withPivot('base', 'taux', 'montant', 'imposable', 'cotisable')
            ->withTimestamps();
    }

    public function payements()
    {
        return $this->hasMany(Payement::class, 'paie_id');
    }

    public function campagne()
    {
        return $this->belongsTo(CampagnePaie::class, 'campagne_paie_id');
    }

    public function getMontantPayeAttribute(): float
    {
        return (float) $this->payements()->where('statut', 1)->sum('montant');
    }

    public function getResteAPayerAttribute(): float
    {
        return max(0, (float) $this->net_a_payer - $this->montant_paye);
    }

    public function getEstPayeAttribute(): bool
    {
        return $this->net_a_payer > 0 && $this->reste_a_payer == 0;
    }

    public function scopeBrouillon($query) { return $query->where('statut', 0); }
    public function scopeValide($query) { return $query->where('statut', 1); }
    public function scopePaye($query) { return $query->where('statut', 2); }
}
