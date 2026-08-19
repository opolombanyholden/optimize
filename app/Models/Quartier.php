<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quartier extends Model
{
    protected $fillable = [
        'dateeffet', 'code', 'libelle', 'statut',
        'attribut1', 'attribut2', 'attribut3',
        'isvalide', 'id_user', 'effacer',
        'id_arrondissement', 'id_localite',
    ];

    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class, 'id_arrondissement');
    }

    public function localite()
    {
        return $this->belongsTo(Localite::class, 'id_localite');
    }
}
