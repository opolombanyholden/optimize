<?php

namespace App\Policies;

use App\Models\CommandeFournisseur;
use App\Models\User;

class CommandePolicy
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
        return $user->hasPermissionTo('read:commande');
    }

    public function view(User $user, CommandeFournisseur $commandeFournisseur): bool
    {
        return $user->hasPermissionTo('read:commande');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create:commande');
    }

    public function update(User $user, CommandeFournisseur $commandeFournisseur): bool
    {
        return $user->hasPermissionTo('update:commande');
    }

    public function delete(User $user, CommandeFournisseur $commandeFournisseur): bool
    {
        return $user->hasPermissionTo('delete:commande');
    }

    public function restore(User $user, CommandeFournisseur $commandeFournisseur): bool
    {
        return $user->hasPermissionTo('delete:commande');
    }

    public function forceDelete(User $user, CommandeFournisseur $commandeFournisseur): bool
    {
        return $user->hasPermissionTo('delete:commande');
    }

    public function validate(User $user, CommandeFournisseur $commandeFournisseur): bool
    {
        return $user->hasPermissionTo('validate:commande');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export:commande');
    }
}
