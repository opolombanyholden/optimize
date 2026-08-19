<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivraisonFournisseurLigne extends Model
{
    protected $table = 'livraison_fournisseur_lignes';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['quantite' => 'decimal:3'];
    }

    public function livraison() { return $this->belongsTo(LivraisonFournisseur::class, 'livraison_id'); }
    public function commandeLigne() { return $this->belongsTo(CommandeLigne::class, 'commande_ligne_id'); }
    public function produit() { return $this->belongsTo(Produit::class); }
    public function emplacement() { return $this->belongsTo(Emplacement::class); }
}
