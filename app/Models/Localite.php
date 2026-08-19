<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Localite extends Model
{
    protected $fillable = [
        'dateeffet', 'code', 'libelle', 'statut',
        'attribut1', 'attribut2', 'attribut3',
        'isvalide', 'id_user', 'effacer',
        'id_typelocalite', 'id_region', 'id_pays',
    ];

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'id_pays');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'id_region');
    }

    public function typeLocalite()
    {
        return $this->belongsTo(TypeLocalite::class, 'id_typelocalite');
    }

    public function arrondissements()
    {
        return $this->hasMany(Arrondissement::class, 'id_localite');
    }

    public function entites()
    {
        return $this->hasMany(Entite::class, 'id_localite');
    }
}
