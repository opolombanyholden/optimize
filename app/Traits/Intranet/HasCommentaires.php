<?php

namespace App\Traits\Intranet;

use App\Models\Intranet\Commentaire;
use App\Models\User;

/**
 * Trait HasCommentaires
 * À ajouter sur tout modèle intranet qui peut être commenté.
 */
trait HasCommentaires
{
    // ── Tous les commentaires (y compris réponses) ───────────
    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    // ── Commentaires racine uniquement (sans les réponses) ───
    public function commentairesRacine()
    {
        return $this->morphMany(Commentaire::class, 'commentable')
                    ->whereNull('parent_id')
                    ->with(['auteur', 'reponses.auteur'])
                    ->latest();
    }

    // ── Compte ───────────────────────────────────────────────
    public function totalCommentaires(): int
    {
        return $this->commentaires()->count();
    }

    // ── Ajouter un commentaire ───────────────────────────────
    public function commenter(string $contenu, ?int $parentId = null, ?User $user = null): Commentaire
    {
        $user = $user ?? auth()->user();
        return $this->commentaires()->create([
            'user_id'    => $user->id,
            'contenu'    => $contenu,
            'parent_id'  => $parentId,
        ]);
    }
}
