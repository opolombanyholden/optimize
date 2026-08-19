<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionCompte extends Model
{
    protected $table = 'transaction_comptes';

    protected $fillable = [
        'compte_id', 'sens', 'montant', 'effacer', 'dateeffet',
    ];

    protected function casts(): array
    {
        return [
            'dateeffet' => 'datetime',
        ];
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id');
    }

    public function scopeDebit($query)
    {
        return $query->where('sens', 1);
    }

    public function scopeCredit($query)
    {
        return $query->where('sens', 0);
    }
}
