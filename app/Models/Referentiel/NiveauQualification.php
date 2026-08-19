<?php

namespace App\Models\Referentiel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NiveauQualification extends Model
{
    use SoftDeletes;

    protected $table = 'niveaux_qualification';

    protected $fillable = ['code', 'libelle', 'description', 'ordre', 'statut', 'extra_attributes'];

    protected function casts(): array
    {
        return ['extra_attributes' => 'array'];
    }

    public function scopeActif($query) { return $query->where('statut', 1); }
}
