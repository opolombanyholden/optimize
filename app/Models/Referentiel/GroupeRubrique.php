<?php

namespace App\Models\Referentiel;

use App\Models\Rubrique;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupeRubrique extends Model
{
    use SoftDeletes;

    protected $table = 'groupes_rubriques';

    protected $fillable = ['code', 'libelle', 'description', 'ordre', 'statut', 'extra_attributes'];

    protected function casts(): array
    {
        return ['extra_attributes' => 'array'];
    }

    public function scopeActif($query) { return $query->where('statut', 1); }

    public function rubriques()
    {
        return $this->hasMany(Rubrique::class, 'groupe_rubrique_id');
    }
}
