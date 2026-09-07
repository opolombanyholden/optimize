<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NatureIntervention extends Model
{
    use SoftDeletes;

    protected $table = 'natures_intervention';

    protected $fillable = ['code', 'libelle', 'description', 'couleur', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class, 'nature_id');
    }
}
