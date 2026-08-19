<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImmobilisationDotation extends Model
{
    protected $fillable = [
        'immobilisation_id', 'periode_debut', 'periode_fin',
        'montant', 'vnc_avant', 'vnc_apres', 'cumul_apres',
        'methode', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'periode_debut' => 'date',
            'periode_fin' => 'date',
            'montant' => 'decimal:2',
            'vnc_avant' => 'decimal:2',
            'vnc_apres' => 'decimal:2',
            'cumul_apres' => 'decimal:2',
        ];
    }

    public function immobilisation() { return $this->belongsTo(Immobilisation::class); }
    public function auteur() { return $this->belongsTo(User::class, 'created_by'); }
}
