<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Postulant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'noms', 'prenoms', 'age', 'date_naissance',
        'email', 'contact', 'profil_id', 'recrutement_id',
        'statut', 'fichiersjoin', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return ['extra_attributes' => 'array'];
    }

    public function profil()
    {
        return $this->belongsTo(Profil::class, 'profil_id');
    }

    public function recrutement()
    {
        return $this->belongsTo(Recrutement::class, 'recrutement_id');
    }

    public function embauche()
    {
        return $this->hasOne(Embauche::class, 'postulant_id');
    }
}
