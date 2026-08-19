<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivraisonInterne extends Model
{
    protected $table = 'livraisons_internes';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['date_livraison' => 'date'];
    }

    public function commande() { return $this->belongsTo(CommandeInterne::class, 'commande_interne_id'); }
    public function livreur() { return $this->belongsTo(User::class, 'livre_par'); }
    public function receveur() { return $this->belongsTo(User::class, 'recu_par'); }
    public function lignes() { return $this->hasMany(LivraisonInterneLigne::class, 'livraison_id'); }
}
