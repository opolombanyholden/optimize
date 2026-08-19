<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OperationFinanciere extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'operations_financieres';

    protected $fillable = [
        'numero', 'type_operation',
        'exercice_id', 'budget_ligne_id',
        'tiers_type', 'tiers_id',
        'facture_id',
        'date_operation', 'objet', 'montant',
        'mode_reglement', 'reference_reglement',
        'statut',
        'soumis_par', 'soumis_at',
        'approuve_par', 'approuve_at',
        'execute_par', 'execute_at',
        'motif_rejet',
        'commentaire', 'created_by', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'date_operation'  => 'date',
            'soumis_at'       => 'datetime',
            'approuve_at'     => 'datetime',
            'execute_at'      => 'datetime',
            'montant'         => 'decimal:2',
            'extra_attributes'=> 'array',
        ];
    }

    public const STATUTS = [
        0 => 'Brouillon',
        1 => 'Soumise',
        2 => 'Approuvée',
        3 => 'Exécutée',
        4 => 'Rejetée',
        5 => 'Annulée',
    ];

    public const STATUT_COULEURS = [
        0 => 'secondary',
        1 => 'info',
        2 => 'warning',
        3 => 'success',
        4 => 'danger',
        5 => 'dark',
    ];

    public const TYPES = [
        'depense' => 'Ordre de dépense',
        'recette' => 'Ordre de recette',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'type_operation', 'montant', 'statut'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('operation_financiere')
            ->setDescriptionForEvent(fn(string $event) => "Opération financière {$event}");
    }

    public function exercice()      { return $this->belongsTo(Exercice::class); }
    public function budgetLigne()   { return $this->belongsTo(BudgetLigne::class); }
    public function facture()       { return $this->belongsTo(Facture::class); }
    public function details()       { return $this->hasMany(OperationFinanciereDetail::class)->orderBy('ordre'); }
    public function soumetteur()    { return $this->belongsTo(User::class, 'soumis_par'); }
    public function approbateur()   { return $this->belongsTo(User::class, 'approuve_par'); }
    public function executeur()     { return $this->belongsTo(User::class, 'execute_par'); }
    public function createur()      { return $this->belongsTo(User::class, 'created_by'); }

    public function tiersResolu()
    {
        if (!$this->tiers_id) return null;
        $class = Facture::TIERS_TYPES[$this->tiers_type] ?? null;
        if (!$class) return null;
        if ($class === \App\Models\Intranet\ContactOrganisation::class) {
            return \App\Models\Intranet\ContactOrganisation::where('id', $this->tiers_id)
                ->where('type', $this->tiers_type)
                ->first();
        }
        return $class::find($this->tiers_id);
    }

    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut] ?? '—'; }
    public function getStatutCouleurAttribute(): string { return self::STATUT_COULEURS[$this->statut] ?? 'secondary'; }
    public function getTypeLibelleAttribute(): string   { return self::TYPES[$this->type_operation] ?? $this->type_operation; }

    public function getEstModifiableAttribute(): bool  { return in_array($this->statut, [0, 4]); }
    public function getEstSoumissibleAttribute(): bool { return $this->statut === 0; }
    public function getEstApprouvableAttribute(): bool { return $this->statut === 1; }
    public function getEstRejetableAttribute(): bool   { return $this->statut === 1; }
    public function getEstExecutableAttribute(): bool  { return $this->statut === 2; }
    public function getEstAnnulableAttribute(): bool   { return in_array($this->statut, [2, 3]); }

    public function scopeDepense($q) { return $q->where('type_operation', 'depense'); }
    public function scopeRecette($q) { return $q->where('type_operation', 'recette'); }
    public function scopeEnAttente($q) { return $q->whereIn('statut', [1, 2]); }
    public function scopeExecutee($q) { return $q->where('statut', 3); }
}
