<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayementGlobal extends Model
{
    protected $table = 'payements_globals';

    protected $fillable = [
        'annee', 'mois', 'exercice_id',
        'nombre_bulletins',
        'masse_salariale_brute', 'masse_salariale_nette',
        'total_cotisations_salariales', 'total_cotisations_patronales',
        'total_irpp', 'total_avances', 'total_retenues', 'total_paye',
        'agregats_par_departement', 'extra_attributes', 'statut',
        'cloturee_par', 'cloturee_at',
    ];

    protected function casts(): array
    {
        return [
            'masse_salariale_brute'           => 'decimal:2',
            'masse_salariale_nette'           => 'decimal:2',
            'total_cotisations_salariales'    => 'decimal:2',
            'total_cotisations_patronales'    => 'decimal:2',
            'total_irpp'                      => 'decimal:2',
            'total_avances'                   => 'decimal:2',
            'total_retenues'                  => 'decimal:2',
            'total_paye'                      => 'decimal:2',
            'agregats_par_departement'        => 'array',
            'extra_attributes'                => 'array',
            'cloturee_at'                     => 'datetime',
        ];
    }

    public function exercice() { return $this->belongsTo(\App\Models\Exercice::class); }
    public function cloturePar() { return $this->belongsTo(User::class, 'cloturee_par'); }

    public function getPeriodeLibelleAttribute(): string
    {
        return \Carbon\Carbon::create($this->annee, $this->mois, 1)->translatedFormat('F Y');
    }
}
