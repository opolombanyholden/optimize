<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OrdreModeleSignataire extends Model
{
    protected $table = 'finance_ordre_modele_signataires';

    protected $fillable = ['modele_id', 'role_libelle', 'user_id_par_defaut', 'ordre'];

    protected $casts = ['ordre' => 'integer'];

    public function modele()
    {
        return $this->belongsTo(OrdreModele::class, 'modele_id');
    }

    public function userParDefaut()
    {
        return $this->belongsTo(User::class, 'user_id_par_defaut');
    }
}
