<?php

use App\Models\CommandeInterne;
use App\Models\Produit;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->demandeur = User::factory()->create(['statut' => 1]);
    $this->n1Actif   = User::factory()->create(['name' => 'ChefActif', 'statut' => 1]);
    $this->n1Inactif = User::factory()->create(['name' => 'ChefInactif', 'statut' => 0]);
    $this->produit = Produit::create([
        'code' => 'CI-P-01', 'designation' => 'Ramette A4',
        'type_article' => 'bien', 'est_stockable' => true,
        'unite_mesure' => 'ram', 'prix_unitaire' => 3500, 'statut' => 1,
    ]);
});

it('la liste des N+1 sur create ne contient que des utilisateurs actifs (statut=1)', function () {
    $this->actingAs($this->demandeur);
    $r = $this->get(route('appro.commandes-internes.create'));
    $r->assertStatus(200);
    $r->assertSee('ChefActif');
    $r->assertDontSee('ChefInactif');
});

it('la vue create n\'affiche plus la colonne P.U. estimé ni Désignation', function () {
    $this->actingAs($this->demandeur);
    $r = $this->get(route('appro.commandes-internes.create'));
    $r->assertStatus(200);
    $r->assertDontSee('P.U. estimé');
    // Pas d'input désignation libre
    $r->assertDontSee('name="lignes[0][designation]"', false);
    $r->assertDontSee('name="lignes[0][prix_unitaire_estime]"', false);
});

it('store : refuse une ligne sans produit_id (plus d\'article libre)', function () {
    $this->actingAs($this->demandeur);
    $this->post(route('appro.commandes-internes.store'), [
        'objet' => 'Test', 'superieur_id' => $this->n1Actif->id,
        'lignes' => [
            ['quantite_demandee' => 3], // pas de produit_id
        ],
    ])->assertSessionHasErrors('lignes.0.produit_id');
});

it('store : la désignation est dérivée du produit sélectionné', function () {
    $this->actingAs($this->demandeur);
    $this->post(route('appro.commandes-internes.store'), [
        'objet' => 'Fournitures',
        'superieur_id' => $this->n1Actif->id,
        'lignes' => [
            ['produit_id' => $this->produit->id, 'quantite_demandee' => 5],
        ],
    ])->assertRedirect();

    $c = CommandeInterne::latest()->first();
    expect($c->lignes->count())->toBe(1);
    $l = $c->lignes->first();
    expect($l->designation)->toBe('Ramette A4');
    expect($l->unite)->toBe('ram');
    expect((float) $l->quantite_demandee)->toBe(5.0);
    // Prix estimé dérivé du prix unitaire catalogue (indicatif budget)
    expect((float) $l->prix_unitaire_estime)->toBe(3500.0);
    expect((float) $c->fresh()->montant_estime)->toBe(17_500.0); // 5 × 3500
});
