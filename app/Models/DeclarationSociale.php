<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DeclarationSociale extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'declarations_sociales';

    protected $fillable = [
        'code', 'type_organisme', 'annee', 'mois', 'trimestre',
        'periodicite', 'date_debut', 'date_fin', 'statut',
        'nombre_employes', 'total_brut', 'total_brut_plafonne',
        'total_cot_salariale', 'total_cot_patronale',
        'snapshot_employeur', 'commentaire',
        'created_by', 'validee_par', 'validee_at',
        'deposee_par', 'deposee_at', 'reference_depot',
        'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'date_debut'           => 'date',
            'date_fin'             => 'date',
            'validee_at'           => 'datetime',
            'deposee_at'           => 'datetime',
            'snapshot_employeur'   => 'array',
            'extra_attributes'     => 'array',
            'total_brut'           => 'decimal:2',
            'total_brut_plafonne'  => 'decimal:2',
            'total_cot_salariale'  => 'decimal:2',
            'total_cot_patronale'  => 'decimal:2',
        ];
    }

    public const STATUTS = [
        0 => 'Brouillon',
        1 => 'Validée',
        2 => 'Déposée',
    ];

    public const ORGANISMES = [
        'cnss'   => ['libelle' => 'CNSS',   'periodicite' => 'trimestrielle', 'plafond' => 1500000, 'taux_sal' => 2.5, 'taux_pat' => 16.0],
        'cnamgs' => ['libelle' => 'CNAMGS', 'periodicite' => 'mensuelle',     'plafond' => 2500000, 'taux_sal' => 2.0, 'taux_pat' => 4.1],
        'fnh'    => ['libelle' => 'FNH',    'periodicite' => 'mensuelle',     'plafond' => 0,       'taux_sal' => 0,   'taux_pat' => 2.0],
        'cfp'    => ['libelle' => 'CFP',    'periodicite' => 'mensuelle',     'plafond' => 0,       'taux_sal' => 0,   'taux_pat' => 0.5],
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'type_organisme', 'annee', 'mois', 'trimestre', 'statut',
                'nombre_employes', 'total_brut', 'total_cot_patronale'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('declaration_sociale')
            ->setDescriptionForEvent(fn(string $event) => "Déclaration sociale {$event}");
    }

    public function lignes()   { return $this->hasMany(DeclarationSocialeLigne::class); }
    public function createur() { return $this->belongsTo(User::class, 'created_by'); }
    public function validateur(){ return $this->belongsTo(User::class, 'validee_par'); }
    public function deposeur() { return $this->belongsTo(User::class, 'deposee_par'); }

    public function getOrganismeLibelleAttribute(): string
    {
        return self::ORGANISMES[$this->type_organisme]['libelle'] ?? strtoupper($this->type_organisme);
    }

    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? '—';
    }

    public function getPeriodeLibelleAttribute(): string
    {
        if ($this->periodicite === 'trimestrielle' && $this->trimestre) {
            return "T{$this->trimestre} {$this->annee}";
        }
        return \Carbon\Carbon::create($this->annee, $this->mois ?? 1, 1)->translatedFormat('F Y');
    }

    public function getEstModifiableAttribute(): bool
    {
        return $this->statut === 0;
    }
}
