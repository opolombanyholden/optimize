<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeReglement extends Model
{
    protected $fillable = [
        'dateeffet', 'code', 'libelle', 'description',
        'statut', 'attribut1', 'attribut2', 'attribut3',
        'isvalide', 'id_user', 'effacer',
        // Finance V2
        'label', 'extra',
    ];

    protected function casts(): array
    {
        return [
            'dateeffet' => 'datetime',
        ];
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 1);
    }
}
