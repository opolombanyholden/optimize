<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Ligne;
use App\Models\OperationFinanciere;
use App\Models\RubriqueOperation;
use App\Models\Titre;
use App\Services\Finance\OperationFinanciereService;

beforeEach(function () {
    seedRoles();
});

function makeTitre(): Titre
{
    return Titre::create([
        'imputation' => 'IMP-' . random_int(100, 999),
        'libelle'    => 'Titre test',
        'type_ligne' => 'depense',
        'id_user'    => auth()->id() ?? 1,
    ]);
}

function makeLigne(?Titre $t = null): Ligne
{
    $t ??= makeTitre();
    return Ligne::create([
        'id_titre'                => $t->id,
        'id_codeanalytique'       => random_int(1000, 9999),
        'libelle'                 => 'Ligne test',
        'nature'                  => 'fonctionnement',
        'id_user'                 => auth()->id() ?? 1,
    ]);
}

function makeRubrique(?Ligne $l = null, string $sens = 'mixte'): RubriqueOperation
{
    $l ??= makeLigne();
    return RubriqueOperation::create([
        'code'      => 'R-' . random_int(1000, 9999),
        'libelle'   => 'Rubrique test',
        'ligne_id'  => $l->id,
        'sens'      => $sens,
        'statut'    => 1,
    ]);
}

function makeBudgetLigneRef(Ligne $l): BudgetLigne
{
    $ex = Exercice::where('statut', 2)->first() ?? Exercice::create([
        'exercice' => '2026-R', 'libelle' => 'Ref Test',
        'statut' => 2, 'datedebut' => '2026-01-01', 'datefin' => '2026-12-31',
    ]);
    return BudgetLigne::create([
        'id_budgetligne'         => 'BL-' . random_int(1000, 9999),
        'id_exercicebudgetaire'  => $ex->id,
        'id_codeanalytique'      => $l->id,
        'dotation_etat'          => 1_000_000,
        'isvalide'               => 1,
        'id_user'                => auth()->id() ?? 1,
    ]);
}

// ─── TITRES ────────────────────────────────────────────

it('liste, crée et modifie un titre', function () {
    actingAsSuperAdmin();
    $this->get('/finance/referentiels/titres')->assertOk();
    $this->post('/finance/referentiels/titres', [
        'imputation' => 'TST', 'libelle' => 'Test', 'type_ligne' => 'depense',
    ])->assertRedirect();
    $t = Titre::where('imputation', 'TST')->first();
    expect($t)->not->toBeNull();
    $this->put("/finance/referentiels/titres/{$t->id}", [
        'imputation' => 'TST', 'libelle' => 'Test modifié', 'type_ligne' => 'mixte',
    ])->assertRedirect();
    expect($t->fresh()->libelle)->toBe('Test modifié');
});

it('refuse de supprimer un titre ayant des lignes', function () {
    actingAsSuperAdmin();
    $t = makeTitre();
    makeLigne($t);
    $this->delete("/finance/referentiels/titres/{$t->id}")
        ->assertRedirect()
        ->assertSessionHas('error');
    expect(Titre::find($t->id))->not->toBeNull();
});

// ─── LIGNES ────────────────────────────────────────────

it('crée une ligne rattachée à un titre', function () {
    actingAsSuperAdmin();
    $t = makeTitre();
    $this->post('/finance/referentiels/lignes', [
        'id_titre'          => $t->id,
        'id_codeanalytique' => 12345,
        'libelle'           => 'Ligne créée',
    ])->assertRedirect();
    expect(Ligne::where('id_codeanalytique', 12345)->exists())->toBeTrue();
});

// ─── RUBRIQUES ─────────────────────────────────────────

it('crée une rubrique liée à une ligne', function () {
    actingAsSuperAdmin();
    $l = makeLigne();
    $this->post('/finance/referentiels/rubriques', [
        'code'     => 'R-CREE',
        'libelle'  => 'Rubrique créée',
        'ligne_id' => $l->id,
        'sens'     => 'depense',
    ])->assertRedirect();
    expect(RubriqueOperation::where('code', 'R-CREE')->exists())->toBeTrue();
});

it('exige un code unique pour les rubriques', function () {
    actingAsSuperAdmin();
    $l = makeLigne();
    RubriqueOperation::create(['code' => 'DUP', 'libelle' => 'A', 'ligne_id' => $l->id, 'sens' => 'mixte', 'statut' => 1]);
    $this->post('/finance/referentiels/rubriques', [
        'code' => 'DUP', 'libelle' => 'B', 'ligne_id' => $l->id, 'sens' => 'mixte',
    ])->assertSessionHasErrors('code');
});

// ─── ENDPOINT AJAX — Rubriques par budget ligne ─────────

it('l\'endpoint AJAX retourne les rubriques de la ligne liée à la BudgetLigne', function () {
    actingAsSuperAdmin();
    $l = makeLigne();
    $bl = makeBudgetLigneRef($l);
    // 2 rubriques sur cette ligne, 1 autre sur une ligne différente
    makeRubrique($l, 'depense');
    makeRubrique($l, 'mixte');
    $autre = makeLigne();
    makeRubrique($autre, 'depense'); // ne doit PAS apparaître

    $resp = $this->getJson("/finance/referentiels/rubriques/par-budget-ligne/{$bl->id}?sens=depense");
    $resp->assertOk();
    expect(count($resp->json('rubriques')))->toBe(2);
});

it('l\'endpoint AJAX retourne une liste vide si la BudgetLigne n\'a pas de code analytique', function () {
    actingAsSuperAdmin();
    $ex = Exercice::create([
        'exercice' => '2026-NoCA', 'libelle' => 'NoCA',
        'statut' => 2, 'datedebut' => '2026-01-01', 'datefin' => '2026-12-31',
    ]);
    $bl = BudgetLigne::create([
        'id_budgetligne' => 'BL-EMPTY',
        'id_exercicebudgetaire' => $ex->id,
        'isvalide' => 1,
        'id_user' => auth()->id(),
    ]);
    $resp = $this->getJson("/finance/referentiels/rubriques/par-budget-ligne/{$bl->id}");
    $resp->assertOk();
    expect(count($resp->json('rubriques')))->toBe(0);
});

it('l\'endpoint AJAX filtre par sens (recette n\'inclut pas une rubrique purement dépense)', function () {
    actingAsSuperAdmin();
    $l = makeLigne();
    $bl = makeBudgetLigneRef($l);
    makeRubrique($l, 'depense'); // ne doit PAS apparaître pour ?sens=recette
    makeRubrique($l, 'mixte');   // doit apparaître

    $resp = $this->getJson("/finance/referentiels/rubriques/par-budget-ligne/{$bl->id}?sens=recette");
    expect(count($resp->json('rubriques')))->toBe(1);
});

// ─── DÉTAILS D'OPÉRATION ──────────────────────────────

it('le service syncDetails recalcule le montant total depuis la somme des détails', function () {
    actingAsSuperAdmin();
    $l = makeLigne();
    $bl = makeBudgetLigneRef($l);
    $r1 = makeRubrique($l);
    $r2 = makeRubrique($l);

    $op = OperationFinanciere::create([
        'numero' => 'OD-DET-1', 'type_operation' => 'depense',
        'exercice_id' => $bl->exercice->id, 'budget_ligne_id' => $bl->id,
        'date_operation' => now()->toDateString(),
        'objet' => 'Test détails', 'montant' => 0,
        'statut' => 0, 'created_by' => auth()->id(),
    ]);

    $service = new OperationFinanciereService();
    $service->syncDetails($op, [
        ['rubrique_id' => $r1->id, 'libelle' => 'Achat 1', 'quantite' => 2, 'prix_unitaire' => 10_000],
        ['rubrique_id' => $r2->id, 'libelle' => 'Achat 2', 'quantite' => 1, 'prix_unitaire' => 50_000],
    ]);

    $op->refresh();
    expect((float) $op->montant)->toBe(70_000.0); // 2*10K + 1*50K
    expect($op->details()->count())->toBe(2);
});

it('purge les anciens détails au sync', function () {
    actingAsSuperAdmin();
    $l = makeLigne();
    $bl = makeBudgetLigneRef($l);
    $r = makeRubrique($l);
    $op = OperationFinanciere::create([
        'numero' => 'OD-DET-2', 'type_operation' => 'depense',
        'exercice_id' => $bl->exercice->id, 'budget_ligne_id' => $bl->id,
        'date_operation' => now()->toDateString(),
        'objet' => 'Test purge', 'montant' => 1,
        'statut' => 0, 'created_by' => auth()->id(),
    ]);

    $service = new OperationFinanciereService();
    $service->syncDetails($op, [
        ['rubrique_id' => $r->id, 'libelle' => 'L1', 'quantite' => 1, 'prix_unitaire' => 100],
        ['rubrique_id' => $r->id, 'libelle' => 'L2', 'quantite' => 1, 'prix_unitaire' => 200],
    ]);
    expect($op->fresh()->details()->count())->toBe(2);

    // Resync avec une seule ligne → purge des autres
    $service->syncDetails($op, [
        ['rubrique_id' => $r->id, 'libelle' => 'L3 seule', 'quantite' => 1, 'prix_unitaire' => 500],
    ]);
    expect($op->fresh()->details()->count())->toBe(1);
    expect((float) $op->fresh()->montant)->toBe(500.0);
});

it('le sync refuse si l\'opération n\'est plus modifiable', function () {
    actingAsSuperAdmin();
    $l = makeLigne();
    $bl = makeBudgetLigneRef($l);
    $op = OperationFinanciere::create([
        'numero' => 'OD-DET-3', 'type_operation' => 'depense',
        'exercice_id' => $bl->exercice->id, 'budget_ligne_id' => $bl->id,
        'date_operation' => now()->toDateString(),
        'objet' => 'Statut bloqué', 'montant' => 100,
        'statut' => 2, // approuvée
        'created_by' => auth()->id(),
    ]);
    expect(fn() => (new OperationFinanciereService())->syncDetails($op, []))
        ->toThrow(\RuntimeException::class, 'non modifiables');
});
