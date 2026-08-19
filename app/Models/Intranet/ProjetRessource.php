<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProjetRessource extends Model
{
    protected $table = 'intranet_projet_ressources';

    protected $fillable = [
        'projet_id', 'user_id', 'role_projet_id',
        'heures_allouees', 'heures_reelles', 'taux_journalier',
        'date_debut', 'date_fin', 'est_actif',
    ];

    protected $casts = [
        'date_debut'      => 'date',
        'date_fin'        => 'date',
        'est_actif'       => 'boolean',
        'heures_allouees' => 'decimal:2',
        'heures_reelles'  => 'decimal:2',
        'taux_journalier' => 'decimal:2',
    ];

    public function projet()      { return $this->belongsTo(Projet::class, 'projet_id'); }
    public function utilisateur() { return $this->belongsTo(User::class, 'user_id'); }
    public function roleProjet()  { return $this->belongsTo(RoleProjet::class, 'role_projet_id'); }

    public function getCoutEstimeAttribute(): float
    {
        if (! $this->taux_journalier || ! $this->heures_allouees) return 0;
        return round(($this->heures_allouees / 8) * $this->taux_journalier, 2);
    }

    public function getCoutReelAttribute(): float
    {
        if (! $this->taux_journalier || ! $this->heures_reelles) return 0;
        return round(($this->heures_reelles / 8) * $this->taux_journalier, 2);
    }
}
