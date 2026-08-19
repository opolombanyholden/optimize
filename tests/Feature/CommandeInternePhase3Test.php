<?php

use App\Models\CommandeInterne;
use App\Models\Produit;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->demandeur = User::factory()->create();
    $this->n1 = User::factory()->create();
    $this->appro = User::factory()->create();
    $this->appro->assignRole('super-admin');
    $this->produit = Produit::create([
        'code' => 'PROD-CI-01', 'designation' => 'Papier', 'type_article' => 'bien',
        'est_stockable' => true, 'stock_actuel' => 100, 'seuil_alerte' => 10, 'unite_mesure' => 'ramette',
        'prix_unitaire' => 3500, 'statut' => 1,
    ]);
});

it('crée une commande interne en brouillon avec lignes', function () {
    $this->actingAs($this->demandeur);
    $r = $this->post(route('appro.commandes-internes.store'), [
        'objet' => 'Fournitures janvier',
        'superieur_id' => $this->n1->id,
        'date_besoin' => '2027-02-15',
        'lignes' => [
            ['produit_id' => $this->produit->id, 'designation' => 'Papier A4', 'quantite_demandee' => 5, 'prix_unitaire_estime' => 3500],
        ],
    ]);
    $r->assertRedirect();
    $c = CommandeInterne::latest()->first();
    expect($c->statut)->toBe(CommandeInterne::STATUT_BROUILLON);
    expect($c->numero)->toStartWith('CI-');
    expect((float) $c->montant_estime)->toBe(17_500.0);
    expect($c->lignes->count())->toBe(1);
});

it('workflow complet : brouillon → N+1 → Appro → livrée', function () {
    $this->actingAs($this->demandeur);
    $this->post(route('appro.commandes-internes.store'), [
        'objet' => 'Fournitures',
        'superieur_id' => $this->n1->id,
        'lignes' => [['produit_id' => $this->produit->id, 'designation' => 'Papier', 'quantite_demandee' => 3, 'prix_unitaire_estime' => 3500]],
    ]);
    $c = CommandeInterne::latest()->first();

    // Demandeur soumet au N+1
    $this->post(route('appro.commandes-internes.soumettre-n1', $c))->assertRedirect();
    expect($c->fresh()->statut)->toBe(CommandeInterne::STATUT_EN_ATTENTE_N1);

    // N+1 valide
    $this->actingAs($this->n1);
    $this->post(route('appro.commandes-internes.valider-n1', $c))->assertRedirect();
    expect($c->fresh()->statut)->toBe(CommandeInterne::STATUT_VALIDEE_N1);

    // Appro prend en charge
    $this->actingAs($this->appro);
    $this->post(route('appro.commandes-internes.transmettre', $c))->assertRedirect();
    expect($c->fresh()->statut)->toBe(CommandeInterne::STATUT_TRANSMISE_APPRO);

    // Livraison complète (tout d'un coup)
    $ligne = $c->lignes->first();
    $this->post(route('appro.commandes-internes.livrer', $c), [
        'quantites' => [$ligne->id => 3],
    ])->assertRedirect();
    $c->refresh();
    expect($c->statut)->toBe(CommandeInterne::STATUT_LIVREE);
    expect($c->livraisons->count())->toBe(1);
    expect($c->livraisons->first()->type)->toBe('complete');
    // Stock décrémenté
    expect((float) $this->produit->fresh()->stock_actuel)->toBe(97.0);
});

it('livraison partielle puis complémentaire', function () {
    $this->actingAs($this->demandeur);
    $c = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(),
        'objet' => 'X', 'demandeur_id' => $this->demandeur->id,
        'superieur_id' => $this->n1->id,
        'date_demande' => now()->toDateString(),
        'statut' => CommandeInterne::STATUT_TRANSMISE_APPRO,
    ]);
    $ligne = $c->lignes()->create([
        'produit_id' => $this->produit->id, 'designation' => 'Papier',
        'quantite_demandee' => 10, 'quantite_livree' => 0, 'prix_unitaire_estime' => 3500,
    ]);

    $this->actingAs($this->appro);
    // Livraison partielle 4/10
    $this->post(route('appro.commandes-internes.livrer', $c), ['quantites' => [$ligne->id => 4]])->assertRedirect();
    $c->refresh();
    expect($c->statut)->toBe(CommandeInterne::STATUT_LIVREE_PARTIELLE);
    expect((float) $ligne->fresh()->quantite_livree)->toBe(4.0);

    // Livraison complémentaire 6/10
    $this->post(route('appro.commandes-internes.livrer', $c), ['quantites' => [$ligne->id => 6]])->assertRedirect();
    $c->refresh();
    expect($c->statut)->toBe(CommandeInterne::STATUT_LIVREE);
    expect((float) $ligne->fresh()->quantite_livree)->toBe(10.0);
    expect($c->livraisons->count())->toBe(2);
});

it('N+1 peut refuser avec motif → statut refusé, éditable à nouveau', function () {
    $this->actingAs($this->demandeur);
    $this->post(route('appro.commandes-internes.store'), [
        'objet' => 'Test', 'superieur_id' => $this->n1->id,
        'lignes' => [['produit_id' => $this->produit->id, 'quantite_demandee' => 1]],
    ]);
    $c = CommandeInterne::latest()->first();
    $this->post(route('appro.commandes-internes.soumettre-n1', $c));

    $this->actingAs($this->n1);
    $this->post(route('appro.commandes-internes.refuser-n1', $c), ['motif_refus_n1' => 'Budget insuffisant ce mois-ci'])
        ->assertRedirect();
    $c->refresh();
    expect($c->statut)->toBe(CommandeInterne::STATUT_REFUSEE_N1);
    expect($c->motif_refus_n1)->toContain('Budget');
    expect($c->peutEtreModifiee())->toBeTrue(); // Le demandeur peut retravailler et resoumettre
});

it('un autre utilisateur ne peut pas valider à la place du N+1', function () {
    $this->actingAs($this->demandeur);
    $this->post(route('appro.commandes-internes.store'), [
        'objet' => 'X', 'superieur_id' => $this->n1->id,
        'lignes' => [['produit_id' => $this->produit->id, 'quantite_demandee' => 1]],
    ]);
    $c = CommandeInterne::latest()->first();
    $this->post(route('appro.commandes-internes.soumettre-n1', $c));

    // Un utilisateur random essaie
    $intrus = User::factory()->create();
    $this->actingAs($intrus);
    $this->post(route('appro.commandes-internes.valider-n1', $c))->assertSessionHas('error');
    expect($c->fresh()->statut)->toBe(CommandeInterne::STATUT_EN_ATTENTE_N1);
});

it('soumission refusée sans lignes', function () {
    $this->actingAs($this->demandeur);
    $c = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(), 'objet' => 'Vide',
        'demandeur_id' => $this->demandeur->id, 'superieur_id' => $this->n1->id,
        'date_demande' => now()->toDateString(), 'statut' => CommandeInterne::STATUT_BROUILLON,
    ]);
    $this->post(route('appro.commandes-internes.soumettre-n1', $c))->assertSessionHas('error');
});

it('livraison ne peut pas dépasser la quantité demandée', function () {
    $this->actingAs($this->appro);
    $c = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(), 'objet' => 'X',
        'demandeur_id' => $this->demandeur->id, 'superieur_id' => $this->n1->id,
        'date_demande' => now()->toDateString(), 'statut' => CommandeInterne::STATUT_TRANSMISE_APPRO,
    ]);
    $ligne = $c->lignes()->create(['designation' => 'X', 'quantite_demandee' => 5, 'quantite_livree' => 0]);

    // Tentative de livrer 100 alors que 5 demandés
    $this->post(route('appro.commandes-internes.livrer', $c), ['quantites' => [$ligne->id => 100]])->assertRedirect();
    expect((float) $ligne->fresh()->quantite_livree)->toBe(5.0); // Plafonné à la demande
    expect($c->fresh()->statut)->toBe(CommandeInterne::STATUT_LIVREE);
});
