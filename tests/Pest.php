<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(Tests\TestCase::class)
    ->use(DatabaseTransactions::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Seed les rôles/permissions si absents.
 * RefreshDatabase recrée la DB entre tests, donc on vérifie à chaque appel.
 * Important : invalider le cache de Spatie Permission après le seed.
 */
function seedRoles(): void
{
    if (! \Spatie\Permission\Models\Role::where('name', 'super-admin')->exists()) {
        app(\Database\Seeders\RolePermissionSeeder::class)->run();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

/**
 * Crée un super-admin et le retourne authentifié.
 */
function actingAsSuperAdmin(): \App\Models\User
{
    seedRoles();
    $user = \App\Models\User::factory()->create([
        'email'  => 'admin-test@optimize.local',
        'statut' => 1,
    ]);
    $user->assignRole('super-admin');
    test()->actingAs($user);
    return $user;
}

/**
 * Crée un user simple authentifié.
 */
function actingAsUser(?array $attrs = []): \App\Models\User
{
    seedRoles();
    $user = \App\Models\User::factory()->create(array_merge(['statut' => 1], $attrs));
    $user->assignRole('user');
    test()->actingAs($user);
    return $user;
}
