<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivraisonInterneLigne extends Model
{
    protected $table = 'livraison_interne_lignes';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['quantite' => 'decimal:3'];
    }

    public function livraison() { return $this->belongsTo(LivraisonInterne::class); }
    public function commandeLigne() { return $this->belongsTo(CommandeInterneLigne::class, 'commande_ligne_id'); }
}
