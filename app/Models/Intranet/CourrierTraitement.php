<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CourrierTraitement extends Model
{
    protected $table = 'intranet_courrier_traitements';

    protected $fillable = [
        'courrier_id', 'user_id', 'action', 'commentaire', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function courrier()
    {
        return $this->belongsTo(Courrier::class, 'courrier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getIconeAttribute(): string
    {
        return match ($this->action) {
            'cree'             => 'fa-circle-plus',
            'assigne'          => 'fa-user-plus',
            'reassigne'        => 'fa-arrows-rotate',
            'commente'         => 'fa-comment',
            'accuse_reception' => 'fa-check-double',
            'traite'           => 'fa-check-circle',
            'archive'          => 'fa-box-archive',
            'rouvert'          => 'fa-rotate-left',
            default            => 'fa-circle',
        };
    }

    public function getCouleurAttribute(): string
    {
        return match ($this->action) {
            'cree'             => '#7C3AED',
            'assigne'          => '#0891B2',
            'reassigne'        => '#F59E0B',
            'commente'         => '#64748B',
            'accuse_reception' => '#16A34A',
            'traite'           => '#16A34A',
            'archive'          => '#94A3B8',
            'rouvert'          => '#D97706',
            default            => '#94A3B8',
        };
    }

    public function getLibelleAttribute(): string
    {
        return match ($this->action) {
            'cree'             => 'Courrier créé',
            'assigne'          => 'Courrier assigné',
            'reassigne'        => 'Courrier réassigné',
            'commente'         => 'Notation ajoutée',
            'accuse_reception' => 'Accusé de réception enregistré',
            'traite'           => 'Courrier traité',
            'archive'          => 'Courrier archivé',
            'rouvert'          => 'Courrier rouvert',
            default            => ucfirst($this->action),
        };
    }
}
