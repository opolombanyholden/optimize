<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandeLigne extends Model
{
    protected $table = 'commande_lignes';

    protected $fillable = [
        'commande_id', 'produit_id', 'designation',
        'quantite_commandee', 'quantite_livree', 'prix_unitaire', 'montant',
        'ordre', 'observation',
    ];

    protected $casts = [
        'quantite_commandee' => 'decimal:3',
        'quantite_livree'    => 'decimal:3',
        'prix_unitaire'      => 'decimal:2',
        'montant'            => 'decimal:2',
        'ordre'              => 'integer',
    ];

    public function commande() { return $this->belongsTo(CommandeFournisseur::class, 'commande_id'); }
    public function produit()  { return $this->belongsTo(Produit::class, 'produit_id'); }

    /**
     * Reste à livrer sur cette ligne.
     */
    public function getResteALivrerAttribute(): float
    {
        return max(0, (float) $this->quantite_commandee - (float) $this->quantite_livree);
    }

    public function getEstEntierementLivreeAttribute(): bool
    {
        return (float) $this->quantite_livree >= (float) $this->quantite_commandee - 0.001;
    }

    protected static function booted(): void
    {
        // Auto-recalcule montant = quantite × prix
        static::saving(function (self $l) {
            if ($l->quantite_commandee !== null && $l->prix_unitaire !== null) {
                $l->montant = round((float) $l->quantite_commandee * (float) $l->prix_unitaire, 2);
            }
        });
    }
}
