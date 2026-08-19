<?php

use App\Models\Emplacement;
use App\Models\Produit;
use App\Models\ProduitMouvement;

beforeEach(function () {
    seedRoles();
    $this->emp1 = Emplacement::create(['code' => 'STK-P-1', 'libelle' => 'Magasin', 'actif' => true]);
    $this->emp2 = Emplacement::create(['code' => 'STK-P-2', 'libelle' => 'Atelier', 'actif' => true]);
    $this->article = Produit::create([
        'designation' => 'Câble RJ45', 'type_article' => 'bien', 'est_stockable' => true,
        'unite_mesure' => 'm', 'prix_unitaire' => 800, 'seuil_alerte' => 20, 'statut' => 1,
    ]);
});

it('la page edit contient uniquement le formulaire fiche produit', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('referentiel.catalogue.edit', $this->article));
    $r->assertStatus(200);
    $r->assertSee('Modifier — Câble RJ45');
    // Le formulaire fiche
    $r->assertSee('name="designation"', false);
    // Plus de tabs stock — gestion dans formulaires dédiés
    $r->assertDontSee('Ventilation par emplacement');
    $r->assertDontSee('modal-ajustement');
});

it('la page edit d\'un service ne montre que le formulaire fiche', function () {
    actingAsSuperAdmin();
    $service = Produit::create(['designation' => 'Prestation', 'type_article' => 'service', 'est_stockable' => false, 'statut' => 1]);
    $r = $this->get(route('referentiel.catalogue.edit', $service));
    $r->assertStatus(200);
    $r->assertSee('Modifier — Prestation');
    $r->assertDontSee('Ventilation par emplacement');
});

it('la fiche show affiche la ventilation par emplacement', function () {
    actingAsSuperAdmin();
    $this->article->ajusterStockEmplacement($this->emp1->id, 12);
    $r = $this->get(route('referentiel.catalogue.show', $this->article));
    $r->assertStatus(200);
    $r->assertSee('Ventilation par emplacement');
    $r->assertSee('Magasin');
});

it('ajuste le stock (delta positif) : crée mouvement + met à jour pivot + agrégat', function () {
    actingAsSuperAdmin();
    $this->post(route('referentiel.catalogue.ajuster-stock', $this->article), [
        'emplacement_id' => $this->emp1->id,
        'delta' => 15,
        'motif' => 'Initialisation stock',
    ])->assertRedirect()->assertSessionHas('success');

    expect((float) $this->article->fresh()->stock_actuel)->toBe(15.0);
    expect((float) $this->article->stockDans($this->emp1->id))->toBe(15.0);
    $m = ProduitMouvement::latest()->first();
    expect($m->type)->toBe('ajustement');
    expect((float) $m->quantite)->toBe(15.0);
    expect($m->emplacement_id)->toBe($this->emp1->id);
});

it('refuse un ajustement négatif dépassant le stock local', function () {
    actingAsSuperAdmin();
    $this->article->ajusterStockEmplacement($this->emp1->id, 5);
    $this->post(route('referentiel.catalogue.ajuster-stock', $this->article), [
        'emplacement_id' => $this->emp1->id,
        'delta' => -10,
        'motif' => 'Test négatif',
    ])->assertSessionHas('error');
    expect((float) $this->article->fresh()->stock_actuel)->toBe(5.0);
});

it('refuse un delta = 0', function () {
    actingAsSuperAdmin();
    $this->post(route('referentiel.catalogue.ajuster-stock', $this->article), [
        'emplacement_id' => $this->emp1->id,
        'delta' => 0,
        'motif' => 'Test zéro',
    ])->assertSessionHasErrors('delta');
});

it('transfère du stock entre deux emplacements', function () {
    actingAsSuperAdmin();
    $this->article->ajusterStockEmplacement($this->emp1->id, 30);

    $this->post(route('referentiel.catalogue.transferer-stock', $this->article), [
        'emplacement_source_id' => $this->emp1->id,
        'emplacement_dest_id' => $this->emp2->id,
        'quantite' => 10,
        'motif' => 'Réappro atelier',
    ])->assertRedirect()->assertSessionHas('success');

    expect((float) $this->article->stockDans($this->emp1->id))->toBe(20.0);
    expect((float) $this->article->stockDans($this->emp2->id))->toBe(10.0);
    expect((float) $this->article->fresh()->stock_actuel)->toBe(30.0); // total inchangé

    $m = ProduitMouvement::where('type', 'transfert')->latest()->first();
    expect($m)->not->toBeNull();
    expect($m->emplacement_source_id)->toBe($this->emp1->id);
    expect($m->emplacement_id)->toBe($this->emp2->id);
});

it('refuse un transfert dépassant le stock source', function () {
    actingAsSuperAdmin();
    $this->article->ajusterStockEmplacement($this->emp1->id, 5);
    $this->post(route('referentiel.catalogue.transferer-stock', $this->article), [
        'emplacement_source_id' => $this->emp1->id,
        'emplacement_dest_id' => $this->emp2->id,
        'quantite' => 20,
    ])->assertSessionHas('error');
});

it('refuse un transfert vers le même emplacement', function () {
    actingAsSuperAdmin();
    $this->article->ajusterStockEmplacement($this->emp1->id, 5);
    $this->post(route('referentiel.catalogue.transferer-stock', $this->article), [
        'emplacement_source_id' => $this->emp1->id,
        'emplacement_dest_id' => $this->emp1->id,
        'quantite' => 2,
    ])->assertSessionHasErrors('emplacement_dest_id');
});

it('refuse ajustement / transfert sur un article non stockable', function () {
    actingAsSuperAdmin();
    $service = Produit::create(['designation' => 'S', 'type_article' => 'service', 'est_stockable' => false, 'statut' => 1]);
    $this->post(route('referentiel.catalogue.ajuster-stock', $service), [
        'emplacement_id' => $this->emp1->id, 'delta' => 5, 'motif' => 'X',
    ])->assertSessionHas('error');
});

it('la fiche show ventile le stock sur plusieurs emplacements', function () {
    actingAsSuperAdmin();
    $this->article->ajusterStockEmplacement($this->emp1->id, 12);
    $this->article->ajusterStockEmplacement($this->emp2->id, 8);

    $r = $this->get(route('referentiel.catalogue.show', $this->article));
    $r->assertStatus(200);
    $r->assertSee('Magasin');
    $r->assertSee('Atelier');
    $r->assertSee('20,000');
});
