<?php

use App\Models\Emplacement;
use App\Models\Produit;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->emp = Emplacement::create(['code' => 'EMP-01', 'libelle' => 'Magasin principal', 'type' => 'magasin', 'actif' => true]);
});

it('affiche la fiche show d\'un emplacement avec stock et sous-emplacements', function () {
    actingAsSuperAdmin();
    $enfant = Emplacement::create(['code' => 'EMP-01-A', 'libelle' => 'Étagère A', 'parent_id' => $this->emp->id, 'type' => 'etagere', 'actif' => true]);
    $prod = Produit::create(['code' => 'P-SHOW', 'designation' => 'Article stock', 'type_article' => 'bien', 'est_stockable' => true, 'unite_mesure' => 'u', 'prix_unitaire' => 1500, 'statut' => 1]);
    $prod->ajusterStockEmplacement($this->emp->id, 8);

    $r = $this->get(route('referentiel.emplacements.show', $this->emp));
    $r->assertStatus(200);
    $r->assertSee('Magasin principal');
    $r->assertSee('Article stock');
    $r->assertSee('Étagère A');
    $r->assertSee('12 000'); // 8 × 1500 = 12000 valeur (séparateur espace)
});

it('modifie un emplacement via update', function () {
    actingAsSuperAdmin();
    $this->put(route('referentiel.emplacements.update', $this->emp), [
        'code' => 'EMP-01', 'libelle' => 'Magasin renommé', 'type' => 'magasin',
        'description' => 'Nouveau descriptif', 'actif' => 1,
    ])->assertRedirect()->assertSessionHas('success');

    expect($this->emp->fresh()->libelle)->toBe('Magasin renommé');
    expect($this->emp->fresh()->description)->toBe('Nouveau descriptif');
});

it('empêche cycle : parent = descendant', function () {
    actingAsSuperAdmin();
    $enfant = Emplacement::create(['code' => 'EMP-01-E', 'libelle' => 'E', 'parent_id' => $this->emp->id, 'actif' => true]);
    $this->put(route('referentiel.emplacements.update', $this->emp), [
        'code' => 'EMP-01', 'libelle' => 'X', 'parent_id' => $enfant->id,
    ])->assertSessionHas('error');
});

it('empêche parent = soi-même', function () {
    actingAsSuperAdmin();
    $this->put(route('referentiel.emplacements.update', $this->emp), [
        'code' => 'EMP-01', 'libelle' => 'X', 'parent_id' => $this->emp->id,
    ])->assertSessionHas('error');
});

it('toggle actif/inactif via POST', function () {
    actingAsSuperAdmin();
    $this->post(route('referentiel.emplacements.toggle', $this->emp))->assertRedirect();
    expect($this->emp->fresh()->actif)->toBeFalse();
    $this->post(route('referentiel.emplacements.toggle', $this->emp))->assertRedirect();
    expect($this->emp->fresh()->actif)->toBeTrue();
});

it('l\'index affiche les boutons voir + modifier + toggle + supprimer', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('referentiel.emplacements.index'));
    $r->assertStatus(200);
    $r->assertSee(route('referentiel.emplacements.show', $this->emp), false);
    $r->assertSee('data-emp-edit', false);
    $r->assertSee('data-emp-add', false);
    $r->assertSee(route('referentiel.emplacements.toggle', $this->emp), false);
});
