<?php

namespace App\Models\Finance;

use App\Models\RubriqueOperation;
use Illuminate\Database\Eloquent\Model;

class OrdreDetail extends Model
{
    protected $table = 'finance_ordre_details';

    protected $fillable = [
        'ordre_id', 'rubrique_id', 'libelle',
        'quantite', 'prix_unitaire', 'montant',
        'observation', 'ordre',
    ];

    protected $casts = [
        'quantite'      => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'montant'       => 'decimal:2',
        'ordre'         => 'integer',
    ];

    public function ordre()
    {
        return $this->belongsTo(Ordre::class, 'ordre_id');
    }

    public function rubrique()
    {
        return $this->belongsTo(RubriqueOperation::class, 'rubrique_id');
    }

    /**
     * Calcule et persiste `montant = quantite × prix_unitaire`.
     */
    public function recalculerMontant(): void
    {
        $this->montant = round((float) $this->quantite * (float) $this->prix_unitaire, 2);
    }

    protected static function booted(): void
    {
        // Auto-calcul du montant à chaque save si quantité ou prix unitaire renseignés
        static::saving(function (self $d) {
            if ($d->quantite !== null && $d->prix_unitaire !== null) {
                $d->montant = round((float) $d->quantite * (float) $d->prix_unitaire, 2);
            }
        });
    }
}
