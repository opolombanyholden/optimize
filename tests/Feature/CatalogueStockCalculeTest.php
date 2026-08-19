<?php

use App\Models\Emplacement;
use App\Models\Produit;

beforeEach(function () {
    seedRoles();
});

it('affiche stock_actuel en champ calculé (readonly) sur la page edit', function () {
    actingAsSuperAdmin();
    $emp = Emplacement::create(['code' => 'CALC-1', 'libelle' => 'Emp', 'actif' => true]);
    $p = Produit::create(['designation' => 'Article calc', 'type_article' => 'bien', 'est_stockable' => true, 'unite_mesure' => 'u', 'statut' => 1]);
    $p->ajusterStockEmplacement($emp->id, 12.5);

    $r = $this->get(route('referentiel.catalogue.edit', $p));
    $r->assertStatus(200);
    // Champ affiché avec valeur calculée et readonly + disabled
    $r->assertSee('Stock actuel', false);
    $r->assertSee('12,500', false);
    $r->assertSee('readonly', false);
    $r->assertSee('disabled', false);
});

it('rejette silencieusement toute tentative de modifier stock_actuel via update', function () {
    actingAsSuperAdmin();
    $p = Produit::create(['designation' => 'X', 'type_article' => 'bien', 'est_stockable' => true, 'statut' => 1]);
    $emp = Emplacement::create(['code' => 'CALC-2', 'libelle' => 'E', 'actif' => true]);
    $p->ajusterStockEmplacement($emp->id, 5);
    expect((float) $p->fresh()->stock_actuel)->toBe(5.0);

    // On envoie un stock_actuel bidon dans le PUT : ne doit pas passer
    $this->put(route('referentiel.catalogue.update', $p), [
        'designation' => 'X', 'type_article' => 'bien', 'est_stockable' => true, 'statut' => 1,
        'stock_actuel' => 9999,
    ])->assertRedirect();

    expect((float) $p->fresh()->stock_actuel)->toBe(5.0);
});
