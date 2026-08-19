<?php

use App\Models\CommandeInterne;
use App\Models\Emplacement;
use App\Models\Produit;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->demandeur = User::factory()->create(['statut' => 1]);
    $this->n1 = User::factory()->create(['statut' => 1]);
    $this->empVide  = Emplacement::create(['code' => 'SEL-EMP-VIDE',  'libelle' => 'MagVide',  'actif' => true]);
    $this->empPlein = Emplacement::create(['code' => 'SEL-EMP-PLEIN', 'libelle' => 'MagPlein', 'actif' => true]);
    $this->produit = Produit::create([
        'code' => 'SEL-P', 'designation' => 'Article Sel', 'type_article' => 'bien',
        'est_stockable' => true, 'unite_mesure' => 'u', 'prix_unitaire' => 100, 'statut' => 1,
    ]);
    // Stock uniquement dans MagPlein
    $this->produit->ajusterStockEmplacement($this->empPlein->id, 5);

    $this->cmd = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(),
        'objet' => 'Test select', 'demandeur_id' => $this->demandeur->id,
        'superieur_id' => $this->n1->id, 'date_demande' => now()->toDateString(),
        'statut' => CommandeInterne::STATUT_TRANSMISE_APPRO,
    ]);
    $this->cmd->lignes()->create([
        'produit_id' => $this->produit->id, 'designation' => 'Article Sel',
        'quantite_demandee' => 3, 'quantite_livree' => 0,
    ]);
});

it('la modale livraison ne liste que les emplacements avec stock > 0 pour ce produit', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('appro.commandes-internes.show', $this->cmd));
    $r->assertStatus(200);
    // MagPlein visible (dispo > 0)
    $r->assertSee('MagPlein');
    // MagVide EXCLU du select (stock = 0 pour ce produit)
    $r->assertDontSee('MagVide');
});

it('si le produit est en rupture partout, le select est désactivé + message d\'alerte', function () {
    actingAsSuperAdmin();
    // Vide MagPlein
    $this->produit->ajusterStockEmplacement($this->empPlein->id, -5);
    expect((float) $this->produit->fresh()->stock_actuel)->toBe(0.0);

    $r = $this->get(route('appro.commandes-internes.show', $this->cmd));
    $r->assertStatus(200);
    $r->assertSee('rupture partout');
    $r->assertSee('disabled', false);
});
