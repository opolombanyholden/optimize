<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'dateeffet', 'num', 'code', 'libelle', 'statut',
        'attribut1', 'attribut2', 'attribut3',
        'isvalide', 'id_user', 'id_pays', 'effacer',
    ];

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'id_pays');
    }

    public function localites()
    {
        return $this->hasMany(Localite::class, 'id_region');
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 1);
    }
}
