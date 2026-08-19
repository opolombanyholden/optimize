<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Titre extends Model
{
    protected $fillable = [
        'imputation', 'libelle', 'seuil', 'type_ligne',
        'description', 'effacer', 'id_user',
        // Finance V2 (alignement CdC initial)
        'code', 'label', 'extra',
    ];

    public function lignes()
    {
        // On utilise `id_titre` (legacy) comme clé principale car TOUS les seeders
        // le remplissent (le seeder V2 remplit `id_titre` + `titre_id` en double),
        // alors que le seeder référentiel n'utilise que `id_titre`.
        // Cette FK est celle qui garantit le lien fonctionnel avec les Lignes.
        return $this->hasMany(Ligne::class, 'id_titre');
    }

    public function lignesV2()
    {
        return $this->hasMany(Ligne::class, 'titre_id');
    }

    public function scopeDepense($query)
    {
        return $query->where('type_ligne', 'depense');
    }

    public function scopeRecette($query)
    {
        return $query->where('type_ligne', 'recette');
    }
}
