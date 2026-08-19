<?php

namespace App\Models\Finance;

use App\Models\Exercice;
use App\Models\Ligne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Budget = 1 enveloppe budgétaire par (ligne × exercice).
 * Le MONTANT est réparti sur plusieurs sources de financement (BudgetSource).
 * Conforme au schéma initial OPTIMIZE Finance (table `budgets`).
 */
class Budget extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'label', 'description', 'seuil', 'status',
        'ligne_id', 'exercice_id', 'extra',
    ];

    protected $casts = [
        'seuil' => 'decimal:2',
        'extra' => 'array',
    ];

    public const STATUTS = [
        0 => 'Brouillon',
        1 => 'Validé',
        2 => 'Verrouillé',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['label', 'seuil', 'status', 'ligne_id', 'exercice_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('budget')
            ->setDescriptionForEvent(fn(string $event) => "Budget {$event}");
    }

    public function ligne()    { return $this->belongsTo(Ligne::class); }
    public function exercice() { return $this->belongsTo(Exercice::class); }
    public function sources()  { return $this->hasMany(BudgetSource::class, 'ligne_id', 'ligne_id')
                                             ->where('exercice_id', $this->exercice_id); }

    // ─── Attributs calculés ─────────────────────────────
    public function getMontantTotalAttribute(): float
    {
        return (float) BudgetSource::where('ligne_id', $this->ligne_id)
            ->where('exercice_id', $this->exercice_id)
            ->sum('montant');
    }

    public function getStatusLibelleAttribute(): string
    {
        return self::STATUTS[$this->status] ?? '—';
    }

    public function scopeValide($q)   { return $q->where('status', 1); }
    public function scopeBrouillon($q){ return $q->where('status', 0); }
}
