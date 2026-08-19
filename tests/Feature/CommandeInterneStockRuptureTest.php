<?php

use App\Models\CommandeInterne;
use App\Models\Emplacement;
use App\Models\Produit;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->demandeur = User::factory()->create(['statut' => 1]);
    $this->n1 = User::factory()->create(['statut' => 1]);
    $this->emp = Emplacement::create(['code' => 'RUP-01', 'libelle' => 'Magasin R', 'actif' => true]);
    $this->produit = Produit::create([
        'code' => 'RUP-P', 'designation' => 'Article R', 'type_article' => 'bien',
        'est_stockable' => true, 'unite_mesure' => 'u', 'prix_unitaire' => 100, 'statut' => 1,
    ]);
    // Commande interne prête à livrer
    $this->cmd = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(),
        'objet' => 'Test rupture', 'demandeur_id' => $this->demandeur->id,
        'superieur_id' => $this->n1->id, 'date_demande' => now()->toDateString(),
        'statut' => CommandeInterne::STATUT_TRANSMISE_APPRO,
    ]);
    $this->ligne = $this->cmd->lignes()->create([
        'produit_id' => $this->produit->id, 'designation' => 'Article R',
        'quantite_demandee' => 5, 'quantite_livree' => 0,
    ]);
});

it('refuse la livraison si stock produit en RUPTURE dans l\'emplacement source', function () {
    actingAsSuperAdmin();
    // Aucun stock dans l'emplacement — rupture totale
    expect($this->produit->stockDans($this->emp->id))->toBe(0.0);

    $this->post(route('appro.commandes-internes.livrer', $this->cmd), [
        'quantites' => [$this->ligne->id => 3],
        'emplacements_source' => [$this->ligne->id => $this->emp->id],
    ])->assertSessionHas('error');

    // Rien n'a été livré, aucune décrémentation
    expect((float) $this->ligne->fresh()->quantite_livree)->toBe(0.0);
    expect($this->cmd->fresh()->livraisons()->count())->toBe(0);
});

it('refuse la livraison si stock insuffisant (partiel) dans l\'emplacement source', function () {
    actingAsSuperAdmin();
    // 2 en stock, demande de 3
    $this->produit->ajusterStockEmplacement($this->emp->id, 2);
    $this->post(route('appro.commandes-internes.livrer', $this->cmd), [
        'quantites' => [$this->ligne->id => 3],
        'emplacements_source' => [$this->ligne->id => $this->emp->id],
    ])->assertSessionHas('error');

    // Stock inchangé
    expect((float) $this->produit->fresh()->stock_actuel)->toBe(2.0);
    expect((float) $this->ligne->fresh()->quantite_livree)->toBe(0.0);
});

it('autorise la livraison si stock suffisant dans l\'emplacement source', function () {
    actingAsSuperAdmin();
    $this->produit->ajusterStockEmplacement($this->emp->id, 10);
    $this->post(route('appro.commandes-internes.livrer', $this->cmd), [
        'quantites' => [$this->ligne->id => 3],
        'emplacements_source' => [$this->ligne->id => $this->emp->id],
    ])->assertSessionHas('success');

    expect((float) $this->ligne->fresh()->quantite_livree)->toBe(3.0);
    expect((float) $this->produit->fresh()->stock_actuel)->toBe(7.0);
});

it('refuse même si 2 lignes cumulées dépassent le stock du même emplacement', function () {
    actingAsSuperAdmin();
    // Ajouter une 2ème ligne du même produit / emplacement
    $ligne2 = $this->cmd->lignes()->create([
        'produit_id' => $this->produit->id, 'designation' => 'Article R (bis)',
        'quantite_demandee' => 4, 'quantite_livree' => 0,
    ]);
    $this->produit->ajusterStockEmplacement($this->emp->id, 5); // 5 en stock
    // Ligne 1 : 3 + Ligne 2 : 3 = 6 > 5
    $this->post(route('appro.commandes-internes.livrer', $this->cmd), [
        'quantites' => [
            $this->ligne->id => 3,
            $ligne2->id => 3,
        ],
        'emplacements_source' => [
            $this->ligne->id => $this->emp->id,
            $ligne2->id => $this->emp->id,
        ],
    ])->assertSessionHas('error');

    expect((float) $this->produit->fresh()->stock_actuel)->toBe(5.0);
});
