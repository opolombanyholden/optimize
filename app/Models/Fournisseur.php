<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fournisseur extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'raison_sociale', 'sigle', 'nif', 'rccm',
        'adresse', 'ville', 'pays', 'telephone', 'email', 'site_web',
        'contact_nom', 'contact_telephone', 'contact_email',
        'rib', 'banque', 'domiciliation', 'categorie',
        'note_evaluation', 'commentaire', 'fichiersjoin',
        'statut', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return ['extra_attributes' => 'array'];
    }

    public function commandes()
    {
        return $this->hasMany(CommandeFournisseur::class, 'fournisseur_id');
    }

    public function approvisionnements()
    {
        return $this->hasMany(Approvisionnement::class, 'fournisseur_id');
    }

    public function scopeActif($query) { return $query->where('statut', 1); }
}
