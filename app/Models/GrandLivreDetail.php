<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrandLivreDetail extends Model
{
    protected $fillable = [
        'id_facture', 'id_produit', 'prix', 'quantite',
        'total_ht', 'total_taxe', 'total_reduction', 'total_ttc',
        'attribut1', 'attribut2', 'attribut3', 'effacer',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'id_produit');
    }
}
