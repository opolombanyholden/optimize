<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvenementCarriere extends Model
{
    use SoftDeletes;

    protected $table = 'evenementscarrieres';

    protected $fillable = [
        'libelle', 'description', 'typesevenementscarriere_id',
        'employee_id', 'date_effet', 'ancien_poste', 'nouveau_poste',
        'fichiersjoin', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'date_effet' => 'date',
            'extra_attributes' => 'array',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function type()
    {
        return $this->belongsTo(TypeEvenementCarriere::class, 'typesevenementscarriere_id');
    }
}
