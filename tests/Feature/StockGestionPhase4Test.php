<?php

use App\Models\CommandeFournisseur;
use App\Models\CommandeInterne;
use App\Models\Emplacement;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Inventaire;
use App\Models\Produit;
use App\Models\ProduitEmplacement;
use App\Models\ProduitMouvement;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->user = User::factory()->create();
    $this->magasin = Emplacement::create([
        'code' => 'MAG-01', 'libelle' => 'Magasin principal', 'type' => 'magasin', 'actif' => true,
    ]);
    $this->etagere = Emplacement::create([
        'code' => 'ETA-A1', 'libelle' => 'Étagère A1', 'type' => 'etagere', 'parent_id' => $this->magasin->id, 'actif' => true,
    ]);
    $this->produit = Produit::create([
        'code' => 'STK-001', 'designation' => 'Ramette A4',
        'type_article' => 'bien', 'est_stockable' => true,
        'prix_unitaire' => 3500, 'unite_mesure' => 'ramette',
        'stock_actuel' => 0, 'seuil_alerte' => 5, 'statut' => 1,
    ]);
});

// ═════════ Emplacements hiérarchiques ═════════

it('crée un emplacement racine + un enfant via HTTP', function () {
    actingAsSuperAdmin();
    $this->post(route('referentiel.emplacements.store'), [
        'code' => 'MAG-NEW', 'libelle' => 'Nouveau magasin', 'type' => 'magasin',
    ])->assertRedirect();
    expect(Emplacement::where('code', 'MAG-NEW')->exists())->toBeTrue();
});

it('empêche un cycle dans la hiérarchie des emplacements', function () {
    actingAsSuperAdmin();
    $this->put(route('referentiel.emplacements.update', $this->magasin), [
        'code' => 'MAG-01', 'libelle' => 'X', 'parent_id' => $this->etagere->id,
    ])->assertSessionHas('error');
});

it('refuse la suppression d\'un emplacement contenant du stock', function () {
    actingAsSuperAdmin();
    ProduitEmplacement::create(['produit_id' => $this->produit->id, 'emplacement_id' => $this->magasin->id, 'quantite' => 5]);
    $this->delete(route('referentiel.emplacements.destroy', $this->magasin))->assertSessionHas('error');
    expect(Emplacement::find($this->magasin->id))->not->toBeNull();
});

it('calcule le chemin hiérarchique d\'un emplacement', function () {
    expect($this->etagere->chemin)->toBe('Magasin principal › Étagère A1');
});

// ═════════ Ajustement stock par emplacement ═════════

it('crée la ligne pivot au premier ajustement + agrège stock_actuel', function () {
    $nouvelle = $this->produit->ajusterStockEmplacement($this->magasin->id, 10);
    expect($nouvelle)->toBe(10.0);
    expect((float) $this->produit->fresh()->stock_actuel)->toBe(10.0);
    expect(ProduitEmplacement::where('produit_id', $this->produit->id)->where('emplacement_id', $this->magasin->id)->first()->quantite)
        ->toBe('10.000');
});

it('agrège correctement sur plusieurs emplacements', function () {
    $this->produit->ajusterStockEmplacement($this->magasin->id, 15);
    $this->produit->ajusterStockEmplacement($this->etagere->id, 7);
    expect((float) $this->produit->fresh()->stock_actuel)->toBe(22.0);
});

it('décrémente sans passer en négatif', function () {
    $this->produit->ajusterStockEmplacement($this->magasin->id, 5);
    $this->produit->ajusterStockEmplacement($this->magasin->id, -10);
    expect((float) $this->produit->fresh()->stock_actuel)->toBe(0.0);
});

// ═════════ Livraison fournisseur avec emplacement ═════════

it('livraison fournisseur alimente le pivot du bon emplacement', function () {
    actingAsSuperAdmin();
    $frn = ContactOrganisation::create(['nom' => 'F', 'type' => 'fournisseur', 'actif' => true, 'created_by' => $this->user->id]);
    $cmd = CommandeFournisseur::create([
        'numero_commande' => 'CMD-STK-01', 'fournisseur_id' => $frn->id,
        'date_commande' => now(), 'statut' => CommandeFournisseur::STATUT_APPROUVEE,
        'created_by' => $this->user->id,
    ]);
    $ligne = $cmd->lignes()->create([
        'produit_id' => $this->produit->id, 'designation' => 'Ramette',
        'quantite_commandee' => 20, 'quantite_livree' => 0, 'prix_unitaire' => 3500,
    ]);

    $this->post(route('appro.commandes.livrer', $cmd), [
        'quantites' => [$ligne->id => 20],
        'emplacements' => [$ligne->id => $this->magasin->id],
    ])->assertRedirect();

    expect((float) $this->produit->fresh()->stock_actuel)->toBe(20.0);
    expect((float) $this->produit->stockDans($this->magasin->id))->toBe(20.0);
    // Un mouvement d'entrée avec emplacement_id renseigné
    $mvt = ProduitMouvement::latest()->first();
    expect($mvt->type)->toBe('entree');
    expect($mvt->emplacement_id)->toBe($this->magasin->id);
});

// ═════════ Livraison interne décrémente le bon emplacement ═════════

it('livraison interne à agent décrémente le pivot d\'emplacement source', function () {
    // Stock initial 30 dans le magasin
    $this->produit->ajusterStockEmplacement($this->magasin->id, 30);

    actingAsSuperAdmin();
    $n1 = User::factory()->create();
    $cmd = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(),
        'objet' => 'Test', 'demandeur_id' => $this->user->id, 'superieur_id' => $n1->id,
        'date_demande' => now()->toDateString(), 'statut' => CommandeInterne::STATUT_TRANSMISE_APPRO,
    ]);
    $ligne = $cmd->lignes()->create([
        'produit_id' => $this->produit->id, 'designation' => 'Papier',
        'quantite_demandee' => 8, 'quantite_livree' => 0,
    ]);

    $this->post(route('appro.commandes-internes.livrer', $cmd), [
        'quantites' => [$ligne->id => 8],
        'emplacements_source' => [$ligne->id => $this->magasin->id],
    ])->assertRedirect();

    expect((float) $this->produit->fresh()->stock_actuel)->toBe(22.0);
    expect((float) $this->produit->stockDans($this->magasin->id))->toBe(22.0);
    $mvt = ProduitMouvement::where('type', 'sortie')->latest()->first();
    expect($mvt->emplacement_source_id)->toBe($this->magasin->id);
});

// ═════════ Inventaire complet ═════════

it('workflow inventaire complet : brouillon → en cours → clôture avec ajustements', function () {
    actingAsSuperAdmin();

    // Stock initial : 10 dans magasin
    $this->produit->ajusterStockEmplacement($this->magasin->id, 10);

    // Créer l'inventaire
    $r = $this->post(route('appro.inventaires.store'), [
        'libelle' => 'Inventaire test',
        'date_prevue' => now()->format('Y-m-d'),
        'emplacement_id' => $this->magasin->id,
    ]);
    $r->assertRedirect();
    $inv = Inventaire::latest()->first();
    expect($inv->lignes->count())->toBe(1);
    expect((float) $inv->lignes->first()->quantite_theorique)->toBe(10.0);

    // Lancer
    $this->post(route('appro.inventaires.lancer', $inv))->assertRedirect();
    expect($inv->fresh()->statut)->toBe(Inventaire::STATUT_EN_COURS);

    // Saisir : compté 7 (écart -3)
    $ligne = $inv->lignes->first();
    $this->post(route('appro.inventaires.saisie.save', $inv), [
        'lignes' => [
            ['id' => $ligne->id, 'quantite_bon_etat' => 7],
        ],
    ])->assertRedirect();
    expect((float) $ligne->fresh()->quantite_reelle)->toBe(7.0);

    // Clôturer
    $this->post(route('appro.inventaires.cloturer', $inv))->assertRedirect();
    $inv->refresh();
    expect($inv->statut)->toBe(Inventaire::STATUT_CLOTURE);
    expect((float) $this->produit->fresh()->stock_actuel)->toBe(7.0);

    // Un mouvement ajustement doit exister
    $mvt = ProduitMouvement::where('type', 'ajustement')->latest()->first();
    expect($mvt)->not->toBeNull();
    expect((float) $mvt->quantite)->toBe(3.0);
    expect($mvt->emplacement_id)->toBe($this->magasin->id);
});

it('inventaire clôture ignore les lignes non comptées', function () {
    $this->produit->ajusterStockEmplacement($this->magasin->id, 5);
    $p2 = Produit::create(['designation' => 'X', 'type_article' => 'bien', 'est_stockable' => true, 'stock_actuel' => 0, 'statut' => 1]);
    $p2->ajusterStockEmplacement($this->magasin->id, 3);

    actingAsSuperAdmin();
    $inv = Inventaire::create([
        'numero' => Inventaire::genererNumero(),
        'libelle' => 'X', 'date_prevue' => now(), 'statut' => Inventaire::STATUT_BROUILLON,
        'emplacement_id' => $this->magasin->id, 'responsable_id' => auth()->id(),
    ]);
    $inv->genererLignes();
    $inv->lancer();

    // Ne compter QUE la ligne de $this->produit (ecart -2)
    $ligne1 = $inv->lignes()->where('produit_id', $this->produit->id)->first();
    $this->post(route('appro.inventaires.saisie.save', $inv), [
        'lignes' => [['id' => $ligne1->id, 'quantite_bon_etat' => 3]],
    ]);
    $this->post(route('appro.inventaires.cloturer', $inv));

    expect((float) $this->produit->fresh()->stock_actuel)->toBe(3.0); // ajusté
    expect((float) $p2->fresh()->stock_actuel)->toBe(3.0); // inchangé (non compté)
});

it('périmètre inventaire limité à un emplacement + descendants', function () {
    $this->produit->ajusterStockEmplacement($this->magasin->id, 10);
    $this->produit->ajusterStockEmplacement($this->etagere->id, 5);
    // Un autre emplacement racine
    $autre = Emplacement::create(['code' => 'MAG-X', 'libelle' => 'Autre magasin', 'actif' => true]);
    $this->produit->ajusterStockEmplacement($autre->id, 100);

    actingAsSuperAdmin();
    $inv = Inventaire::create([
        'numero' => Inventaire::genererNumero(),
        'libelle' => 'Test périmètre', 'date_prevue' => now(),
        'emplacement_id' => $this->magasin->id,
        'statut' => Inventaire::STATUT_BROUILLON,
    ]);
    $inv->genererLignes();

    // Doit inclure magasin + étagère (2 lignes) mais PAS l'autre magasin
    expect($inv->lignes->count())->toBe(2);
    expect($inv->lignes->pluck('emplacement_id')->all())->not->toContain($autre->id);
});

// ═════════ Dashboard stock ═════════

it('dashboard stock rendu OK avec métriques', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('appro.stock.dashboard'));
    $r->assertStatus(200);
    $r->assertSee('Gestion des stocks');
    $r->assertSee('Valeur du stock');
    $r->assertSee('Emplacements actifs');
});

it('stock par emplacement rendu OK', function () {
    actingAsSuperAdmin();
    $this->produit->ajusterStockEmplacement($this->magasin->id, 12);
    $r = $this->get(route('appro.stock.emplacements'));
    $r->assertStatus(200);
    $r->assertSee('Ramette A4');
});

it('journal des mouvements rendu OK avec filtres', function () {
    actingAsSuperAdmin();
    ProduitMouvement::create([
        'produit_id' => $this->produit->id, 'type' => 'entree', 'quantite' => 5,
        'stock_apres' => 5, 'emplacement_id' => $this->magasin->id,
        'source_type' => 'test', 'source_id' => 1, 'user_id' => auth()->id(),
    ]);
    $r = $this->get(route('appro.stock.mouvements'));
    $r->assertStatus(200);
    $r->assertSee('Entrée');
});
