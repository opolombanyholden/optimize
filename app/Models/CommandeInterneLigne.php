<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandeInterneLigne extends Model
{
    protected $table = 'commande_interne_lignes';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quantite_demandee' => 'decimal:3',
            'quantite_livree' => 'decimal:3',
            'prix_unitaire_estime' => 'decimal:2',
        ];
    }

    public function commande() { return $this->belongsTo(CommandeInterne::class, 'commande_interne_id'); }
    public function produit() { return $this->belongsTo(Produit::class); }

    public function getResteALivrerAttribute(): float
    {
        return max((float) $this->quantite_demandee - (float) $this->quantite_livree, 0);
    }

    public function getEstEntierementLivreeAttribute(): bool
    {
        return $this->reste_a_livrer <= 0;
    }
}
