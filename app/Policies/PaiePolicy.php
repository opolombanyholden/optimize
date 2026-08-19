<?php

namespace App\Policies;

use App\Models\Paie;
use App\Models\User;

class PaiePolicy
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
        return $user->hasPermissionTo('read:paie');
    }

    public function view(User $user, Paie $paie): bool
    {
        return $user->hasPermissionTo('read:paie');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create:paie');
    }

    public function update(User $user, Paie $paie): bool
    {
        return $user->hasPermissionTo('update:paie');
    }

    public function delete(User $user, Paie $paie): bool
    {
        return $user->hasPermissionTo('delete:paie');
    }

    public function restore(User $user, Paie $paie): bool
    {
        return $user->hasPermissionTo('delete:paie');
    }

    public function forceDelete(User $user, Paie $paie): bool
    {
        return $user->hasPermissionTo('delete:paie');
    }

    public function validate(User $user, Paie $paie): bool
    {
        return $user->hasPermissionTo('validate:paie');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export:paie');
    }
}
