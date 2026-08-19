<?php

use App\Models\Intranet\Groupe;
use App\Models\Intranet\Service;
use App\Models\User;

beforeEach(function () {
    seedRoles();
});

it('renders the collaborateurs index', function () {
    actingAsSuperAdmin();
    $response = $this->get('/intranet/annuaire/collaborateurs');
    $response->assertOk();
});

it('shows a collaborateur detail page', function () {
    $admin = actingAsSuperAdmin();
    $colleague = User::factory()->create([
        'name' => 'Doe', 'prenoms' => 'John', 'statut' => 1,
    ]);

    $response = $this->get("/intranet/annuaire/collaborateurs/{$colleague->id}");
    $response->assertOk();
    $response->assertSee('John', false);
    $response->assertSee('Doe', false);
});

it('renders the services index', function () {
    actingAsSuperAdmin();
    $response = $this->get('/intranet/annuaire/services');
    $response->assertOk();
});

it('creates a service via POST', function () {
    actingAsSuperAdmin();

    $response = $this->post('/intranet/annuaire/services', [
        'nom'         => 'Service Test',
        'code'        => 'TST',
        'description' => 'Description test',
        'couleur'     => '#0D9488',
    ]);

    $response->assertRedirect();
    $service = Service::where('code', 'TST')->first();
    expect($service)->not->toBeNull();
    expect($service->nom)->toBe('Service Test');
});

it('attaches a member to a service', function () {
    $admin = actingAsSuperAdmin();
    $service = Service::create(['nom' => 'S', 'code' => 'S1', 'est_actif' => true]);
    $member  = User::factory()->create();

    $response = $this->post("/intranet/annuaire/services/{$service->id}/membres", [
        'user_id' => $member->id,
        'poste'   => 'Développeur',
    ]);

    $response->assertRedirect();
    $service->refresh();
    expect($service->membres->count())->toBe(1);
    expect($service->membres->first()->id)->toBe($member->id);
});

it('detaches a member from a service', function () {
    actingAsSuperAdmin();
    $service = Service::create(['nom' => 'S', 'code' => 'S2', 'est_actif' => true]);
    $member  = User::factory()->create();
    $service->membres()->attach($member->id, ['poste' => 'Dev']);

    $response = $this->delete("/intranet/annuaire/services/{$service->id}/membres/{$member->id}");
    $response->assertRedirect();

    expect($service->fresh()->membres->count())->toBe(0);
});

it('creates an equipe with members', function () {
    $admin = actingAsSuperAdmin();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();

    $response = $this->post('/intranet/annuaire/equipes', [
        'nom'     => 'Taskforce',
        'membres' => [$member1->id, $member2->id],
    ]);

    $response->assertRedirect();
    $equipe = Groupe::where('nom', 'Taskforce')->first();
    expect($equipe)->not->toBeNull();
    expect($equipe->membres->count())->toBe(2);
});

it('cannot delete a service with members', function () {
    actingAsSuperAdmin();
    $service = Service::create(['nom' => 'S', 'code' => 'S3', 'est_actif' => true]);
    $service->membres()->attach(User::factory()->create()->id, ['poste' => 'Dev']);

    $response = $this->delete("/intranet/annuaire/services/{$service->id}");
    $response->assertRedirect();
    $response->assertSessionHasErrors();

    expect($service->fresh())->not->toBeNull();
});
