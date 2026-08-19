<?php

namespace App\Models\Finance;

use App\Models\Exercice;
use App\Models\Ligne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Allocation d'un montant sur (source × ligne × exercice).
 * Table pivot enrichie. Un même trio (source, ligne, exercice) = allocation unique.
 * Les Modifs opèrent des transferts entre BudgetSource.
 */
class BudgetSource extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'label', 'description', 'montant',
        'source_id', 'ligne_id', 'exercice_id', 'extra',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'extra'   => 'array',
    ];

    public function source()   { return $this->belongsTo(Source::class); }
    public function ligne()    { return $this->belongsTo(Ligne::class); }
    public function exercice() { return $this->belongsTo(Exercice::class); }

    // Lien avec le budget (par ligne × exercice)
    public function budget()
    {
        return $this->hasOne(Budget::class, 'ligne_id', 'ligne_id')
            ->where('exercice_id', $this->exercice_id);
    }

    // Modifs sortantes/entrantes
    public function modifsEmises()   { return $this->hasMany(Modif::class, 'budget_emission_id'); }
    public function modifsRecues()   { return $this->hasMany(Modif::class, 'budget_reception_id'); }
}
