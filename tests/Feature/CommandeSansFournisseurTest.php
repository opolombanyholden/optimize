<?php

use App\Models\CommandeFournisseur;
use App\Models\DevisFournisseur;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    seedRoles();
    $this->user = User::factory()->create();
    $this->prod = Produit::create([
        'code' => 'P-CSF', 'designation' => 'Item', 'type_article' => 'bien', 'est_stockable' => true,
        'prix_unitaire' => 1000, 'statut' => 1,
    ]);
});

it('crée une commande sans fournisseur (consultation ouverte)', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('appro.commandes.store'), [
        'date_commande' => now()->format('Y-m-d'),
        'lignes' => [
            ['produit_id' => $this->prod->id, 'designation' => 'Item', 'quantite_commandee' => 5, 'prix_unitaire' => 1000],
        ],
    ]);
    $r->assertRedirect();

    $c = CommandeFournisseur::latest()->first();
    expect($c)->not->toBeNull();
    expect($c->fournisseur_id)->toBeNull();
    expect($c->numero_commande)->toStartWith('CMD-');
    expect((float) $c->montant_ht)->toBe(5000.0);
});

it('l\'index/show affiche « — » pour commande sans fournisseur', function () {
    actingAsSuperAdmin();
    $c = CommandeFournisseur::create([
        'numero_commande' => 'CMD-SF-01', 'date_commande' => now(),
        'statut' => CommandeFournisseur::STATUT_BROUILLON, 'created_by' => $this->user->id,
    ]);
    $this->get(route('appro.commandes.index'))->assertStatus(200)->assertSee('CMD-SF-01');
    $this->get(route('appro.commandes.show', $c))->assertStatus(200)->assertSee('CMD-SF-01');
});

it('la sélection d\'un devis affecte automatiquement le fournisseur à la commande sans fournisseur', function () {
    actingAsSuperAdmin();
    $frn = ContactOrganisation::create(['nom' => 'Frn X', 'type' => 'fournisseur', 'actif' => true, 'created_by' => $this->user->id]);
    $c = CommandeFournisseur::create([
        'numero_commande' => 'CMD-AF', 'date_commande' => now(),
        'statut' => CommandeFournisseur::STATUT_APPROUVEE, 'created_by' => $this->user->id,
    ]);
    expect($c->fournisseur_id)->toBeNull();

    $devis = DevisFournisseur::create([
        'numero' => 'DEV-AF', 'commande_fournisseur_id' => $c->id,
        'fournisseur_id' => $frn->id, 'date_reception' => now(),
        'statut' => DevisFournisseur::STATUT_RECU,
        'montant_ht' => 100, 'montant_ttc' => 118,
    ]);
    $this->post(route('appro.devis-fournisseur.selectionner', $devis), [
        'motivation' => 'Meilleur devis',
    ])->assertRedirect();

    expect($c->fresh()->fournisseur_id)->toBe($frn->id);
});
