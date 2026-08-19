<?php

namespace App\Traits\Intranet;

use App\Models\Intranet\Like;
use App\Models\User;

/**
 * Trait HasLikes
 * À ajouter sur tout modèle intranet qui peut être liké.
 */
trait HasLikes
{
    // ── Relation polymorphique ───────────────────────────────
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    // ── Compte total ─────────────────────────────────────────
    public function totalLikes(): int
    {
        return $this->likes()->count();
    }

    // ── L'utilisateur courant a-t-il liké ? ─────────────────
    public function estLikeParUser(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    // ── Toggle like (retourne true si like ajouté, false si retiré) ──
    public function toggleLike(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) return false;

        $existing = $this->likes()->where('user_id', $user->id)->first();
        if ($existing) {
            $existing->delete();
            return false;
        }

        $this->likes()->create(['user_id' => $user->id]);
        return true;
    }
}
