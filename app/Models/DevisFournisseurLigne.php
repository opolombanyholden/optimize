<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevisFournisseurLigne extends Model
{
    protected $table = 'devis_fournisseur_lignes';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quantite'      => 'decimal:3',
            'prix_unitaire' => 'decimal:2',
            'montant'       => 'decimal:2',
        ];
    }

    public function devis() { return $this->belongsTo(DevisFournisseur::class, 'devis_id'); }

    protected static function booted(): void
    {
        static::saving(function (self $l) {
            $l->montant = round((float) $l->quantite * (float) $l->prix_unitaire, 2);
        });
    }
}
