<?php

use App\Models\BudgetLigne;
use App\Models\Client;
use App\Models\Exercice;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\OperationFinanciere;
use App\Services\Finance\OperationFinanciereService;
use Database\Seeders\ComptesPaieOhadaSeeder;

beforeEach(function () {
    seedRoles();
    // Seed des comptes utilisés par le service (idempotent)
    (new ComptesPaieOhadaSeeder())->run();
});

function exFinOps(): Exercice
{
    Exercice::where('statut', 2)->update(['statut' => 3]);
    return Exercice::create([
        'exercice' => 'OPS-' . random_int(100, 999),
        'libelle'  => 'Test Ops',
        'statut'   => 2,
        'datedebut'=> '2026-01-01',
        'datefin'  => '2026-12-31',
    ]);
}

function ligneValidee(Exercice $ex, float $budget = 1_000_000): BudgetLigne
{
    return BudgetLigne::create([
        'id_budgetligne' => 'BL-' . random_int(1000, 9999),
        'id_exercicebudgetaire' => $ex->id,
        'dotation_etat'  => $budget,
        'isvalide'       => 1, // validée
        'id_user'        => auth()->id() ?? 1,
    ]);
}

function makeClient(): Client
{
    return Client::create([
        'code'           => 'C-' . random_int(1000, 9999),
        'raison_sociale' => 'Test Client SARL',
        'statut'         => 1,
    ]);
}

function makeFournisseur(): \App\Models\Intranet\ContactOrganisation
{
    // Depuis la fusion Client/Fournisseur → ContactOrganisation typée (2026-07-09),
    // les factures pointent vers cette table (Fournisseur/Client legacy conservés).
    return \App\Models\Intranet\ContactOrganisation::create([
        'nom'            => 'Test Fournisseur ' . random_int(1000, 9999),
        'raison_sociale' => 'TEST FRN SARL',
        'type'           => 'fournisseur',
        'actif'          => true,
        'statut'         => 1,
        'created_by'     => \App\Models\User::factory()->create()->id,
    ]);
}

// ─── CLIENTS (fusionnés dans intranet_contacts type=client depuis 2026-07-09) ─────

it('les routes legacy /finance/clients redirigent vers /intranet/organisations', function () {
    actingAsSuperAdmin();
    $this->get('/finance/clients')
        ->assertRedirect(route('intranet.organisations.index', ['type' => 'client']));
    $this->get('/finance/clients/create')
        ->assertRedirect(route('intranet.organisations.create', ['type' => 'client']));
});

// ─── FACTURES ──────────────────────────────────────────

it('crée une facture fournisseur en brouillon avec TTC calculé', function () {
    actingAsSuperAdmin();
    $f = makeFournisseur();
    $this->post('/finance/factures', [
        'sens'          => 'depense',
        'tiers_type'    => 'fournisseur',
        'tiers_id'      => $f->id,
        'date_emission' => '2026-05-15',
        'objet'         => 'Achat matériel',
        'montant_ht'    => 100_000,
        'taux_tva'      => 18,
    ])->assertRedirect();

    $facture = Facture::where('objet', 'Achat matériel')->first();
    expect($facture)->not->toBeNull();
    expect((float) $facture->montant_tva)->toBe(18_000.0);
    expect((float) $facture->montant_ttc)->toBe(118_000.0);
    expect($facture->statut)->toBe(0); // brouillon
});

it('valide une facture brouillon (statut 0 → 1)', function () {
    actingAsSuperAdmin();
    $f = makeFournisseur();
    $facture = Facture::create([
        'numero' => 'F-V1', 'sens' => 'depense',
        'tiers_type' => 'fournisseur', 'tiers_id' => $f->id,
        'date_emission' => now()->toDateString(),
        'objet' => 'Test', 'montant_ht' => 50_000, 'montant_ttc' => 50_000,
        'statut' => 0,
    ]);
    $this->post("/finance/factures/{$facture->id}/valider")->assertRedirect();
    expect($facture->fresh()->statut)->toBe(1);
});

it('annule une facture avec motif', function () {
    actingAsSuperAdmin();
    $f = makeFournisseur();
    $facture = Facture::create([
        'numero' => 'F-A1', 'sens' => 'depense',
        'tiers_type' => 'fournisseur', 'tiers_id' => $f->id,
        'date_emission' => now()->toDateString(),
        'objet' => 'Test', 'montant_ht' => 50_000, 'montant_ttc' => 50_000,
        'statut' => 1, // validée
    ]);
    $this->post("/finance/factures/{$facture->id}/annuler", [
        'motif_annulation' => 'Erreur fournisseur',
    ])->assertRedirect();
    expect($facture->fresh()->statut)->toBe(4);
    expect($facture->fresh()->motif_annulation)->toBe('Erreur fournisseur');
});

// ─── OPÉRATIONS FINANCIÈRES ─────────────────────────────

it('refuse une opération de dépense sans ligne budgétaire', function () {
    actingAsSuperAdmin();
    $this->post('/finance/operations', [
        'type_operation' => 'depense',
        'date_operation' => '2026-05-15',
        'objet'          => 'Sans ligne',
        'montant'        => 10_000,
    ])->assertSessionHasErrors('budget_ligne_id');
});

it('crée un ordre de dépense en brouillon', function () {
    actingAsSuperAdmin();
    $ex = exFinOps();
    $ligne = ligneValidee($ex);

    $this->post('/finance/operations', [
        'type_operation'  => 'depense',
        'exercice_id'     => $ex->id,
        'budget_ligne_id' => $ligne->id,
        'date_operation'  => '2026-05-15',
        'objet'           => 'Achat de matériel informatique',
        'montant'         => 250_000,
    ])->assertRedirect();

    expect(OperationFinanciere::where('objet', 'Achat de matériel informatique')->exists())->toBeTrue();
});

it('workflow complet : soumet → approuve → exécute (engagement budget effectif)', function () {
    actingAsSuperAdmin();
    $ex = exFinOps();
    $ligne = ligneValidee($ex, 1_000_000);

    $op = OperationFinanciere::create([
        'numero'          => 'OD-TEST-001',
        'type_operation'  => 'depense',
        'exercice_id'     => $ex->id,
        'budget_ligne_id' => $ligne->id,
        'date_operation'  => '2026-05-15',
        'objet'           => 'Test workflow',
        'montant'         => 100_000,
        'statut'          => 0,
        'created_by'      => auth()->id(),
    ]);

    // 1. Soumettre
    $this->post("/finance/operations/{$op->id}/soumettre")->assertRedirect();
    expect($op->fresh()->statut)->toBe(1);

    // 2. Approuver
    $this->post("/finance/operations/{$op->id}/approuver")->assertRedirect();
    expect($op->fresh()->statut)->toBe(2);

    // 3. Exécuter : engagement réel sur la ligne
    $this->post("/finance/operations/{$op->id}/executer")->assertRedirect();
    expect($op->fresh()->statut)->toBe(3);
    expect((float) $ligne->fresh()->engagement)->toBe(100_000.0);
});

it('refuse l\'approbation si solde de la ligne insuffisant', function () {
    actingAsSuperAdmin();
    $ex = exFinOps();
    $ligne = ligneValidee($ex, 50_000); // budget 50K seulement

    $op = OperationFinanciere::create([
        'numero'          => 'OD-OF',
        'type_operation'  => 'depense',
        'exercice_id'     => $ex->id,
        'budget_ligne_id' => $ligne->id,
        'date_operation'  => now()->toDateString(),
        'objet'           => 'Trop cher',
        'montant'         => 200_000, // > 50K
        'statut'          => 1, // soumise
        'created_by'      => auth()->id(),
    ]);

    $this->post("/finance/operations/{$op->id}/approuver")
        ->assertRedirect()
        ->assertSessionHas('error');
    expect($op->fresh()->statut)->toBe(1); // reste soumise
});

it('annule une opération exécutée : libère l\'engagement', function () {
    actingAsSuperAdmin();
    $ex = exFinOps();
    $ligne = ligneValidee($ex);

    $op = OperationFinanciere::create([
        'numero'          => 'OD-ANN',
        'type_operation'  => 'depense',
        'exercice_id'     => $ex->id,
        'budget_ligne_id' => $ligne->id,
        'date_operation'  => now()->toDateString(),
        'objet'           => 'À annuler',
        'montant'         => 100_000,
        'statut'          => 2, // approuvée
        'created_by'      => auth()->id(),
    ]);

    (new OperationFinanciereService())->executer($op, auth()->id());
    expect((float) $ligne->fresh()->engagement)->toBe(100_000.0);

    $this->post("/finance/operations/{$op->id}/annuler")->assertRedirect();
    expect($op->fresh()->statut)->toBe(5);
    expect((float) $ligne->fresh()->engagement)->toBe(0.0);
});

it('génère un numéro unique automatique', function () {
    expect(OperationFinanciereService::genererNumero('depense'))->toStartWith('OD-');
    expect(OperationFinanciereService::genererNumero('recette'))->toStartWith('OR-');
    expect(OperationFinanciereService::genererNumeroFacture('depense'))->toStartWith('FF-');
    expect(OperationFinanciereService::genererNumeroFacture('recette'))->toStartWith('FC-');
});

it('l\'exécution d\'une dépense vers un fournisseur génère des écritures comptables', function () {
    actingAsSuperAdmin();
    $ex = exFinOps();
    $ligne = ligneValidee($ex);
    $fourn = makeFournisseur();

    $op = OperationFinanciere::create([
        'numero'          => 'OD-COMP',
        'type_operation'  => 'depense',
        'exercice_id'     => $ex->id,
        'budget_ligne_id' => $ligne->id,
        'tiers_type'      => 'fournisseur',
        'tiers_id'        => $fourn->id,
        'date_operation'  => now()->toDateString(),
        'objet'           => 'Avec compta',
        'montant'         => 100_000,
        'statut'          => 2,
        'created_by'      => auth()->id(),
    ]);

    (new OperationFinanciereService())->executer($op, auth()->id());

    // Une trace dans le grand-livre avec ref_piece = numero
    expect(\App\Models\GrandLivre::where('ref_piece', 'OD-COMP')->count())->toBeGreaterThan(0);
});
