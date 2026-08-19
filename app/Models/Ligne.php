<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ligne extends Model
{
    protected $fillable = [
        'id_titre', 'nature', 'seuil', 'libelle', 'description',
        'attribut1', 'attribut2', 'attribut3', 'effacer',
        'id_codeanalytique', 'id_famillecodeanalytique',
        'id_user', 'dateeffet', 'id_typecode',
        // Finance V2 (alignement CdC initial)
        'code', 'label', 'status', 'type', 'titre_id', 'extra',
    ];

    protected $casts_v2 = [
        'extra' => 'array',
    ];

    protected function casts(): array
    {
        return [
            'dateeffet' => 'datetime',
        ];
    }

    public function titre()
    {
        return $this->belongsTo(Titre::class, 'id_titre');
    }

    public function budgetLignes()
    {
        return $this->hasMany(BudgetLigne::class, 'id_codeanalytique');
    }
}
