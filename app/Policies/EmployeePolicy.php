<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
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
        return $user->hasPermissionTo('read:employee');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('read:employee');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create:employee');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('update:employee');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('delete:employee');
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('delete:employee');
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('delete:employee');
    }

    public function validate(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('validate:employee');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export:employee');
    }
}
