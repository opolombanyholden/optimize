<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TacheHistorique extends Model
{
    protected $table = 'intranet_tache_historique';

    protected $fillable = [
        'tache_id', 'user_id', 'action', 'commentaire', 'note', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'note' => 'integer',
    ];

    public function tache() { return $this->belongsTo(Tache::class, 'tache_id'); }
    public function user()  { return $this->belongsTo(User::class, 'user_id'); }

    public function getIconeAttribute(): string
    {
        return match ($this->action) {
            'cree'                 => 'fa-circle-plus',
            'modifie'              => 'fa-pen-to-square',
            'soumis', 'resoumis'   => 'fa-paper-plane',
            'approuve'             => 'fa-check-circle',
            'rejete'               => 'fa-circle-xmark',
            'revisions_demandees'  => 'fa-arrows-rotate',
            'evalue'               => 'fa-star',
            'cloture'              => 'fa-flag-checkered',
            'piece_jointe_ajoutee' => 'fa-paperclip',
            'commentaire'          => 'fa-comment',
            default                => 'fa-circle',
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
            'evalue'             => '#D97706',
            'cloture'            => '#059669',
            default              => '#64748B',
        };
    }

    public function getLibelleAttribute(): string
    {
        return match ($this->action) {
            'cree'                 => 'Tâche créée',
            'modifie'              => 'Tâche modifiée',
            'soumis'               => 'Soumis pour validation',
            'resoumis'             => 'Resoumis après corrections',
            'approuve'             => 'Tâche approuvée',
            'rejete'               => 'Tâche rejetée',
            'revisions_demandees'  => 'Révisions demandées',
            'evalue'               => 'Tâche évaluée',
            'cloture'              => 'Tâche clôturée',
            'piece_jointe_ajoutee' => 'Pièce justificative ajoutée',
            'commentaire'          => 'Commentaire ajouté',
            default                => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
