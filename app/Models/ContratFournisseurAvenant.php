<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratFournisseurAvenant extends Model
{
    protected $table = 'contrats_fournisseur_avenants';

    protected $fillable = [
        'contrat_id', 'numero', 'date_avenant', 'objet', 'impact',
        'delta_montant_ht', 'nouvelle_date_fin', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_avenant'      => 'date',
            'nouvelle_date_fin' => 'date',
            'delta_montant_ht'  => 'decimal:2',
        ];
    }

    public function contrat() { return $this->belongsTo(ContratFournisseur::class, 'contrat_id'); }
    public function auteur()  { return $this->belongsTo(User::class, 'created_by'); }
}
