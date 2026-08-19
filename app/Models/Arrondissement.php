<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arrondissement extends Model
{
    protected $fillable = [
        'dateeffet', 'code', 'libelle', 'statut',
        'attribut1', 'attribut2', 'attribut3',
        'isvalide', 'id_user', 'effacer', 'id_localite',
    ];

    public function localite()
    {
        return $this->belongsTo(Localite::class, 'id_localite');
    }

    public function quartiers()
    {
        return $this->hasMany(Quartier::class, 'id_arrondissement');
    }
}
