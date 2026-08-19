<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Approvisionnement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference', 'label', 'description', 'fournisseur_id',
        'demandeur_id', 'date_demande', 'date_livraison_souhaitee',
        'montant_total', 'priorite', 'statut',
        'fichiersjoin', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'date_demande' => 'date',
            'date_livraison_souhaitee' => 'date',
            'extra_attributes' => 'array',
        ];
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    public function demandeur()
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function produits()
    {
        return $this->hasMany(ApprovisionnementProduit::class, 'approvisionnement_id');
    }

    public function commande()
    {
        return $this->hasOne(CommandeFournisseur::class, 'approvisionnement_id');
    }

    public function scopeBrouillon($query) { return $query->where('statut', 0); }
    public function scopeValide($query) { return $query->where('statut', 2); }
}
