<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ModificationBudgetaire extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'modification_budgetaires';

    protected $fillable = [
        // Legacy (conservés mais non utilisés par le nouveau workflow)
        'id_compte_emission', 'codecompte_emission',
        'id_compte_reception', 'codecompte_reception',
        'isbudgetligne',

        // Nouveaux champs
        'exercice_id',
        'budget_ligne_source_id', 'budget_ligne_destination_id',
        'type_modification',          // 'transfert' | 'ajout'
        'objetmodification',           // libellé court
        'montant_modification',
        'commentaire',
        'id_user',
        'statut',                       // 0=brouillon, 1=soumise, 2=approuvée, 3=appliquée, 4=rejetée
        'soumis_par', 'soumis_at',
        'approuve_par', 'approuve_at',
        'applique_par', 'applique_at',
        'motif_rejet',
    ];

    protected function casts(): array
    {
        return [
            'montant_modification' => 'decimal:2',
            'soumis_at'            => 'datetime',
            'approuve_at'          => 'datetime',
            'applique_at'          => 'datetime',
        ];
    }

    public const STATUTS = [
        0 => 'Brouillon',
        1 => 'Soumise',
        2 => 'Approuvée',
        3 => 'Appliquée',
        4 => 'Rejetée',
    ];

    public const STATUT_COULEURS = [
        0 => 'secondary',
        1 => 'info',
        2 => 'warning',
        3 => 'success',
        4 => 'danger',
    ];

    public const TYPES = [
        'transfert' => 'Transfert entre lignes',
        'ajout'     => 'Apport sur une ligne',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['statut', 'type_modification', 'montant_modification', 'objetmodification'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('modification_budgetaire')
            ->setDescriptionForEvent(fn(string $event) => "Modification budgétaire {$event}");
    }

    // ─── Relations ──────────────────────────────────────
    public function exercice()        { return $this->belongsTo(Exercice::class, 'exercice_id'); }
    public function ligneSource()     { return $this->belongsTo(BudgetLigne::class, 'budget_ligne_source_id'); }
    public function ligneDestination(){ return $this->belongsTo(BudgetLigne::class, 'budget_ligne_destination_id'); }
    public function user()            { return $this->belongsTo(User::class, 'id_user'); }
    public function soumetteur()      { return $this->belongsTo(User::class, 'soumis_par'); }
    public function approbateur()     { return $this->belongsTo(User::class, 'approuve_par'); }
    public function applicateur()     { return $this->belongsTo(User::class, 'applique_par'); }

    // Legacy
    public function compteEmission()  { return $this->belongsTo(Compte::class, 'id_compte_emission'); }
    public function compteReception() { return $this->belongsTo(Compte::class, 'id_compte_reception'); }

    // ─── Accessors ──────────────────────────────────────
    public function getStatutLibelleAttribute(): string  { return self::STATUTS[$this->statut] ?? '—'; }
    public function getStatutCouleurAttribute(): string  { return self::STATUT_COULEURS[$this->statut] ?? 'secondary'; }
    public function getTypeLibelleAttribute(): string    { return self::TYPES[$this->type_modification] ?? $this->type_modification; }

    public function getEstModifiableAttribute(): bool    { return in_array($this->statut, [0, 4]); }
    public function getEstSoumissibleAttribute(): bool   { return $this->statut === 0; }
    public function getEstApprouvableAttribute(): bool   { return $this->statut === 1; }
    public function getEstRejetableAttribute(): bool     { return $this->statut === 1; }
    public function getEstApplicableAttribute(): bool    { return $this->statut === 2; }

    // ─── Scopes ─────────────────────────────────────────
    public function scopeBrouillon($q)  { return $q->where('statut', 0); }
    public function scopeSoumise($q)    { return $q->where('statut', 1); }
    public function scopeApprouvee($q)  { return $q->where('statut', 2); }
    public function scopeAppliquee($q)  { return $q->where('statut', 3); }
    public function scopeRejetee($q)    { return $q->where('statut', 4); }
    public function scopeEnAttente($q)  { return $q->whereIn('statut', [1, 2]); }
}
