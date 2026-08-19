<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;

class SecteurActivite extends Model
{
    protected $table = 'intranet_secteurs_activite';

    protected $fillable = ['nom', 'code', 'couleur', 'icone', 'ordre'];

    public function organisations()
    {
        return $this->hasMany(ContactOrganisation::class, 'secteur_id');
    }
}
