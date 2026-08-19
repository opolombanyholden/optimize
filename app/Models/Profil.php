<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profil extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'label', 'introduction', 'description', 'nombres',
        'recrutement_id', 'fichiersjoin', 'statut', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return ['extra_attributes' => 'array'];
    }

    public function recrutement()
    {
        return $this->belongsTo(Recrutement::class, 'recrutement_id');
    }

    public function postulants()
    {
        return $this->hasMany(Postulant::class, 'profil_id');
    }
}
