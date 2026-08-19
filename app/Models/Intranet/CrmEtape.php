<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;

class CrmEtape extends Model
{
    protected $table = 'intranet_crm_etapes';

    protected $fillable = [
        'nom', 'ordre', 'couleur',
        'probabilite_defaut', 'est_gagnee', 'est_perdue', 'est_finale',
    ];

    protected $casts = [
        'est_gagnee'         => 'boolean',
        'est_perdue'         => 'boolean',
        'est_finale'         => 'boolean',
        'probabilite_defaut' => 'integer',
        'ordre'              => 'integer',
    ];

    public function opportunites()
    {
        return $this->hasMany(Opportunite::class, 'etape_id')->orderBy('ordre_kanban');
    }
}
