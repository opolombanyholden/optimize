<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CampagnePaie extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'campagnes_paie';

    protected $fillable = [
        'code', 'libelle', 'annee', 'mois',
        'date_debut', 'date_fin', 'date_paiement_prevue',
        'periodicite', 'simulation', 'statut',
        'nombre_bulletins', 'masse_brute', 'masse_nette',
        'total_cotisations_sal', 'total_cotisations_pat',
        'commentaire', 'created_by', 'validee_par', 'validee_at',
        'cloturee_par', 'cloturee_at', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'date_debut'            => 'date',
            'date_fin'              => 'date',
            'date_paiement_prevue'  => 'date',
            'validee_at'            => 'datetime',
            'cloturee_at'           => 'datetime',
            'simulation'            => 'boolean',
            'masse_brute'           => 'decimal:2',
            'masse_nette'           => 'decimal:2',
            'total_cotisations_sal' => 'decimal:2',
            'total_cotisations_pat' => 'decimal:2',
            'extra_attributes'      => 'array',
        ];
    }

    public const STATUTS = [
        0 => 'Brouillon',
        1 => 'Générée',
        2 => 'Validée',
        3 => 'Payée',
        4 => 'Clôturée',
    ];

    public const STATUT_COULEURS = [
        0 => 'secondary',
        1 => 'info',
        2 => 'warning',
        3 => 'primary',
        4 => 'success',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'libelle', 'annee', 'mois', 'simulation', 'statut',
                'nombre_bulletins', 'masse_brute', 'masse_nette'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('campagne_paie')
            ->setDescriptionForEvent(fn(string $event) => "Campagne de paie {$event}");
    }

    // ─── Relations ──────────────────────────────
    public function bulletins() { return $this->hasMany(Paie::class, 'campagne_paie_id'); }
    public function employes()
    {
        return $this->belongsToMany(Employee::class, 'campagne_paie_employe')
            ->withPivot('primes_override', 'indemnites_override', 'heures_sup_override',
                'avances_override', 'retenues_override', 'notes', 'statut')
            ->withTimestamps();
    }
    public function createur()   { return $this->belongsTo(User::class, 'created_by'); }
    public function validateur() { return $this->belongsTo(User::class, 'validee_par'); }
    public function cloturePar() { return $this->belongsTo(User::class, 'cloturee_par'); }

    // ─── Accessors ──────────────────────────────
    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut] ?? '—'; }
    public function getStatutCouleurAttribute(): string { return self::STATUT_COULEURS[$this->statut] ?? 'secondary'; }
    public function getPeriodeLibelleAttribute(): string
    {
        return \Carbon\Carbon::create($this->annee, $this->mois, 1)->translatedFormat('F Y');
    }

    public function getEstModifiableAttribute(): bool
    {
        return in_array($this->statut, [0, 1]); // brouillon ou générée
    }
    public function getEstCloturableAttribute(): bool
    {
        return $this->statut === 3; // payée
    }

    // ─── Scopes ─────────────────────────────────
    public function scopeReelle($q)    { return $q->where('simulation', false); }
    public function scopeSimulation($q){ return $q->where('simulation', true); }
}
