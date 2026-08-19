<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventaireLigne extends Model
{
    protected $table = 'inventaire_lignes';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quantite_theorique'    => 'decimal:3',
            'quantite_reelle'       => 'decimal:3',
            'quantite_bon_etat'     => 'decimal:3',
            'quantite_mauvais_etat' => 'decimal:3',
            'ecart' => 'decimal:3',
            'compte_at' => 'datetime',
        ];
    }

    public function inventaire() { return $this->belongsTo(Inventaire::class); }
    public function produit() { return $this->belongsTo(Produit::class); }
    public function emplacement() { return $this->belongsTo(Emplacement::class); }
    public function compteur() { return $this->belongsTo(User::class, 'compte_par'); }

    public function getEcartCouleurAttribute(): string
    {
        if ($this->quantite_reelle === null) return 'secondary';
        $ecart = (float) ($this->ecart ?? 0);
        if (abs($ecart) < 0.0005) return 'success';
        return $ecart > 0 ? 'info' : 'warning';
    }
}
