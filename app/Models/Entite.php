<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entite extends Model
{
    protected $fillable = [
        'libelle', 'code', 'id_localite',
        'attribut1', 'attribut2', 'attribut3', 'effacer',
        // Finance V2
        'label', 'description', 'extra',
    ];

    public function localite()
    {
        return $this->belongsTo(Localite::class, 'id_localite');
    }

    public function grandLivres()
    {
        return $this->hasMany(GrandLivre::class, 'id_entite');
    }

    public function immobilisations()
    {
        return $this->hasMany(Immobilisation::class, 'entite_id');
    }
}
