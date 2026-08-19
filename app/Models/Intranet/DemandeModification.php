<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DemandeModification extends Model
{
    protected $table = 'intranet_demandes_modification';

    protected $fillable = [
        'modifiable_type', 'modifiable_id',
        'type_demande', 'statut', 'motif',
        'champs_modifies', 'demandeur_id',
        'decideur_id', 'decide_le', 'commentaire_decision',
    ];

    protected $casts = [
        'champs_modifies' => 'array',
        'decide_le'       => 'datetime',
    ];

    public function modifiable()  { return $this->morphTo(); }
    public function demandeur()   { return $this->belongsTo(User::class, 'demandeur_id'); }
    public function decideur()    { return $this->belongsTo(User::class, 'decideur_id'); }

    public function getTypeLibelleAttribute(): string
    {
        return $this->type_demande === 'modification' ? 'Modification' : 'Suppression';
    }

    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => '#F59E0B',
            'approuve'   => '#16A34A',
            'rejete'     => '#DC2626',
            default      => '#94A3B8',
        };
    }

    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'En attente',
            'approuve'   => 'Approuvé',
            'rejete'     => 'Rejeté',
            default      => $this->statut,
        };
    }

    public function getEntiteNomAttribute(): string
    {
        $entity = $this->modifiable;
        if (! $entity) return 'Élément supprimé';
        return $entity->nom ?? $entity->titre ?? $entity->libelle ?? 'ID ' . $entity->id;
    }

    public function getEntiteTypeLibelleAttribute(): string
    {
        return match ($this->modifiable_type) {
            'App\\Models\\Intranet\\Projet'      => 'Projet',
            'App\\Models\\Intranet\\ProjetPhase'  => 'Phase WBS',
            'App\\Models\\Intranet\\Jalon'        => 'Jalon',
            'App\\Models\\Intranet\\Tache'        => 'Tâche',
            default => class_basename($this->modifiable_type),
        };
    }
}
