<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeLocalite extends Model
{
    protected $table = 'type_localites';

    protected $fillable = [
        'dateeffet', 'libelle',
        'attribut1', 'attribut2', 'attribut3',
        'isvalide', 'id_user', 'effacer',
    ];

    public function localites()
    {
        return $this->hasMany(Localite::class, 'id_typelocalite');
    }
}
