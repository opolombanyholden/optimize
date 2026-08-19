<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationFinanciereDetail extends Model
{
    protected $table = 'operation_financiere_details';

    protected $fillable = [
        'operation_financiere_id', 'rubrique_id',
        'libelle', 'quantite', 'prix_unitaire', 'montant',
        'ordre', 'observation',
    ];

    protected function casts(): array
    {
        return [
            'quantite'      => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
            'montant'       => 'decimal:2',
        ];
    }

    public function operation()
    {
        return $this->belongsTo(OperationFinanciere::class, 'operation_financiere_id');
    }

    public function rubrique()
    {
        return $this->belongsTo(RubriqueOperation::class, 'rubrique_id');
    }
}
