<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduitEmplacement extends Model
{
    protected $table = 'produit_emplacements';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['quantite' => 'decimal:3'];
    }

    public function produit() { return $this->belongsTo(Produit::class); }
    public function emplacement() { return $this->belongsTo(Emplacement::class); }
}
