<?php

use App\Models\Emplacement;
use App\Models\Produit;

beforeEach(function () {
    seedRoles();
    $this->article = Produit::create([
        'designation' => 'Test dissocié', 'type_article' => 'bien', 'est_stockable' => true,
        'unite_mesure' => 'u', 'prix_unitaire' => 500, 'statut' => 1,
    ]);
    $this->emp = Emplacement::create(['code' => 'DIS-01', 'libelle' => 'Emp dis', 'actif' => true]);
});

it('la page edit ne contient plus les tabs stocks/mouvements/inventaires', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('referentiel.catalogue.edit', $this->article));
    $r->assertStatus(200);
    $r->assertSee('Modifier — Test dissocié');
    // Le tab-content et les tabs stock sont retirés
    $r->assertDontSee('Ventilation par emplacement');
    $r->assertDontSee('modal-ajustement');
    $r->assertDontSee('modal-transfert');
    $r->assertDontSee('tab-stock');
});

it('le formulaire dédié ajustement s\'affiche via GET', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('referentiel.catalogue.ajuster-stock.form', $this->article));
    $r->assertStatus(200);
    $r->assertSee('Ajustement manuel de stock');
    $r->assertSee('name="emplacement_id"', false);
    $r->assertSee('name="delta"', false);
    $r->assertSee('name="motif"', false);
});

it('ajustement POST redirige vers la fiche show avec success', function () {
    actingAsSuperAdmin();
    $this->post(route('referentiel.catalogue.ajuster-stock', $this->article), [
        'emplacement_id' => $this->emp->id, 'delta' => 3, 'motif' => 'Test dissocie',
    ])
        ->assertRedirect(route('referentiel.catalogue.show', $this->article))
        ->assertSessionHas('success');
    expect((float) $this->article->fresh()->stock_actuel)->toBe(3.0);
});

it('le formulaire dédié transfert redirige si aucun stock source', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('referentiel.catalogue.transferer-stock.form', $this->article));
    $r->assertRedirect(route('referentiel.catalogue.show', $this->article));
    $r->assertSessionHas('error');
});

it('le formulaire dédié transfert s\'affiche si stock présent', function () {
    actingAsSuperAdmin();
    $this->article->ajusterStockEmplacement($this->emp->id, 10);
    $r = $this->get(route('referentiel.catalogue.transferer-stock.form', $this->article));
    $r->assertStatus(200);
    $r->assertSee('Transfert entre emplacements');
    $r->assertSee('name="emplacement_source_id"', false);
    $r->assertSee('name="emplacement_dest_id"', false);
    $r->assertSee('name="quantite"', false);
});

it('transfert POST redirige vers la fiche show', function () {
    actingAsSuperAdmin();
    $emp2 = Emplacement::create(['code' => 'DIS-02', 'libelle' => 'E2', 'actif' => true]);
    $this->article->ajusterStockEmplacement($this->emp->id, 10);

    $this->post(route('referentiel.catalogue.transferer-stock', $this->article), [
        'emplacement_source_id' => $this->emp->id,
        'emplacement_dest_id' => $emp2->id,
        'quantite' => 4,
    ])
        ->assertRedirect(route('referentiel.catalogue.show', $this->article))
        ->assertSessionHas('success');
});

it('la fiche show affiche les liens vers les formulaires dédiés', function () {
    actingAsSuperAdmin();
    $this->article->ajusterStockEmplacement($this->emp->id, 5);
    $r = $this->get(route('referentiel.catalogue.show', $this->article));
    $r->assertStatus(200);
    $r->assertSee(route('referentiel.catalogue.ajuster-stock.form', $this->article), false);
    $r->assertSee(route('referentiel.catalogue.transferer-stock.form', $this->article), false);
});

it('formulaires refusés sur un service', function () {
    actingAsSuperAdmin();
    $s = Produit::create(['designation' => 'Service', 'type_article' => 'service', 'est_stockable' => false, 'statut' => 1]);
    $this->get(route('referentiel.catalogue.ajuster-stock.form', $s))->assertStatus(404);
    $this->get(route('referentiel.catalogue.transferer-stock.form', $s))->assertStatus(404);
});
