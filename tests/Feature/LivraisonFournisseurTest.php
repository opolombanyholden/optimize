<?php

use App\Models\CommandeFournisseur;
use App\Models\Emplacement;
use App\Models\Intranet\ContactOrganisation;
use App\Models\LivraisonFournisseur;
use App\Models\Produit;
use App\Models\ProduitMouvement;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->user = User::factory()->create();
    $this->frn = ContactOrganisation::create(['nom' => 'Frn LF', 'type' => 'fournisseur', 'actif' => true, 'created_by' => $this->user->id]);
    $this->emp = Emplacement::create(['code' => 'LF-01', 'libelle' => 'Magasin LF', 'actif' => true]);
    $this->prod = Produit::create([
        'code' => 'LF-P-01', 'designation' => 'Papier', 'type_article' => 'bien',
        'est_stockable' => true, 'unite_mesure' => 'ram', 'prix_unitaire' => 3500,
        'statut' => 1,
    ]);
    $this->commande = CommandeFournisseur::create([
        'numero_commande' => 'CMD-LF-01', 'fournisseur_id' => $this->frn->id,
        'date_commande' => now(), 'statut' => CommandeFournisseur::STATUT_APPROUVEE,
        'created_by' => $this->user->id,
    ]);
    $this->ligne = $this->commande->lignes()->create([
        'produit_id' => $this->prod->id, 'designation' => 'Papier A4',
        'quantite_commandee' => 20, 'quantite_livree' => 0, 'prix_unitaire' => 3500,
    ]);
});

it('création : livraison totale en une fois → commande passe LIVREE', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('appro.livraisons-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_livraison' => '2027-01-15',
        'emplacement_reception_id' => $this->emp->id,
        'bon_livraison_ref' => 'BL-2027-42',
        'lignes' => [
            ['commande_ligne_id' => $this->ligne->id, 'quantite' => 20],
        ],
    ]);
    $r->assertRedirect();

    $liv = LivraisonFournisseur::latest()->first();
    expect($liv->numero)->toStartWith('LF-');
    expect($liv->type)->toBe('totale');
    expect($liv->bon_livraison_ref)->toBe('BL-2027-42');
    expect((float) $liv->lignes->sum('quantite'))->toBe(20.0);

    // Commande passe LIVREE
    expect($this->commande->fresh()->statut)->toBe(CommandeFournisseur::STATUT_LIVREE);
    // Ligne commande : quantite_livree = 20
    expect((float) $this->ligne->fresh()->quantite_livree)->toBe(20.0);
    // Stock produit incrémenté
    expect((float) $this->prod->fresh()->stock_actuel)->toBe(20.0);
    expect((float) $this->prod->stockDans($this->emp->id))->toBe(20.0);
    // Mouvement d'entrée créé
    $mvt = ProduitMouvement::where('source_type', LivraisonFournisseur::class)->where('source_id', $liv->id)->first();
    expect($mvt)->not->toBeNull();
    expect($mvt->type)->toBe('entree');
});

it('livraison partielle → commande passe LIVREE_PARTIELLE + type = partielle', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.livraisons-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_livraison' => '2027-01-15',
        'emplacement_reception_id' => $this->emp->id,
        'lignes' => [
            ['commande_ligne_id' => $this->ligne->id, 'quantite' => 8],
        ],
    ])->assertRedirect();

    expect(LivraisonFournisseur::latest()->first()->type)->toBe('partielle');
    expect($this->commande->fresh()->statut)->toBe(CommandeFournisseur::STATUT_LIVREE_PARTIELLE);
    expect((float) $this->ligne->fresh()->quantite_livree)->toBe(8.0);
});

it('multi-livraisons séquencées : la 2e complète → LIVREE', function () {
    actingAsSuperAdmin();

    // Livraison 1 (partielle)
    $this->post(route('appro.livraisons-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_livraison' => '2027-01-15',
        'emplacement_reception_id' => $this->emp->id,
        'bon_livraison_ref' => 'BL-01',
        'lignes' => [['commande_ligne_id' => $this->ligne->id, 'quantite' => 12]],
    ])->assertRedirect();

    expect($this->commande->fresh()->statut)->toBe(CommandeFournisseur::STATUT_LIVREE_PARTIELLE);
    expect($this->commande->fresh()->livraisons()->count())->toBe(1);

    // Livraison 2 (complète le reste)
    $this->post(route('appro.livraisons-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_livraison' => '2027-01-20',
        'emplacement_reception_id' => $this->emp->id,
        'bon_livraison_ref' => 'BL-02',
        'lignes' => [['commande_ligne_id' => $this->ligne->id, 'quantite' => 8]],
    ])->assertRedirect();

    expect($this->commande->fresh()->statut)->toBe(CommandeFournisseur::STATUT_LIVREE);
    expect($this->commande->fresh()->livraisons()->count())->toBe(2);
    expect((float) $this->ligne->fresh()->quantite_livree)->toBe(20.0);
    expect((float) $this->prod->fresh()->stock_actuel)->toBe(20.0);
});

it('refus : livraison si commande non approuvée', function () {
    actingAsSuperAdmin();
    $this->commande->update(['statut' => CommandeFournisseur::STATUT_BROUILLON]);
    $this->post(route('appro.livraisons-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_livraison' => '2027-01-15',
        'lignes' => [['commande_ligne_id' => $this->ligne->id, 'quantite' => 5]],
    ])->assertSessionHas('error');
    expect(LivraisonFournisseur::count())->toBe(0);
});

it('refus : quantité dépassant le reste à livrer', function () {
    actingAsSuperAdmin();
    $this->ligne->update(['quantite_livree' => 15]);
    $this->post(route('appro.livraisons-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_livraison' => '2027-01-15',
        'lignes' => [['commande_ligne_id' => $this->ligne->id, 'quantite' => 10]], // reste=5, demandé=10
    ])->assertSessionHas('error');
});

it('refus : aucune quantité utile', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.livraisons-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_livraison' => '2027-01-15',
        'lignes' => [['commande_ligne_id' => $this->ligne->id, 'quantite' => 0]],
    ])->assertSessionHas('error');
});

it('emplacement de réception override par ligne', function () {
    actingAsSuperAdmin();
    $autre = Emplacement::create(['code' => 'LF-02', 'libelle' => 'Autre', 'actif' => true]);
    $this->post(route('appro.livraisons-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_livraison' => '2027-01-15',
        'emplacement_reception_id' => $this->emp->id,
        'lignes' => [
            ['commande_ligne_id' => $this->ligne->id, 'quantite' => 20, 'emplacement_id' => $autre->id],
        ],
    ])->assertRedirect();

    expect((float) $this->prod->stockDans($autre->id))->toBe(20.0);
    expect((float) $this->prod->stockDans($this->emp->id))->toBe(0.0);
});

it('page create rend le formulaire quand commande_id est fourni', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('appro.livraisons-fournisseur.create', ['commande_id' => $this->commande->id]));
    $r->assertStatus(200);
    $r->assertSee('Enregistrer une livraison fournisseur');
    $r->assertSee('N° bon de livraison fournisseur');
    $r->assertSee($this->ligne->designation);
});

it('page create sans commande_id liste les commandes livrables', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('appro.livraisons-fournisseur.create'));
    $r->assertStatus(200);
    $r->assertSee('Sélectionner la commande fournisseur');
    $r->assertSee('CMD-LF-01');
});

it('page show affiche la livraison + son BL', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.livraisons-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_livraison' => '2027-01-15',
        'bon_livraison_ref' => 'BL-SHOW-99',
        'lignes' => [['commande_ligne_id' => $this->ligne->id, 'quantite' => 5]],
    ])->assertRedirect();

    $liv = LivraisonFournisseur::latest()->first();
    $r = $this->get(route('appro.livraisons-fournisseur.show', $liv));
    $r->assertStatus(200);
    $r->assertSee($liv->numero);
    $r->assertSee('BL-SHOW-99');
    $r->assertSee('Papier A4');
});

it('page index rend la liste avec filtres', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.livraisons-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_livraison' => '2027-01-15',
        'lignes' => [['commande_ligne_id' => $this->ligne->id, 'quantite' => 5]],
    ]);
    $r = $this->get(route('appro.livraisons-fournisseur.index'));
    $r->assertStatus(200);
    $r->assertSee('Livraisons fournisseur');
    $r->assertSee('LF-');
});
