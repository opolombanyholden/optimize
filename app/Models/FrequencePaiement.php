<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FrequencePaiement extends Model
{
    use SoftDeletes;

    protected $table = 'frequences_paiement';

    protected $fillable = ['code', 'libelle', 'description', 'mois_increment', 'ordre', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean', 'ordre' => 'integer', 'mois_increment' => 'integer'];
    }

    public function scopeActifs($q) { return $q->where('actif', true); }
}
