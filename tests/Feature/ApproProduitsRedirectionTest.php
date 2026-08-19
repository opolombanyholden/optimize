<?php

use App\Models\Produit;

beforeEach(function () {
    seedRoles();
});

it('redirige /appro/produits vers /referentiel/catalogue', function () {
    actingAsSuperAdmin();
    $this->get('/appro/produits')->assertRedirect(route('referentiel.catalogue.index'));
});

it('redirige /appro/produits/create vers /referentiel/catalogue/create', function () {
    actingAsSuperAdmin();
    $this->get('/appro/produits/create')->assertRedirect(route('referentiel.catalogue.create'));
});

it('redirige /appro/produits/{id} vers /referentiel/catalogue/{id}', function () {
    actingAsSuperAdmin();
    $p = Produit::create(['designation' => 'Item', 'type_article' => 'bien', 'est_stockable' => true, 'statut' => 1]);
    $this->get("/appro/produits/{$p->id}")->assertRedirect(route('referentiel.catalogue.show', $p));
});

it('redirige /appro/produits/{id}/edit vers /referentiel/catalogue/{id}/edit', function () {
    actingAsSuperAdmin();
    $p = Produit::create(['designation' => 'Item', 'type_article' => 'bien', 'est_stockable' => true, 'statut' => 1]);
    $this->get("/appro/produits/{$p->id}/edit")->assertRedirect(route('referentiel.catalogue.edit', $p));
});

it('les routes nommées appro.produits.* existent toujours pour rétro-compat', function () {
    expect(app('router')->has('appro.produits.index'))->toBeTrue();
    expect(app('router')->has('appro.produits.create'))->toBeTrue();
    expect(app('router')->has('appro.produits.show'))->toBeTrue();
    expect(app('router')->has('appro.produits.edit'))->toBeTrue();
});
