<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CritereEvaluation extends Model
{
    protected $table = 'criteres_evaluation';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'poids' => 'decimal:2',
            'echelle_min' => 'integer',
            'echelle_max' => 'integer',
        ];
    }

    public function theme() { return $this->belongsTo(ThemeEvaluation::class, 'theme_id'); }
}
