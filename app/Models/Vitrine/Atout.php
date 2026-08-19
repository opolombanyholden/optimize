<?php

namespace App\Models\Vitrine;

use Illuminate\Database\Eloquent\Model;

class Atout extends Model
{
    protected $table = 'vitrine_atouts';

    protected $fillable = ['titre', 'description', 'icone', 'gradient_from', 'gradient_to', 'ordre', 'est_actif'];

    protected $casts = ['est_actif' => 'boolean'];

    public function scopeActif($q) { return $q->where('est_actif', true); }
}
