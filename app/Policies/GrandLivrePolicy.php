<?php

namespace App\Policies;

use App\Models\GrandLivre;
use App\Models\User;

class GrandLivrePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('read:grandlivre');
    }

    public function view(User $user, GrandLivre $grandLivre): bool
    {
        return $user->hasPermissionTo('read:grandlivre');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create:grandlivre');
    }

    public function update(User $user, GrandLivre $grandLivre): bool
    {
        return $user->hasPermissionTo('update:grandlivre');
    }

    public function delete(User $user, GrandLivre $grandLivre): bool
    {
        return $user->hasPermissionTo('delete:grandlivre');
    }

    public function restore(User $user, GrandLivre $grandLivre): bool
    {
        return $user->hasPermissionTo('delete:grandlivre');
    }

    public function forceDelete(User $user, GrandLivre $grandLivre): bool
    {
        return $user->hasPermissionTo('delete:grandlivre');
    }

    public function validate(User $user, GrandLivre $grandLivre): bool
    {
        return $user->hasPermissionTo('validate:grandlivre');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export:grandlivre');
    }
}
