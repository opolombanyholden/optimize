<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Source de financement (référentiel).
 * Exemples : FP (Fonds Propres), RB (Reports Budgétaires), ETAT (Dotation de l'État).
 * Extensible : l'utilisateur peut créer de nouvelles sources selon ses conventions.
 */
class Source extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'label', 'description', 'extra'];

    protected $casts = ['extra' => 'array'];

    public function budgetSources()
    {
        return $this->hasMany(BudgetSource::class);
    }
}
