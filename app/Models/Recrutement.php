<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recrutement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'label', 'introduction', 'description',
        'debut', 'fin', 'statut', 'fichiersjoin', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'debut' => 'datetime',
            'fin' => 'datetime',
            'extra_attributes' => 'array',
        ];
    }

    public function profils()
    {
        return $this->hasMany(Profil::class, 'recrutement_id');
    }

    public function postulants()
    {
        return $this->hasMany(Postulant::class, 'recrutement_id');
    }

    public function scopeOuvert($query) { return $query->where('statut', 0); }
    public function scopeFerme($query) { return $query->where('statut', 1); }
}
