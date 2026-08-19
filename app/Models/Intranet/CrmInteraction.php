<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmInteraction extends Model
{
    use SoftDeletes;

    protected $table = 'intranet_crm_interactions';

    protected $fillable = [
        'interactable_type', 'interactable_id',
        'type', 'objet', 'description',
        'date_interaction', 'duree_minutes',
        'est_terminee', 'realisee_par',
    ];

    protected $casts = [
        'date_interaction' => 'datetime',
        'est_terminee'     => 'boolean',
        'duree_minutes'    => 'integer',
    ];

    public function interactable()
    {
        return $this->morphTo();
    }

    public function realisateur()
    {
        return $this->belongsTo(User::class, 'realisee_par');
    }

    public function getIconeAttribute(): string
    {
        return match ($this->type) {
            'appel'   => 'fa-phone',
            'reunion' => 'fa-handshake',
            'email'   => 'fa-envelope',
            'tache'   => 'fa-list-check',
            'autre'   => 'fa-circle',
            default   => 'fa-note-sticky',
        };
    }

    public function getCouleurAttribute(): string
    {
        return match ($this->type) {
            'appel'   => '#0891B2',
            'reunion' => '#7C3AED',
            'email'   => '#4F46E5',
            'tache'   => '#D97706',
            'note'    => '#64748B',
            default   => '#94A3B8',
        };
    }
}
