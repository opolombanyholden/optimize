<?php

namespace App\Traits\Intranet;

use App\Models\Intranet\Vue;
use App\Models\User;

/**
 * Trait HasVues
 * Trace les consultations uniques par utilisateur.
 */
trait HasVues
{
    public function vues()
    {
        return $this->morphMany(Vue::class, 'viewable');
    }

    public function totalVues(): int
    {
        return $this->vues()->count();
    }

    /**
     * Enregistre une vue (unique par user).
     */
    public function enregistrerVue(?User $user = null): void
    {
        $user = $user ?? auth()->user();
        if (!$user) return;

        Vue::firstOrCreate([
            'viewable_type' => static::class,
            'viewable_id'   => $this->id,
            'user_id'       => $user->id,
        ]);
    }

    public function estVuPar(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) return false;
        return $this->vues()->where('user_id', $user->id)->exists();
    }
}
