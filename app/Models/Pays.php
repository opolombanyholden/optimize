<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pays extends Model
{
    protected $table = 'pays';

    protected $fillable = [
        'code', 'zipcode', 'alpha2', 'alpha3',
        'nom_en_gb', 'nom_fr_fr', 'isvalide', 'statut',
    ];

    public function regions()
    {
        return $this->hasMany(Region::class, 'id_pays');
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 1);
    }
}
