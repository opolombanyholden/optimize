<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeEvenementCarriere extends Model
{
    use SoftDeletes;

    protected $table = 'typesevenementscarrieres';

    protected $fillable = [
        'code', 'libelle', 'description', 'ordre', 'statut', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return ['extra_attributes' => 'array'];
    }

    public function evenements()
    {
        return $this->hasMany(EvenementCarriere::class, 'typesevenementscarriere_id');
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 1);
    }
}
