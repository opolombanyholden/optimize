<?php

use App\Models\Emplacement;
use App\Models\Produit;
use App\Models\ProduitMouvement;

beforeEach(function () {
    seedRoles();
});

it('affiche la fiche complète d\'un bien avec stock ventilé', function () {
    actingAsSuperAdmin();
    $emp = Emplacement::create(['code' => 'SHOW-E', 'libelle' => 'Magasin Show', 'actif' => true]);
    $p = Produit::create([
        'code' => 'SHOW-01', 'designation' => 'Article show', 'type_article' => 'bien',
        'est_stockable' => true, 'unite_mesure' => 'u', 'prix_unitaire' => 500,
        'seuil_alerte' => 10, 'emplacement_id' => $emp->id, 'statut' => 1,
    ]);
    $p->ajusterStockEmplacement($emp->id, 25);
    ProduitMouvement::create([
        'produit_id' => $p->id, 'type' => 'entree', 'quantite' => 25, 'stock_apres' => 25,
        'emplacement_id' => $emp->id, 'reference' => 'INIT', 'motif' => 'init',
        'source_type' => 'manual', 'source_id' => 0,
    ]);

    $r = $this->get(route('referentiel.catalogue.show', $p));
    $r->assertStatus(200);
    $r->assertSee('Article show');
    $r->assertSee('SHOW-01');
    $r->assertSee('Identification');
    $r->assertSee('Ventilation par emplacement');
    $r->assertSee('Derniers mouvements');
    $r->assertSee('Magasin Show');
    $r->assertSee('25,000');
});

it('n\'affiche pas la ventilation pour un service', function () {
    actingAsSuperAdmin();
    $s = Produit::create(['designation' => 'Prestation', 'type_article' => 'service', 'est_stockable' => false, 'statut' => 1]);
    $r = $this->get(route('referentiel.catalogue.show', $s));
    $r->assertStatus(200);
    $r->assertSee('Prestation');
    $r->assertSee('Prestation immatérielle');
    $r->assertDontSee('Ventilation par emplacement');
});

it('l\'index affiche le bouton Voir vers la fiche', function () {
    actingAsSuperAdmin();
    $p = Produit::create(['designation' => 'ItemIdx', 'type_article' => 'bien', 'est_stockable' => true, 'statut' => 1]);
    $r = $this->get(route('referentiel.catalogue.index'));
    $r->assertStatus(200);
    $r->assertSee(route('referentiel.catalogue.show', $p), false);
    $r->assertSee('fa-eye', false);
});
