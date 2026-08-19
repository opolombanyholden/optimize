<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;

class RoleProjet extends Model
{
    protected $table = 'intranet_roles_projet';

    protected $fillable = ['libelle', 'code', 'couleur', 'description', 'ordre', 'est_actif'];

    protected $casts = ['est_actif' => 'boolean'];

    public function ressources()
    {
        return $this->hasMany(ProjetRessource::class, 'role_projet_id');
    }

    public function scopeActif($query)
    {
        return $query->where('est_actif', true);
    }
}
