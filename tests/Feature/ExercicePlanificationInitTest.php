<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Ligne;
use App\Models\Titre;

beforeEach(function () {
    seedRoles();
    $this->auteur = \App\Models\User::factory()->create();
    // Titre + 3 lignes du référentiel pour rendre le test indépendant du seeder
    $t = Titre::create(['imputation' => 'T-INIT', 'libelle' => 'Titre init test', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $base = ['nature' => 'depense', 'id_user' => $this->auteur->id];
    Ligne::create($base + ['id_titre' => $t->id, 'id_codeanalytique' => 91001, 'libelle' => 'Ligne A']);
    Ligne::create($base + ['id_titre' => $t->id, 'id_codeanalytique' => 91002, 'libelle' => 'Ligne B']);
    Ligne::create($base + ['id_titre' => $t->id, 'id_codeanalytique' => 91003, 'libelle' => 'Ligne C']);
});

it('crée un exercice et matérialise toutes les lignes du référentiel à zéro', function () {
    actingAsSuperAdmin();
    $countLignesRef = Ligne::whereNotNull('id_codeanalytique')->count();

    $r = $this->post('/finance/exercices', [
        'exercice' => 'EX-INIT-1', 'libelle' => 'Exercice init',
        'datedebut' => '2027-01-01', 'datefin' => '2027-12-31',
    ]);
    $r->assertRedirect();

    $ex = Exercice::where('exercice', 'EX-INIT-1')->firstOrFail();
    expect($ex->budgetLignes()->count())->toBe($countLignesRef);

    // Toutes à zéro
    $ex->budgetLignes->each(function ($bl) {
        expect((float) $bl->dotation_etat)->toBe(0.0);
        expect((float) $bl->fonds_propres)->toBe(0.0);
        expect((float) $bl->reports_budgetaire)->toBe(0.0);
        expect((float) $bl->reports_tresorerie)->toBe(0.0);
    });
});

it('initialiserPlanification est idempotente', function () {
    $ex = Exercice::create([
        'exercice' => 'EX-IDEM', 'libelle' => 'Idempotent',
        'statut' => 1, 'validation_statut' => 0,
        'id_user' => $this->auteur->id,
    ]);
    $first  = $ex->initialiserPlanification($this->auteur->id);
    $second = $ex->initialiserPlanification($this->auteur->id);
    expect($first)->toBeGreaterThan(0);
    expect($second)->toBe(0);
});

it('synchroniserReferentiel ajoute uniquement les nouvelles lignes', function () {
    actingAsSuperAdmin();
    $ex = Exercice::create([
        'exercice' => 'EX-SYNC', 'libelle' => 'Sync',
        'statut' => 1, 'validation_statut' => 0, 'id_user' => $this->auteur->id,
    ]);
    $ex->initialiserPlanification($this->auteur->id);
    $avant = $ex->budgetLignes()->count();

    // Nouvelle ligne ajoutée au référentiel après création de l'exercice
    $titre = Titre::first();
    Ligne::create(['id_titre' => $titre->id, 'id_codeanalytique' => 99999, 'libelle' => 'Nouvelle', 'nature' => 'depense', 'id_user' => $this->auteur->id]);

    $this->post(route('finance.exercices.planification.sync-referentiel', $ex))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($ex->budgetLignes()->count())->toBe($avant + 1);
});

it('planificationSave ne supprime plus une ligne dont le total tombe à zéro', function () {
    actingAsSuperAdmin();
    $ex = Exercice::create([
        'exercice' => 'EX-KEEP', 'libelle' => 'Keep zero',
        'statut' => 1, 'validation_statut' => 0, 'id_user' => $this->auteur->id,
    ]);
    $ex->initialiserPlanification($this->auteur->id);
    // Convention métier : `id_codeanalytique` dans budget_lignes = FK Ligne::id
    $lignePk = Ligne::first()->id;

    // On enregistre avec 0 partout : la ligne doit rester matérialisée
    $r = $this->post(route('finance.exercices.planification.save', $ex), [
        'ref' => [
            $lignePk => ['dotation_etat' => 0, 'fonds_propres' => 0, 'reports_budgetaire' => 0, 'reports_tresorerie' => 0],
        ],
    ]);
    $r->assertRedirect();

    expect(BudgetLigne::where('id_exercicebudgetaire', $ex->id)
        ->where('id_codeanalytique', $lignePk)->exists())->toBeTrue();
});

it('un exercice sans lignes budgétaires ne matérialise rien à zéro déjà (référentiel vide)', function () {
    actingAsSuperAdmin();
    // Vider le référentiel test-local
    Ligne::query()->delete();
    Titre::query()->delete();

    $r = $this->post('/finance/exercices', [
        'exercice' => 'EX-EMPTY', 'libelle' => 'Vide',
    ]);
    $r->assertRedirect();
    $ex = Exercice::where('exercice', 'EX-EMPTY')->first();
    expect($ex->budgetLignes()->count())->toBe(0);
});
