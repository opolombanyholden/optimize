<?php

namespace App\Models\Finance;

use App\Models\Ligne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ligne de détail d'une transaction (rubrique / prestation / article).
 * Le montant total de la transaction = somme des details.
 */
class TransactionDetail extends Model
{
    use SoftDeletes;

    protected $table = 'transaction_details';

    protected $fillable = [
        'montant', 'montant_lettres', 'label', 'quantity',
        'transaction_id', 'ligne_id', 'extra',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'extra'   => 'array',
    ];

    public function transaction() { return $this->belongsTo(Transaction::class, 'transaction_id'); }
    public function ligne()       { return $this->belongsTo(Ligne::class); }
}
