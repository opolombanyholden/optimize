<?php

use App\Models\CommandeInterne;
use App\Models\Emplacement;
use App\Models\LivraisonInterne;
use App\Models\Produit;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->demandeur = User::factory()->create(['name' => 'DemLiv', 'prenoms' => 'Test', 'statut' => 1]);
    $this->autre = User::factory()->create(['name' => 'AutreRecep', 'statut' => 1]);
    $this->emp = Emplacement::create(['code' => 'RCP-01', 'libelle' => 'Stock', 'actif' => true]);
    $this->prod = Produit::create([
        'code' => 'RCP-P', 'designation' => 'Item', 'type_article' => 'bien',
        'est_stockable' => true, 'unite_mesure' => 'u', 'prix_unitaire' => 100, 'statut' => 1,
    ]);
    $this->prod->ajusterStockEmplacement($this->emp->id, 20);

    $this->cmd = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(),
        'objet' => 'Test recu_par', 'demandeur_id' => $this->demandeur->id,
        'date_demande' => now()->toDateString(), 'statut' => CommandeInterne::STATUT_TRANSMISE_APPRO,
    ]);
    $this->ligne = $this->cmd->lignes()->create([
        'produit_id' => $this->prod->id, 'designation' => 'Item',
        'quantite_demandee' => 3, 'quantite_livree' => 0,
    ]);
});

it('la modale livraison pré-sélectionne le demandeur comme "Reçu par"', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('appro.commandes-internes.show', $this->cmd));
    $r->assertStatus(200);
    // Le demandeur est proposé avec "(demandeur)" et sélectionné
    $r->assertSee('DemLiv');
    $r->assertSee('(demandeur)');
    // Autre utilisateur également disponible
    $r->assertSee('AutreRecep');
    // Le select pré-sélectionne bien le demandeur
    $r->assertSee('value="' . $this->demandeur->id . '" selected', false);
});

it('livraison enregistrée avec recu_par = demandeur par défaut', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.commandes-internes.livrer', $this->cmd), [
        'quantites' => [$this->ligne->id => 2],
        'emplacements_source' => [$this->ligne->id => $this->emp->id],
        'recu_par' => $this->demandeur->id,
    ])->assertRedirect()->assertSessionHas('success');

    $liv = LivraisonInterne::latest()->first();
    expect($liv->recu_par)->toBe($this->demandeur->id);
});

it('livraison peut être remise à un tiers différent du demandeur', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.commandes-internes.livrer', $this->cmd), [
        'quantites' => [$this->ligne->id => 2],
        'emplacements_source' => [$this->ligne->id => $this->emp->id],
        'recu_par' => $this->autre->id,
    ])->assertRedirect()->assertSessionHas('success');

    $liv = LivraisonInterne::latest()->first();
    expect($liv->recu_par)->toBe($this->autre->id);
});
