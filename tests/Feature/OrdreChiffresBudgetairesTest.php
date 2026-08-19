<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Finance\Ordre;
use App\Models\Finance\OrdreModele;
use App\Models\Ligne;
use App\Models\Titre;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $this->auteur = \App\Models\User::factory()->create();

    $t = Titre::create(['imputation' => 'T-CB', 'libelle' => 'Chiffres', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $this->ligne = Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 45001, 'libelle' => 'L CB', 'nature' => 'depense', 'id_user' => $this->auteur->id]);

    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $this->exercice = Exercice::create([
        'exercice' => 'EX-CB-' . random_int(100, 999), 'libelle' => 'CB',
        'statut' => Exercice::STATUT_EN_EXECUTION, 'validation_statut' => 2, 'id_user' => $this->auteur->id,
    ]);
    // BL avec dotation_etat=800k, fonds_propres=200k → budget_total=1M ; engagement=300k → solde=700k
    $this->bl = BudgetLigne::create([
        'id_budgetligne' => 'BL-CB', 'id_exercicebudgetaire' => $this->exercice->id,
        'id_codeanalytique' => $this->ligne->id, 'id_famillecodeanalytique' => $t->id,
        'dotation_etat' => 800_000, 'fonds_propres' => 200_000, 'engagement' => 300_000,
        'isvalide' => 1, 'id_user' => $this->auteur->id,
    ]);

    $this->modele = OrdreModele::where('code', 'ordonnance_paiement')->first();
});

// ═════════ API endpoint ═════════

it('endpoint budget-lignes retourne dotation_totale et solde_disponible', function () {
    actingAsSuperAdmin();
    $r = $this->getJson(route('finance.api.exercices.budget-lignes', ['exercice' => $this->exercice->id]));
    $r->assertOk();

    $ligneJson = collect($r->json())->firstWhere('id', $this->bl->id);
    expect($ligneJson)->not->toBeNull();
    expect((float) $ligneJson['dotation_totale'])->toBe(1_000_000.0);
    expect((float) $ligneJson['solde_disponible'])->toBe(700_000.0);
});

// ═════════ Serveur force les 3 champs ═════════

function ordreCB(array $donneesExtra = []): array {
    return [
        'modele_id'       => test()->modele->id,
        'exercice_id'     => test()->exercice->id,
        'budget_ligne_id' => test()->bl->id,
        'donnees'         => array_merge([
            'nature_depense'      => 'Achat CB',
            'imputation_budget'   => '999-fake',
            'beneficiaire_raison' => 'Y',
            'ref_date'            => '2027-01-01',
            'montant'             => 150_000,
            'mode_reglement'      => 'virement',
            'dotation_initiale'   => 999_999_999,  // valeur client bidon, doit être écrasée
            'solde_precedent'     => 999_999_999,
            'nouveau_solde'       => 999_999_999,
        ], $donneesExtra),
    ];
}

it('store écrase dotation_initiale avec le budget_total de la ligne', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), ordreCB())->assertRedirect();

    $ordre = Ordre::latest()->first();
    expect((float) $ordre->donnees_json['dotation_initiale'])->toBe(1_000_000.0);
});

it('store écrase solde_precedent avec le solde_disponible avant opération', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), ordreCB())->assertRedirect();

    $ordre = Ordre::latest()->first();
    expect((float) $ordre->donnees_json['solde_precedent'])->toBe(700_000.0);
});

it('store calcule nouveau_solde = solde_precedent - montant pour une dépense', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), ordreCB(['montant' => 150_000]))->assertRedirect();

    $ordre = Ordre::latest()->first();
    // 700 000 - 150 000 = 550 000
    expect((float) $ordre->donnees_json['nouveau_solde'])->toBe(550_000.0);
});

it('pour une recette : solde_precedent = cumul des recettes précédemment enregistrées', function () {
    actingAsSuperAdmin();
    $modeleRec = OrdreModele::where('code', 'ordre_recette')->first();
    $modeleRec->champs()->create(['code_champ' => 'solde_precedent', 'label_personnalise' => 'SOLDE PRECEDENT', 'type_saisie' => 'number', 'ordre' => 60]);
    $modeleRec->champs()->create(['code_champ' => 'nouveau_solde',   'label_personnalise' => 'NOUVEAU SOLDE',   'type_saisie' => 'number', 'ordre' => 70]);

    // Recette antérieure déjà exécutée : 300 000
    Ordre::create([
        'modele_id' => $modeleRec->id, 'numero_ordre' => 'REC-PREC', 'exercice_id' => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id, 'sens' => 'recette', 'montant' => 300_000,
        'statut' => Ordre::STATUT_EXECUTE, 'created_by' => $this->auteur->id,
    ]);
    // Recette brouillon (NON exécutée) : ne doit pas être comptée
    Ordre::create([
        'modele_id' => $modeleRec->id, 'numero_ordre' => 'REC-BROUILLON', 'exercice_id' => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id, 'sens' => 'recette', 'montant' => 999_999,
        'statut' => Ordre::STATUT_BROUILLON, 'created_by' => $this->auteur->id,
    ]);

    $this->post(route('finance.ordres.store'), [
        'modele_id'       => $modeleRec->id,
        'exercice_id'     => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id,
        'donnees' => [
            'nature_recette' => 'Vente',
            'imputation_budget' => '0',
            'date_emission' => '2027-02-01',
            'montant' => 400_000,
        ],
    ])->assertRedirect();

    $ordre = Ordre::whereNotIn('numero_ordre', ['REC-PREC', 'REC-BROUILLON'])->latest()->first();
    // solde_precedent = 300 000 (uniquement la recette exécutée)
    expect((float) $ordre->donnees_json['solde_precedent'])->toBe(300_000.0);
    // nouveau_solde = 300 000 + 400 000 = 700 000
    expect((float) $ordre->donnees_json['nouveau_solde'])->toBe(700_000.0);
});

it('pour une dépense : solde_precedent reste = solde disponible de la BL', function () {
    actingAsSuperAdmin();
    // Recette exécutée sur la même BL — ne doit pas influencer une dépense
    Ordre::create([
        'modele_id' => $this->modele->id, 'numero_ordre' => 'DEP-INIT', 'exercice_id' => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id, 'sens' => 'recette', 'montant' => 500_000,
        'statut' => Ordre::STATUT_EXECUTE, 'created_by' => $this->auteur->id,
    ]);

    $this->post(route('finance.ordres.store'), ordreCB(['montant' => 100_000]))->assertRedirect();
    $ordre = Ordre::where('numero_ordre', '!=', 'DEP-INIT')->latest()->first();
    // Toujours 700 000 pour une dépense (solde_disponible), pas 500 000
    expect((float) $ordre->donnees_json['solde_precedent'])->toBe(700_000.0);
});

it('endpoint budget-lignes retourne cumul_recettes', function () {
    actingAsSuperAdmin();
    $modeleRec = OrdreModele::where('code', 'ordre_recette')->first();
    Ordre::create([
        'modele_id' => $modeleRec->id, 'numero_ordre' => 'REC-1', 'exercice_id' => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id, 'sens' => 'recette', 'montant' => 250_000,
        'statut' => Ordre::STATUT_EXECUTE, 'created_by' => $this->auteur->id,
    ]);

    $r = $this->getJson(route('finance.api.exercices.budget-lignes', ['exercice' => $this->exercice->id]));
    $ligneJson = collect($r->json())->firstWhere('id', $this->bl->id);
    expect((float) $ligneJson['cumul_recettes'])->toBe(250_000.0);
});

it('nouveau_solde reflète le montant réel envoyé (recalcul si montant change à l\'update)', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), ordreCB(['montant' => 100_000]))->assertRedirect();
    $ordre = Ordre::latest()->first();
    expect((float) $ordre->donnees_json['nouveau_solde'])->toBe(600_000.0);

    // Update avec un nouveau montant
    $this->put(route('finance.ordres.update', $ordre), ordreCB(['montant' => 250_000]))->assertRedirect();
    expect((float) $ordre->fresh()->donnees_json['nouveau_solde'])->toBe(450_000.0);
});
