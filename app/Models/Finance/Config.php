<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Paramétrage clé/valeur (avec objet JSON complémentaire).
 * Conforme au schéma initial : table `configs`.
 */
class Config extends Model
{
    use SoftDeletes;

    protected $table = 'finance_configs';

    protected $fillable = ['key', 'value', 'config_object', 'extra'];

    protected $casts = [
        'config_object' => 'array',
        'extra'         => 'array',
    ];
}
