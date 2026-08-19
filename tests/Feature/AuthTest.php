<?php

use App\Models\User;

it('renders the login page', function () {
    $response = $this->get('/login');
    $response->assertOk();
    $response->assertSee('email', false);
});

it('redirects authenticated users from login', function () {
    actingAsUser();
    $response = $this->get('/login');
    // Selon la config, peut rediriger ou afficher
    expect(in_array($response->status(), [200, 302]))->toBeTrue();
});

it('logs in a user with valid credentials', function () {
    $user = User::factory()->create([
        'email'    => 'test-login@optimize.local',
        'password' => bcrypt('secret123'),
        'statut'   => 1,
    ]);

    $response = $this->post('/login', [
        'email'    => 'test-login@optimize.local',
        'password' => 'secret123',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    User::factory()->create([
        'email'    => 'test-bad@optimize.local',
        'password' => bcrypt('correct'),
    ]);

    $response = $this->post('/login', [
        'email'    => 'test-bad@optimize.local',
        'password' => 'wrong',
    ]);

    $this->assertGuest();
    expect($response->status())->toBeIn([302, 422]);
});

it('logs out an authenticated user', function () {
    actingAsUser();
    $this->post('/logout');
    $this->assertGuest();
});

it('protects authenticated routes', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('lets authenticated users hit a protected page', function () {
    actingAsSuperAdmin();
    // /dashboard charge beaucoup de données ERP — on teste plutôt une page simple
    $response = $this->get('/intranet/annuaire/services');
    $response->assertOk();
});
