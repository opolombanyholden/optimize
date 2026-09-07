<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilleDysfonctionnement extends Model
{
    use SoftDeletes;

    protected $table = 'familles_dysfonctionnement';

    protected $fillable = ['libelle', 'description', 'couleur'];

    public function types()
    {
        return $this->hasMany(TypeDysfonctionnement::class, 'famille_id');
    }
}
