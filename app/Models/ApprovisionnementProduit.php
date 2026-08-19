<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovisionnementProduit extends Model
{
    protected $table = 'approvisionnementsproduits';

    protected $fillable = [
        'approvisionnement_id', 'produit_id',
        'quantite_demandee', 'quantite_recue',
        'prix_unitaire', 'montant_ht', 'montant_tva', 'montant_ttc',
        'commentaire',
    ];

    public function approvisionnement()
    {
        return $this->belongsTo(Approvisionnement::class, 'approvisionnement_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }
}
