<?php

namespace App\Policies;

use App\Models\Fournisseur;
use App\Models\User;

class FournisseurPolicy
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
        return $user->hasPermissionTo('read:fournisseur');
    }

    public function view(User $user, Fournisseur $fournisseur): bool
    {
        return $user->hasPermissionTo('read:fournisseur');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create:fournisseur');
    }

    public function update(User $user, Fournisseur $fournisseur): bool
    {
        return $user->hasPermissionTo('update:fournisseur');
    }

    public function delete(User $user, Fournisseur $fournisseur): bool
    {
        return $user->hasPermissionTo('delete:fournisseur');
    }

    public function restore(User $user, Fournisseur $fournisseur): bool
    {
        return $user->hasPermissionTo('delete:fournisseur');
    }

    public function forceDelete(User $user, Fournisseur $fournisseur): bool
    {
        return $user->hasPermissionTo('delete:fournisseur');
    }

    public function validate(User $user, Fournisseur $fournisseur): bool
    {
        return $user->hasPermissionTo('validate:fournisseur');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export:fournisseur');
    }
}
