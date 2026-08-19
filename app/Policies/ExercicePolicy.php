<?php

namespace App\Policies;

use App\Models\Exercice;
use App\Models\User;

class ExercicePolicy
{
    /**
     * Le super-admin a un pouvoir total : il contourne à la fois les permissions
     * RBAC ET les règles métier de verrouillage (update/delete/annuler). C'est
     * une décision explicite pour lui donner la capacité d'intervenir sur un
     * exercice bloqué (audit, correction post-mortem, purge de test).
     * Les vues affichent des avertissements pour rappeler la responsabilité.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('read:exercice');
    }

    public function view(User $user, Exercice $exercice): bool
    {
        return $user->hasPermissionTo('read:exercice');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create:exercice');
    }

    /**
     * Pour les utilisateurs non-super-admin : la permission RBAC + la règle métier
     * de verrouillage doivent être satisfaites. Le super-admin passe par `before()`.
     */
    public function update(User $user, Exercice $exercice): bool
    {
        return $user->hasPermissionTo('update:exercice') && $exercice->peutEtreModifie();
    }

    public function delete(User $user, Exercice $exercice): bool
    {
        return $user->hasPermissionTo('delete:exercice') && $exercice->peutEtreSupprime();
    }

    public function annuler(User $user, Exercice $exercice): bool
    {
        return $user->hasPermissionTo('validate:exercice') && $exercice->peutEtreAnnule();
    }

    public function restore(User $user, Exercice $exercice): bool
    {
        return $user->hasPermissionTo('delete:exercice');
    }

    public function forceDelete(User $user, Exercice $exercice): bool
    {
        return $user->hasPermissionTo('delete:exercice') && $exercice->peutEtreSupprime();
    }

    public function validate(User $user, Exercice $exercice): bool
    {
        return $user->hasPermissionTo('validate:exercice');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export:exercice');
    }
}
