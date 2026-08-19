<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeEvaluation extends Model
{
    protected $table = 'themes_evaluation';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function criteres() { return $this->hasMany(CritereEvaluation::class, 'theme_id')->orderBy('ordre'); }
}
