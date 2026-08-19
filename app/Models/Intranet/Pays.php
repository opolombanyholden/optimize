<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;

class Pays extends Model
{
    protected $table = 'intranet_pays';

    protected $fillable = [
        'code_iso2', 'code_iso3', 'nom', 'nom_en',
        'indicatif_telephone', 'drapeau_emoji', 'continent', 'ordre',
    ];

    public function getNomCompletAttribute(): string
    {
        return trim(($this->drapeau_emoji ?? '') . ' ' . $this->nom);
    }
}
