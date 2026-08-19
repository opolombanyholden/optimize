<?php

use App\Models\CommandeFournisseur;
use App\Models\CommandeLigne;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Produit;
use App\Models\ProduitMouvement;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->auteur = User::factory()->create();
    $this->frn = ContactOrganisation::create([
        'nom' => 'Test Frn', 'raison_sociale' => 'TEST FRN SA', 'type' => 'fournisseur',
        'actif' => true, 'created_by' => $this->auteur->id,
    ]);
    $this->prod = Produit::create([
        'code' => 'P-001', 'designation' => 'Papier A4',
        'prix_unitaire' => 5000, 'stock_actuel' => 100, 'stock_minimum' => 10,
        'statut' => 1,
    ]);
});

function payloadCmd(array $extra = []): array {
    $base = [
        'fournisseur_id' => test()->frn->id,
        'date_commande'  => '2027-01-15',
        'lignes' => [
            ['produit_id' => test()->prod->id, 'designation' => 'Papier A4', 'quantite_commandee' => 10, 'prix_unitaire' => 5000],
        ],
    ];
    if (isset($extra['lignes'])) {
        $base['lignes'] = $extra['lignes'];
        unset($extra['lignes']);
    }
    return array_merge($base, $extra);
}

// ═════════ CRUD + lignes ═════════

it('crée une commande avec numéro auto + lignes + montant HT calculé', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('appro.commandes.store'), payloadCmd());
    $r->assertRedirect();

    $cmd = CommandeFournisseur::latest()->first();
    expect($cmd)->not->toBeNull();
    expect($cmd->numero_commande)->toStartWith('CMD-');
    expect($cmd->statut)->toBe(CommandeFournisseur::STATUT_BROUILLON);
    expect($cmd->lignes->count())->toBe(1);
    expect((float) $cmd->montant_ht)->toBe(50_000.0); // 10 × 5000
});

// ═════════ Workflow ═════════

it('workflow complet : brouillon → soumise → approuvée → livrée + stock', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.commandes.store'), payloadCmd());
    $cmd = CommandeFournisseur::latest()->first();

    $this->post(route('appro.commandes.soumettre', $cmd))->assertRedirect();
    expect($cmd->fresh()->statut)->toBe(CommandeFournisseur::STATUT_SOUMISE);

    $this->post(route('appro.commandes.approuver', $cmd))->assertRedirect();
    expect($cmd->fresh()->statut)->toBe(CommandeFournisseur::STATUT_APPROUVEE);

    $stockAvant = $this->prod->fresh()->stock_actuel;
    $this->post(route('appro.commandes.livrer', $cmd))->assertRedirect();
    $cmd->refresh();
    expect($cmd->statut)->toBe(CommandeFournisseur::STATUT_LIVREE);
    expect($cmd->livre_at)->not->toBeNull();

    // Stock incrémenté
    expect((float) $this->prod->fresh()->stock_actuel)->toBe($stockAvant + 10.0);

    // Mouvement de stock créé
    $mvt = ProduitMouvement::where('source_type', CommandeFournisseur::class)
        ->where('source_id', $cmd->id)->first();
    expect($mvt)->not->toBeNull();
    expect($mvt->type)->toBe(ProduitMouvement::TYPE_ENTREE);
    expect((float) $mvt->quantite)->toBe(10.0);
});

it('livraison partielle : quantite_livree suivie ligne par ligne', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.commandes.store'), payloadCmd());
    $cmd = CommandeFournisseur::latest()->first();
    $cmd->soumettre($this->auteur->id);
    $cmd->approuver($this->auteur->id);

    $ligne = $cmd->lignes->first();
    $this->post(route('appro.commandes.livrer', $cmd), [
        'quantites' => [$ligne->id => 3],
    ])->assertRedirect();

    expect((float) $ligne->fresh()->quantite_livree)->toBe(3.0);
    expect((float) $this->prod->fresh()->stock_actuel)->toBe(103.0); // 100 + 3
    expect($cmd->fresh()->statut)->toBe(CommandeFournisseur::STATUT_LIVREE);
});

it('soumission refusée sans lignes', function () {
    actingAsSuperAdmin();
    $cmd = CommandeFournisseur::create([
        'numero_commande' => 'C-EMPTY', 'fournisseur_id' => $this->frn->id,
        'date_commande' => '2027-01-01', 'statut' => CommandeFournisseur::STATUT_BROUILLON,
        'created_by' => $this->auteur->id,
    ]);
    $this->post(route('appro.commandes.soumettre', $cmd))->assertSessionHas('error');
});

it('approbation refusée si pas encore soumise', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.commandes.store'), payloadCmd());
    $cmd = CommandeFournisseur::latest()->first();
    $this->post(route('appro.commandes.approuver', $cmd))->assertSessionHas('error');
});

// ═════════ Annulation ═════════

it('annule avec motif', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.commandes.store'), payloadCmd());
    $cmd = CommandeFournisseur::latest()->first();

    $this->post(route('appro.commandes.annuler', $cmd), [
        'motif_annulation' => 'Fournisseur non disponible en délai imparti.',
    ])->assertRedirect();

    expect($cmd->fresh()->statut)->toBe(CommandeFournisseur::STATUT_ANNULEE);
    expect($cmd->fresh()->motif_annulation)->toContain('non disponible');
});

it('annulation refusée après livraison', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.commandes.store'), payloadCmd());
    $cmd = CommandeFournisseur::latest()->first();
    $cmd->soumettre($this->auteur->id);
    $cmd->approuver($this->auteur->id);
    $cmd->livrer([], $this->auteur->id);

    $this->post(route('appro.commandes.annuler', $cmd), ['motif_annulation' => 'trop tard vraiment'])
        ->assertSessionHas('error');
    expect($cmd->fresh()->statut)->toBe(CommandeFournisseur::STATUT_LIVREE);
});

// ═════════ Génération facture ═════════

it('génère une facture depuis une commande livrée', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.commandes.store'), payloadCmd());
    $cmd = CommandeFournisseur::latest()->first();
    $cmd->soumettre($this->auteur->id);
    $cmd->approuver($this->auteur->id);
    $cmd->livrer([], $this->auteur->id);

    $r = $this->post(route('appro.commandes.generer-facture', $cmd));
    $r->assertRedirect();

    $cmd->refresh();
    expect($cmd->facture_id)->not->toBeNull();
    $facture = \App\Models\Facture::find($cmd->facture_id);
    expect($facture)->not->toBeNull();
    expect($facture->sens)->toBe('depense');
    expect((float) $facture->montant_ht)->toBe(50_000.0);
    expect($facture->tiers_id)->toBe($this->frn->id);
});

it('génération facture refusée si commande non livrée', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.commandes.store'), payloadCmd());
    $cmd = CommandeFournisseur::latest()->first();
    $this->post(route('appro.commandes.generer-facture', $cmd))->assertSessionHas('error');
    expect($cmd->fresh()->facture_id)->toBeNull();
});

it('génération facture refusée si déjà générée', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.commandes.store'), payloadCmd());
    $cmd = CommandeFournisseur::latest()->first();
    $cmd->soumettre($this->auteur->id);
    $cmd->approuver($this->auteur->id);
    $cmd->livrer([], $this->auteur->id);
    $this->post(route('appro.commandes.generer-facture', $cmd));

    // Deuxième tentative → refus
    $this->post(route('appro.commandes.generer-facture', $cmd))->assertSessionHas('error');
});
