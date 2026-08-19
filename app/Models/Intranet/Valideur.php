<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Valideur extends Model
{
    protected $table = 'intranet_valideurs';

    protected $fillable = ['validable_type', 'validable_id', 'valideur_type', 'valideur_id'];

    public function validable()
    {
        return $this->morphTo();
    }

    public function getValideurAttribute()
    {
        return match ($this->valideur_type) {
            'user'   => User::find($this->valideur_id),
            'groupe' => Groupe::find($this->valideur_id),
            default  => null,
        };
    }

    public function getNomAffichageAttribute(): string
    {
        if ($this->valideur_type === 'user') {
            $u = User::find($this->valideur_id);
            return $u ? trim(($u->prenoms ?? '') . ' ' . $u->name) : 'Utilisateur inconnu';
        }
        $g = Groupe::find($this->valideur_id);
        return $g ? $g->nom : 'Groupe inconnu';
    }
}
