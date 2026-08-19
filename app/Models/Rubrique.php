<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rubrique extends Model
{
    protected $fillable = [
        'groupe_rubrique_id',
        'code', 'libelle', 'libelle_court', 'type',
        'base_calcul', 'base_calcul_libelle',
        'taux', 'valeur2', 'montant_fixe', 'formule',
        'imposable', 'cotisable', 'ordre_affichage',
        'date_effet', 'date_fin_effet',
        'statut', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'imposable' => 'boolean',
            'cotisable' => 'boolean',
            'date_effet' => 'date',
            'date_fin_effet' => 'date',
            'extra_attributes' => 'array',
        ];
    }

    public function groupe()
    {
        return $this->belongsTo(\App\Models\Referentiel\GroupeRubrique::class, 'groupe_rubrique_id');
    }

    public function scopeGain($query) { return $query->where('type', 'gain'); }
    public function scopeRetenue($query) { return $query->where('type', 'retenue'); }
    public function scopeActif($query) { return $query->where('statut', 1); }
}
