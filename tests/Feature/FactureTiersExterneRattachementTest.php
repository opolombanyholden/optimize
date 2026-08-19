<?php

use App\Models\Compte;
use App\Models\Exercice;
use App\Models\Facture;
use App\Models\Finance\Ordre;
use App\Models\Finance\OrdreModele;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Ligne;
use App\Models\Titre;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $this->auteur = User::factory()->create();
    $this->frn = ContactOrganisation::create([
        'nom' => 'FRN Test', 'raison_sociale' => 'FRN TEST SA', 'type' => 'fournisseur',
        'actif' => true, 'created_by' => $this->auteur->id,
    ]);
});

// ═════════ Tiers externe ═════════

it('facture dépense : accepte un fournisseur externe (non enregistré) avec infos', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('finance.factures.store'), [
        'sens' => 'depense', 'tiers_type' => 'fournisseur',
        'tiers_source' => 'externe',
        'tiers_infos'  => [
            'nom'       => 'Boutique du coin',
            'telephone' => '+241 66 00 00 00',
            'email'     => 'contact@boutique.ga',
            'adresse'   => 'BP 123, Libreville',
            'nif'       => 'NIF-EXT-001',
        ],
        'date_emission' => '2027-01-15',
        'objet'         => 'Achat matériel bureau',
        'montant_ht'    => 50_000, 'taux_tva' => 18,
    ]);
    $r->assertRedirect();

    $f = Facture::latest()->first();
    expect($f->tiers_source)->toBe('externe');
    expect($f->tiers_id)->toBeNull();
    expect($f->tiers_infos_json['nom'])->toBe('Boutique du coin');
    expect($f->tiers_infos_json['nif'])->toBe('NIF-EXT-001');
    expect($f->tiers_libelle)->toBe('Boutique du coin');
});

it('facture externe : refuse si nom manquant', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('finance.factures.store'), [
        'sens' => 'depense', 'tiers_type' => 'fournisseur',
        'tiers_source' => 'externe',
        'tiers_infos'  => ['telephone' => '+241 66'],
        'date_emission' => '2027-01-15', 'objet' => 'X', 'montant_ht' => 100,
    ]);
    $r->assertSessionHasErrors('tiers_infos.nom');
});

it('facture organisation : refuse si type ne matche pas', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('finance.factures.store'), [
        'sens' => 'recette', 'tiers_type' => 'client',
        'tiers_source' => 'organisation',
        'tiers_id'     => $this->frn->id, // fournisseur envoyé pour un client
        'date_emission' => '2027-01-15', 'objet' => 'X', 'montant_ht' => 100,
    ]);
    $r->assertSessionHasErrors('tiers_id');
});

// ═════════ Pièces jointes ═════════

it('facture : accepte plusieurs pièces jointes à la création', function () {
    Storage::fake('public');
    actingAsSuperAdmin();
    $this->post(route('finance.factures.store'), [
        'sens' => 'depense', 'tiers_type' => 'fournisseur',
        'tiers_source' => 'organisation', 'tiers_id' => $this->frn->id,
        'date_emission' => '2027-01-15', 'objet' => 'X', 'montant_ht' => 100,
        'pieces_jointes' => [
            UploadedFile::fake()->image('scan.png'),
            UploadedFile::fake()->create('recu.pdf', 500, 'application/pdf'),
        ],
    ])->assertRedirect();

    $f = Facture::latest()->first();
    expect($f->piecesJointes()->count())->toBe(2);
});

it('facture : refuse un format non autorisé', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('finance.factures.store'), [
        'sens' => 'depense', 'tiers_type' => 'fournisseur',
        'tiers_source' => 'organisation', 'tiers_id' => $this->frn->id,
        'date_emission' => '2027-01-15', 'objet' => 'X', 'montant_ht' => 100,
        'pieces_jointes' => [UploadedFile::fake()->create('bad.exe', 100)],
    ]);
    $r->assertSessionHasErrors('pieces_jointes.0');
});

// ═════════ Rattachement Ordre ↔ Facture ═════════

it('un ordre peut être rattaché à une facture du même sens', function () {
    actingAsSuperAdmin();
    $facture = Facture::create([
        'numero' => 'FA-001', 'sens' => 'depense',
        'tiers_type' => 'fournisseur', 'tiers_source' => 'organisation', 'tiers_id' => $this->frn->id,
        'date_emission' => '2027-01-01', 'objet' => 'Test',
        'montant_ht' => 100_000, 'taux_tva' => 0, 'montant_tva' => 0, 'montant_ttc' => 100_000,
        'statut' => 1, 'created_by' => $this->auteur->id,
    ]);
    // Prérequis : ordonnancement obligatoire avant tout ordre de paiement
    $facture->ordonnancer('Test setup', $this->auteur->id);

    $t = Titre::create(['imputation' => 'T-F', 'libelle' => 'F', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $ligne = Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 82001, 'libelle' => 'L', 'nature' => 'depense', 'id_user' => $this->auteur->id]);
    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $ex = Exercice::create(['exercice' => 'EX-F-' . random_int(1, 999), 'libelle' => 'F', 'statut' => Exercice::STATUT_EN_EXECUTION, 'id_user' => $this->auteur->id]);
    $bl = \App\Models\BudgetLigne::create([
        'id_budgetligne' => 'BL-F', 'id_exercicebudgetaire' => $ex->id,
        'id_codeanalytique' => $ligne->id, 'id_famillecodeanalytique' => $t->id,
        'dotation_etat' => 1_000_000, 'isvalide' => 1, 'id_user' => $this->auteur->id,
    ]);
    $compte = Compte::create([
        'code' => 'F-BQ', 'nom' => 'Cpt F', 'type' => Compte::TYPE_BANQUE,
        'solde' => 1_000_000, 'devise' => 'XAF', 'actif' => true, 'effacer' => 0,
    ]);
    $modele = OrdreModele::where('code', 'ordonnance_paiement')->first();

    $r = $this->post(route('finance.ordres.store'), [
        'modele_id' => $modele->id, 'exercice_id' => $ex->id,
        'budget_ligne_id' => $bl->id, 'compte_id' => $compte->id,
        'facture_id' => $facture->id,
        'donnees' => [
            'nature_depense' => 'X', 'imputation_budget' => '0',
            'ref_date' => '2027-01-15', 'montant' => 40_000, 'mode_reglement' => 'virement',
        ],
    ]);
    $r->assertRedirect();

    $ordre = Ordre::latest()->first();
    expect($ordre->facture_id)->toBe($facture->id);
});

it('rejette le rattachement si le sens ordre ≠ sens facture', function () {
    actingAsSuperAdmin();
    // Facture recette
    $factureRec = Facture::create([
        'numero' => 'FA-REC', 'sens' => 'recette',
        'tiers_type' => 'client', 'tiers_source' => 'organisation',
        'tiers_id' => ContactOrganisation::create([
            'nom' => 'C', 'raison_sociale' => 'CL SA', 'type' => 'client',
            'actif' => true, 'created_by' => $this->auteur->id,
        ])->id,
        'date_emission' => '2027-01-01', 'objet' => 'Vente',
        'montant_ht' => 200_000, 'taux_tva' => 0, 'montant_tva' => 0, 'montant_ttc' => 200_000,
        'statut' => 1, 'created_by' => $this->auteur->id,
    ]);

    // Ordre dépense qui tente de se rattacher à une facture recette → REJET
    $t = Titre::create(['imputation' => 'T-Z', 'libelle' => 'Z', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $ligne = Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 82002, 'libelle' => 'L', 'nature' => 'depense', 'id_user' => $this->auteur->id]);
    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $ex = Exercice::create(['exercice' => 'EX-Z-' . random_int(1, 999), 'libelle' => 'Z', 'statut' => Exercice::STATUT_EN_EXECUTION, 'id_user' => $this->auteur->id]);
    $bl = \App\Models\BudgetLigne::create([
        'id_budgetligne' => 'BL-Z', 'id_exercicebudgetaire' => $ex->id,
        'id_codeanalytique' => $ligne->id, 'id_famillecodeanalytique' => $t->id,
        'dotation_etat' => 1_000_000, 'isvalide' => 1, 'id_user' => $this->auteur->id,
    ]);
    $modele = OrdreModele::where('code', 'ordonnance_paiement')->first(); // sens=depense

    $r = $this->post(route('finance.ordres.store'), [
        'modele_id' => $modele->id, 'exercice_id' => $ex->id,
        'budget_ligne_id' => $bl->id,
        'facture_id' => $factureRec->id, // FACTURE RECETTE ≠ ORDRE DÉPENSE
        'donnees' => [
            'nature_depense' => 'X', 'imputation_budget' => '0',
            'ref_date' => '2027-01-15', 'montant' => 100, 'mode_reglement' => 'virement',
        ],
    ]);
    $r->assertSessionHasErrors('facture_id');
});

it('montant_ordres_executes et solde_restant sont corrects', function () {
    $facture = Facture::create([
        'numero' => 'FA-CALC', 'sens' => 'depense',
        'tiers_type' => 'fournisseur', 'tiers_source' => 'organisation', 'tiers_id' => $this->frn->id,
        'date_emission' => '2027-01-01', 'objet' => 'X',
        'montant_ht' => 500_000, 'taux_tva' => 0, 'montant_tva' => 0, 'montant_ttc' => 500_000,
        'statut' => 1, 'created_by' => $this->auteur->id,
    ]);
    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $ex = Exercice::create(['exercice' => 'EX-CALC', 'libelle' => 'X', 'statut' => Exercice::STATUT_EN_EXECUTION, 'id_user' => $this->auteur->id]);
    $modele = OrdreModele::where('code', 'ordonnance_paiement')->first();

    // Un ordre exécuté (compte 300k) + un ordre brouillon (200k) — seul l'exécuté doit compter
    Ordre::create([
        'modele_id' => $modele->id, 'numero_ordre' => 'O-EXE', 'exercice_id' => $ex->id,
        'facture_id' => $facture->id, 'sens' => 'depense', 'montant' => 300_000,
        'statut' => Ordre::STATUT_EXECUTE, 'created_by' => $this->auteur->id,
    ]);
    Ordre::create([
        'modele_id' => $modele->id, 'numero_ordre' => 'O-BR', 'exercice_id' => $ex->id,
        'facture_id' => $facture->id, 'sens' => 'depense', 'montant' => 200_000,
        'statut' => Ordre::STATUT_BROUILLON, 'created_by' => $this->auteur->id,
    ]);

    expect($facture->fresh()->montant_ordres_executes)->toBe(300_000.0);
    expect($facture->fresh()->solde_restant)->toBe(200_000.0);
});
