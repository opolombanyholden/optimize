<?php

namespace App\Models\Vitrine;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'vitrine_modules';

    protected $fillable = ['nom', 'description', 'icone', 'couleur', 'features', 'ordre', 'est_actif'];

    protected $casts = [
        'features'  => 'array',
        'est_actif' => 'boolean',
    ];

    public function scopeActif($q) { return $q->where('est_actif', true); }
}
