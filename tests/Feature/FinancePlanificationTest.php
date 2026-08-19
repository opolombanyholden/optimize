<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\User;

beforeEach(function () {
    seedRoles();
});

function exPlanif(int $statut = 1, int $validationStatut = 0, ?string $datefin = null): Exercice
{
    Exercice::where('statut', 2)->update(['statut' => 3]);
    return Exercice::create([
        'exercice'          => 'EX-P-' . random_int(100, 999),
        'libelle'           => 'Test Planif',
        'statut'            => $statut,
        'validation_statut' => $validationStatut,
        'datedebut'         => '2026-01-01',
        'datefin'           => $datefin ?? '2026-12-31',
    ]);
}

function blPlanif(Exercice $ex, float $dotation = 1_000_000): BudgetLigne
{
    return BudgetLigne::create([
        'id_budgetligne'        => 'BL-' . random_int(1000, 9999),
        'id_exercicebudgetaire' => $ex->id,
        'dotation_etat'         => $dotation,
        'isvalide'              => 0,
        'id_user'               => auth()->id() ?? 1,
    ]);
}

// ─── CRÉATION D'EXERCICE ───────────────────────────────

it('crée un exercice et redirige vers la page de planification', function () {
    actingAsSuperAdmin();
    $this->post('/finance/exercices', [
        'exercice' => 'EX-2027', 'libelle' => 'Exercice 2027',
        'datedebut' => '2027-01-01', 'datefin' => '2027-12-31',
        'budgetglobalinitial' => 500_000_000,
    ])->assertRedirectContains('/planification');

    $ex = Exercice::where('libelle', 'Exercice 2027')->first();
    expect($ex->statut)->toBe(1);
    expect((int) $ex->validation_statut)->toBe(0);
});

// ─── PLANIFICATION ─────────────────────────────────────

it('la page de planification s\'affiche pour un exercice en mode planification', function () {
    actingAsSuperAdmin();
    $ex = exPlanif();
    $this->get("/finance/exercices/{$ex->id}/planification")
        ->assertOk()
        ->assertSee('Planification budgétaire');
});

it('on peut créer des lignes libres via la planification (bulk save)', function () {
    actingAsSuperAdmin();
    $ex = exPlanif();
    $this->post("/finance/exercices/{$ex->id}/planification", [
        'libres' => [
            ['id_budgetligne' => 'BL-001', 'commentaire' => 'Salaires',    'dotation_etat' => 200_000_000],
            ['id_budgetligne' => 'BL-002', 'commentaire' => 'Fournitures', 'dotation_etat' => 10_000_000],
        ],
    ])->assertRedirect();

    expect(BudgetLigne::where('id_exercicebudgetaire', $ex->id)->count())->toBe(2);
});

it('on peut créer une ligne du référentiel via la grille (clé = ligne.id)', function () {
    actingAsSuperAdmin();
    $ex = exPlanif();
    // Crée une ligne référentiel
    $titre = \App\Models\Titre::create(['imputation' => 'T-G', 'libelle' => 'Test grille', 'type_ligne' => 'depense', 'id_user' => auth()->id()]);
    $ligne = \App\Models\Ligne::create([
        'id_titre' => $titre->id, 'id_codeanalytique' => 9001,
        'libelle' => 'Code 9001', 'nature' => 'fonctionnement', 'id_user' => auth()->id(),
    ]);

    $this->post("/finance/exercices/{$ex->id}/planification", [
        'ref' => [
            $ligne->id => ['dotation_etat' => 100_000, 'commentaire' => 'Test'],
        ],
    ])->assertRedirect();

    // BudgetLigne créée avec id_codeanalytique = $ligne->id
    $bl = BudgetLigne::where('id_exercicebudgetaire', $ex->id)->first();
    expect($bl)->not->toBeNull();
    expect((int) $bl->id_codeanalytique)->toBe($ligne->id);
    expect((float) $bl->dotation_etat)->toBe(100_000.0);
});

it('on peut modifier une ligne libre existante via la planification', function () {
    actingAsSuperAdmin();
    $ex = exPlanif();
    $bl = blPlanif($ex, 100_000);

    $this->post("/finance/exercices/{$ex->id}/planification", [
        'libres' => [
            ['id' => $bl->id, 'commentaire' => 'Modifié', 'dotation_etat' => 500_000, 'id_budgetligne' => $bl->id_budgetligne],
        ],
    ])->assertRedirect();

    expect((float) $bl->fresh()->dotation_etat)->toBe(500_000.0);
});

it('on peut supprimer une ligne libre sans engagement via la planification', function () {
    actingAsSuperAdmin();
    $ex = exPlanif();
    $bl = blPlanif($ex);

    $this->post("/finance/exercices/{$ex->id}/planification", [
        'libres' => [
            ['id' => $bl->id, '_delete' => '1'],
        ],
    ])->assertRedirect();

    expect(BudgetLigne::find($bl->id))->toBeNull();
});

it('une ligne ref reste matérialisée même si le total tombe à 0', function () {
    // Les lignes du référentiel sont matérialisées à la création de l'exercice
    // et restent visibles en permanence, même à 0. Elles ne sont plus supprimées
    // par un save partiel (règle métier 2026-07-09).
    actingAsSuperAdmin();
    $ex = exPlanif();
    $titre = \App\Models\Titre::create(['imputation' => 'T-Z', 'libelle' => 'Zero', 'type_ligne' => 'depense', 'id_user' => auth()->id()]);
    $ligne = \App\Models\Ligne::create([
        'id_titre' => $titre->id, 'id_codeanalytique' => 9002,
        'libelle' => 'Code 9002', 'nature' => 'fonctionnement', 'id_user' => auth()->id(),
    ]);
    $bl = BudgetLigne::create([
        'id_budgetligne'        => 'BLZ',
        'id_exercicebudgetaire' => $ex->id,
        'id_codeanalytique'     => $ligne->id,
        'dotation_etat'         => 100_000,
        'isvalide'              => 0,
        'id_user'               => auth()->id(),
    ]);

    $this->post("/finance/exercices/{$ex->id}/planification", [
        'ref' => [
            $ligne->id => ['dotation_etat' => 0, 'fonds_propres' => 0],
        ],
    ])->assertRedirect();

    $bl->refresh();
    expect($bl)->not->toBeNull();
    expect((float) $bl->dotation_etat)->toBe(0.0);
});

it('la planification est verrouillée après soumission (peut_planifier=false)', function () {
    $ex = exPlanif(1, 1); // soumis
    expect($ex->peut_planifier)->toBeFalse();
    expect($ex->peut_soumettre)->toBeFalse();
    expect($ex->peut_valider)->toBeTrue();
});

// ─── WORKFLOW SOUMISSION/VALIDATION ────────────────────

it('soumet la planification (statut validation 0 → 1)', function () {
    actingAsSuperAdmin();
    $ex = exPlanif();
    blPlanif($ex); // au moins une ligne

    $this->post("/finance/exercices/{$ex->id}/soumettre")->assertRedirect();
    expect((int) $ex->fresh()->validation_statut)->toBe(1);
    expect($ex->fresh()->soumis_par)->not->toBeNull();
});

it('refuse de soumettre sans aucune ligne budgétaire', function () {
    actingAsSuperAdmin();
    $ex = exPlanif();
    $this->post("/finance/exercices/{$ex->id}/soumettre")
        ->assertRedirect()
        ->assertSessionHas('error');
    expect((int) $ex->fresh()->validation_statut)->toBe(0);
});

it('top management valide → exercice passe en EXÉCUTION (statut 1→2 + validation 2)', function () {
    actingAsSuperAdmin();
    $ex = exPlanif(1, 1); // soumis
    blPlanif($ex);

    $this->post("/finance/exercices/{$ex->id}/valider")->assertRedirect();
    $ex->refresh();
    expect($ex->statut)->toBe(2);
    expect((int) $ex->validation_statut)->toBe(2);

    // Toutes les lignes passent en isvalide=1
    expect(BudgetLigne::where('id_exercicebudgetaire', $ex->id)->where('isvalide', 1)->count())
        ->toBe($ex->budgetLignes()->count());
});

it('refuse de valider si un autre exercice est déjà en exécution', function () {
    actingAsSuperAdmin();
    // L'ordre est important : exPlanif clôt tous les exercices en cours, donc on crée l'exercice
    // soumis EN PREMIER, puis l'exercice concurrent en exécution APRÈS.
    $ex = exPlanif(1, 1);
    blPlanif($ex);
    Exercice::create([
        'exercice' => 'EX-EC', 'libelle' => 'Déjà en cours',
        'statut' => 2, 'validation_statut' => 2,
        'datedebut' => '2026-01-01', 'datefin' => '2026-12-31',
    ]);

    $this->post("/finance/exercices/{$ex->id}/valider")
        ->assertRedirect()
        ->assertSessionHas('error');
    expect($ex->fresh()->statut)->toBe(1);
});

it('top management rejette avec motif : retour à planification + motif stocké', function () {
    actingAsSuperAdmin();
    $ex = exPlanif(1, 1);
    blPlanif($ex);

    $this->post("/finance/exercices/{$ex->id}/rejeter", [
        'motif_rejet' => 'Dotations sous-évaluées sur le titre Rémunérations',
    ])->assertRedirect();

    $ex->refresh();
    expect((int) $ex->validation_statut)->toBe(0);
    expect($ex->motif_rejet)->toBe('Dotations sous-évaluées sur le titre Rémunérations');
});

// ─── VERROUILLAGE EN EXÉCUTION ─────────────────────────

it('en exécution, modification de ligne budgétaire refusée', function () {
    actingAsSuperAdmin();
    $ex = exPlanif(2, 2); // exécution
    $bl = blPlanif($ex);

    $this->put("/finance/budgets/{$bl->id}", ['budgetligne' => 999_999])
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('en exécution, création de nouvelle ligne refusée', function () {
    actingAsSuperAdmin();
    $ex = exPlanif(2, 2);

    $this->post('/finance/budgets', [
        'id_budgetligne'        => 'NEW-99',
        'id_exercicebudgetaire' => $ex->id,
        'budgetligne'           => 100_000,
    ])->assertRedirect()->assertSessionHas('error');
});

// ─── CLÔTURE ───────────────────────────────────────────

it('clôture un exercice en exécution', function () {
    actingAsSuperAdmin();
    $ex = exPlanif(2, 2);
    $this->post("/finance/exercices/{$ex->id}/cloturer")->assertRedirect();
    expect($ex->fresh()->statut)->toBe(3);
    expect($ex->fresh()->cloture_par)->not->toBeNull();
});

it('détecte un exercice en attente de clôture (date fin passée)', function () {
    $ex = exPlanif(2, 2, '2025-12-31'); // date fin passée
    expect($ex->en_attente_cloture)->toBeTrue();
});

it('exercice en cours avec date fin future n\'est PAS en attente de clôture', function () {
    $ex = exPlanif(2, 2, '2099-12-31');
    expect($ex->en_attente_cloture)->toBeFalse();
});

// ─── PERMISSIONS ───────────────────────────────────────

it('un user simple ne peut pas valider une planification', function () {
    $u = User::factory()->create(['statut' => 1]);
    $u->assignRole('user');
    $this->actingAs($u);
    $ex = exPlanif(1, 1);
    $this->post("/finance/exercices/{$ex->id}/valider")->assertForbidden();
});
