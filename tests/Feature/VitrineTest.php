<?php

use App\Models\Vitrine\Atout;
use App\Models\Vitrine\Capture;
use App\Models\Vitrine\Module;
use App\Models\Vitrine\Setting;
use App\Models\Vitrine\SlideHero;

beforeEach(function () {
    seedRoles();
});

it('renders the public vitrine homepage', function () {
    SlideHero::create([
        'titre' => 'Bienvenue sur OptimiZe', 'sous_titre' => 'Test', 'mockup_type' => 'dashboard',
        'cta_texte' => 'Demander une démo', 'cta_url' => '#contact', 'est_actif' => true,
    ]);

    $response = $this->get('/');
    $response->assertOk();
    $response->assertSee('OptimiZe', false);
    $response->assertSee('Bienvenue sur OptimiZe', false);
});

it('does not require authentication for the homepage', function () {
    $this->assertGuest();
    $response = $this->get('/');
    $response->assertOk();
});

it('shows hero slider with active slides only', function () {
    SlideHero::create(['titre' => 'Slide actif',   'est_actif' => true]);
    SlideHero::create(['titre' => 'Slide inactif', 'est_actif' => false]);

    $response = $this->get('/');
    $response->assertSee('Slide actif', false);
    $response->assertDontSee('Slide inactif', false);
});

it('renders modules from database', function () {
    Module::create(['nom' => 'Mon Module Test', 'description' => 'Une description', 'icone' => 'fa-star', 'couleur' => '#FF0000', 'est_actif' => true]);
    $response = $this->get('/');
    $response->assertSee('Mon Module Test', false);
});

it('uses settings from database', function () {
    Setting::create(['cle' => 'marque_nom', 'valeur' => 'MaSuperMarque', 'libelle' => 'Marque', 'groupe' => 'general', 'type' => 'text']);
    Setting::create(['cle' => 'contact_email', 'valeur' => 'demo@test.com', 'libelle' => 'Email', 'groupe' => 'contact', 'type' => 'text']);
    \Illuminate\Support\Facades\Cache::flush();

    $response = $this->get('/');
    $response->assertSee('MaSuperMarque', false);
    $response->assertSee('demo@test.com', false);
});

/* ─── ADMIN ─── */

it('protects the admin vitrine page from guests', function () {
    $this->get('/admin/vitrine')->assertRedirect('/login');
});

it('renders the admin vitrine page for super-admin', function () {
    actingAsSuperAdmin();
    $this->get('/admin/vitrine')->assertOk();
});

it('admin can create a new slide', function () {
    actingAsSuperAdmin();

    $response = $this->post('/admin/vitrine/slides', [
        'titre'       => 'Nouveau slide test',
        'eyebrow'     => 'Test',
        'sous_titre'  => 'Sous-titre',
        'cta_texte'   => 'Cliquez',
        'cta_url'     => '#contact',
        'mockup_type' => 'dashboard',
        'est_actif'   => 1,
    ]);

    $response->assertRedirect();
    expect(SlideHero::where('titre', 'Nouveau slide test')->count())->toBe(1);
});

it('admin can create a module with features', function () {
    actingAsSuperAdmin();

    $this->post('/admin/vitrine/modules', [
        'nom'           => 'Module ABC',
        'description'   => 'Test',
        'icone'         => 'fa-star',
        'couleur'       => '#FF0000',
        'features_raw'  => "Feature 1\nFeature 2\nFeature 3",
        'est_actif'     => 1,
    ]);

    $module = Module::where('nom', 'Module ABC')->first();
    expect($module)->not->toBeNull();
    expect($module->features)->toBe(['Feature 1', 'Feature 2', 'Feature 3']);
});

it('admin can update settings in bulk', function () {
    actingAsSuperAdmin();
    Setting::create(['cle' => 'marque_nom',    'valeur' => 'Old',     'libelle' => 'Marque', 'groupe' => 'general', 'type' => 'text']);
    Setting::create(['cle' => 'contact_email', 'valeur' => 'old@e.c', 'libelle' => 'Email',  'groupe' => 'contact', 'type' => 'text']);

    $this->put('/admin/vitrine/settings', [
        'settings' => ['marque_nom' => 'NewBrand', 'contact_email' => 'new@e.com'],
    ]);

    expect(Setting::where('cle', 'marque_nom')->value('valeur'))->toBe('NewBrand');
    expect(Setting::where('cle', 'contact_email')->value('valeur'))->toBe('new@e.com');
});
