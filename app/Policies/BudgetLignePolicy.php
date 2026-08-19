<?php

namespace App\Policies;

use App\Models\BudgetLigne;
use App\Models\User;

class BudgetLignePolicy
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
        return $user->hasPermissionTo('read:budget');
    }

    public function view(User $user, BudgetLigne $budgetLigne): bool
    {
        return $user->hasPermissionTo('read:budget');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create:budget');
    }

    public function update(User $user, BudgetLigne $budgetLigne): bool
    {
        return $user->hasPermissionTo('update:budget');
    }

    public function delete(User $user, BudgetLigne $budgetLigne): bool
    {
        return $user->hasPermissionTo('delete:budget');
    }

    public function restore(User $user, BudgetLigne $budgetLigne): bool
    {
        return $user->hasPermissionTo('delete:budget');
    }

    public function forceDelete(User $user, BudgetLigne $budgetLigne): bool
    {
        return $user->hasPermissionTo('delete:budget');
    }

    public function validate(User $user, BudgetLigne $budgetLigne): bool
    {
        return $user->hasPermissionTo('validate:budget');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export:budget');
    }
}
