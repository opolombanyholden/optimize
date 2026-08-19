<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class HistoriqueValidation extends Model
{
    protected $table = 'intranet_historique_validations';

    protected $fillable = [
        'validable_type', 'validable_id',
        'user_id', 'action', 'justification', 'commentaire', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function validable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getIconeAttribute(): string
    {
        return match ($this->action) {
            'cree'                => 'fa-circle-plus',
            'modifie'             => 'fa-pen-to-square',
            'soumis', 'resoumis' => 'fa-paper-plane',
            'approuve'           => 'fa-check-circle',
            'rejete'             => 'fa-circle-xmark',
            'revisions_demandees'=> 'fa-arrows-rotate',
            'cloture'            => 'fa-flag-checkered',
            'reouvert'           => 'fa-lock-open',
            default              => 'fa-circle',
        };
    }

    public function getCouleurAttribute(): string
    {
        return match ($this->action) {
            'cree'                => '#7C3AED',
            'soumis', 'resoumis' => '#0891B2',
            'approuve'           => '#16A34A',
            'rejete'             => '#DC2626',
            'revisions_demandees'=> '#F59E0B',
            'cloture'            => '#059669',
            'reouvert'           => '#6366F1',
            default              => '#64748B',
        };
    }

    public function getLibelleAttribute(): string
    {
        return match ($this->action) {
            'cree'                => 'Créé',
            'modifie'             => 'Modifié',
            'soumis'              => 'Soumis pour validation',
            'resoumis'            => 'Resoumis après corrections',
            'approuve'            => 'Clôture approuvée',
            'rejete'              => 'Clôture rejetée',
            'revisions_demandees' => 'Révisions demandées',
            'cloture'             => 'Clôturé',
            'reouvert'            => 'Réouvert',
            default               => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
