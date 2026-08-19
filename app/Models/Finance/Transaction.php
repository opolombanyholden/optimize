<?php

namespace App\Models\Finance;

use App\Models\Compte;
use App\Models\Entite;
use App\Models\Exercice;
use App\Models\Ligne;
use App\Models\ModeReglement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Transaction financière (dépense ou recette).
 * Conforme au schéma initial : table `transactions` avec type + status + montant/restant + entite/compte/ligne/exercice/mode_reglement.
 *
 * TYPE   : 0=dépense, 1=recette
 * STATUS : 0=brouillon, 1=soumise, 2=validée, 3=payée/encaissée, 4=annulée
 */
class Transaction extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'transactions_v2';

    protected $fillable = [
        'type', 'status', 'montant', 'montant_restant',
        'montant_lettres', 'montant_restant_lettres',
        'date', 'devise', 'beneficiaire', 'beneficiaire_externe',
        'code', 'label', 'description', 'isvalide',
        'entite_id', 'compte_id', 'ligne_id', 'exercice_id',
        'mode_reglement_id', 'id_user',
        'file', 'file_comment', 'filepath', 'extra',
    ];

    protected $casts = [
        'date'                 => 'date',
        'montant'              => 'decimal:2',
        'montant_restant'      => 'decimal:2',
        'beneficiaire_externe' => 'boolean',
        'isvalide'             => 'boolean',
        'extra'                => 'array',
    ];

    public const TYPES = [
        0 => 'Dépense',
        1 => 'Recette',
    ];

    public const STATUTS = [
        0 => 'Brouillon',
        1 => 'Soumise',
        2 => 'Validée',
        3 => 'Payée/Encaissée',
        4 => 'Annulée',
    ];

    public const STATUT_COULEURS = [
        0 => 'secondary',
        1 => 'info',
        2 => 'warning',
        3 => 'success',
        4 => 'danger',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'type', 'status', 'montant', 'label'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('transaction')
            ->setDescriptionForEvent(fn(string $event) => "Transaction {$event}");
    }

    public function details()      { return $this->hasMany(TransactionDetail::class, 'transaction_id')->orderBy('id'); }
    public function entite()       { return $this->belongsTo(Entite::class); }
    public function compte()       { return $this->belongsTo(Compte::class); }
    public function ligne()        { return $this->belongsTo(Ligne::class); }
    public function exercice()     { return $this->belongsTo(Exercice::class); }
    public function modeReglement(){ return $this->belongsTo(ModeReglement::class); }
    public function user()         { return $this->belongsTo(User::class, 'id_user'); }

    public function getTypeLibelleAttribute(): string    { return self::TYPES[$this->type] ?? '—'; }
    public function getStatusLibelleAttribute(): string  { return self::STATUTS[$this->status] ?? '—'; }
    public function getStatusCouleurAttribute(): string  { return self::STATUT_COULEURS[$this->status] ?? 'secondary'; }

    public function getEstModifiableAttribute(): bool { return in_array($this->status, [0, 4]); }
    public function getEstSoumissibleAttribute(): bool{ return $this->status === 0; }
    public function getEstValidableAttribute(): bool  { return $this->status === 1; }
    public function getEstPayableAttribute(): bool    { return $this->status === 2; }

    public function scopeDepense($q)   { return $q->where('type', 0); }
    public function scopeRecette($q)   { return $q->where('type', 1); }
    public function scopeBrouillon($q) { return $q->where('status', 0); }
    public function scopeValidee($q)   { return $q->where('status', 2); }
}
