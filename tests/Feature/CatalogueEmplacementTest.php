<?php

use App\Models\Emplacement;
use App\Models\Produit;

beforeEach(function () {
    seedRoles();
});

it('crée un article avec emplacement principal FK', function () {
    actingAsSuperAdmin();
    $e = Emplacement::create(['code' => 'MAG-CT', 'libelle' => 'Magasin catalogue', 'actif' => true]);

    $this->post(route('referentiel.catalogue.store'), [
        'designation' => 'Article FK', 'type_article' => 'bien', 'est_stockable' => true,
        'emplacement_id' => $e->id,
    ])->assertRedirect();

    $p = Produit::where('designation', 'Article FK')->first();
    expect($p->emplacement_id)->toBe($e->id);
    expect($p->emplacementPrincipal?->libelle)->toBe('Magasin catalogue');
});

it('édite l\'emplacement principal d\'un article', function () {
    actingAsSuperAdmin();
    $e1 = Emplacement::create(['code' => 'E1', 'libelle' => 'E1', 'actif' => true]);
    $e2 = Emplacement::create(['code' => 'E2', 'libelle' => 'E2', 'actif' => true]);
    $p = Produit::create(['designation' => 'X', 'type_article' => 'bien', 'est_stockable' => true, 'statut' => 1, 'emplacement_id' => $e1->id]);

    $this->put(route('referentiel.catalogue.update', $p), [
        'designation' => 'X', 'type_article' => 'bien', 'est_stockable' => true,
        'emplacement_id' => $e2->id,
    ])->assertRedirect();

    expect($p->fresh()->emplacement_id)->toBe($e2->id);
});

it('rend la page edit avec le select emplacement peuplé', function () {
    actingAsSuperAdmin();
    Emplacement::create(['code' => 'MAG-E1', 'libelle' => 'Emplacement A', 'actif' => true]);
    Emplacement::create(['code' => 'MAG-E2', 'libelle' => 'Emplacement B', 'actif' => false]); // inactif → exclu
    $p = Produit::create(['designation' => 'P', 'type_article' => 'bien', 'est_stockable' => true, 'statut' => 1]);

    $r = $this->get(route('referentiel.catalogue.edit', $p));
    $r->assertStatus(200);
    $r->assertSee('name="emplacement_id"', false);
    $r->assertSee('Emplacement A');
    // Inactif non affiché dans le select
    $r->assertDontSee('Emplacement B');
});

it('rejette un emplacement_id inexistant', function () {
    actingAsSuperAdmin();
    $this->post(route('referentiel.catalogue.store'), [
        'designation' => 'Y', 'type_article' => 'bien', 'est_stockable' => true,
        'emplacement_id' => 999999,
    ])->assertSessionHasErrors('emplacement_id');
});
