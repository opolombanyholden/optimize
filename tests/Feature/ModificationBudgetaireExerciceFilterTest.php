<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\ModificationBudgetaire;

beforeEach(function () {
    seedRoles();
    $this->auteur = \App\Models\User::factory()->create();
});

function mkExStatut(int $statut, string $prefixe = 'FILTER'): Exercice
{
    // Assure l'unicité d'un seul exercice en exécution SEULEMENT quand on en crée un
    if ($statut === Exercice::STATUT_EN_EXECUTION) {
        Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)
            ->update(['statut' => Exercice::STATUT_CLOTURE]);
    }
    return Exercice::create([
        'exercice' => $prefixe . '-' . random_int(1000, 9999),
        'libelle'  => 'X ' . $statut,
        'statut'   => $statut,
        'validation_statut' => $statut === Exercice::STATUT_PLANIFICATION ? 0 : 2,
        'id_user'  => test()->auteur->id,
    ]);
}

it('create ne liste que les exercices en planification ou exécution', function () {
    actingAsSuperAdmin();
    // 1 planif + 1 exécution + 1 clôturé + 1 annulé
    $planif = mkExStatut(Exercice::STATUT_PLANIFICATION);
    $ex     = mkExStatut(Exercice::STATUT_EN_EXECUTION);
    $cloture= mkExStatut(Exercice::STATUT_CLOTURE, 'CLOS');
    $annule = mkExStatut(Exercice::STATUT_ANNULE, 'ANN');

    $r = $this->get(route('finance.modifications-budgetaires.create'));
    $r->assertOk();
    $ids = $r->viewData('exercices')->pluck('id')->all();
    expect($ids)->toContain($planif->id, $ex->id);
    expect($ids)->not->toContain($cloture->id);
    expect($ids)->not->toContain($annule->id);
});

it('store refuse un exercice clôturé (validation)', function () {
    actingAsSuperAdmin();
    $cloture = mkExStatut(Exercice::STATUT_CLOTURE, 'STORE-CLOS');
    $planif  = mkExStatut(Exercice::STATUT_PLANIFICATION, 'STORE-PLAN');
    $planif->initialiserPlanification($this->auteur->id);
    // Récupère une BudgetLigne pour éviter d'échouer sur exists ligne
    $bl = BudgetLigne::where('id_exercicebudgetaire', $planif->id)->first();
    if (!$bl) {
        $bl = BudgetLigne::create([
            'id_budgetligne'        => 'BL-STORE-1',
            'id_exercicebudgetaire' => $planif->id,
            'isvalide'              => 0,
            'id_user'               => $this->auteur->id,
        ]);
    }

    $r = $this->post(route('finance.modifications-budgetaires.store'), [
        'exercice_id'                 => $cloture->id,
        'type_modification'           => 'ajout',
        'objetmodification'           => 'Test refus',
        'montant_modification'        => 100_000,
        'budget_ligne_destination_id' => $bl->id,
    ]);
    $r->assertSessionHasErrors('exercice_id');
});

it('store accepte un exercice en planification', function () {
    actingAsSuperAdmin();
    $planif = mkExStatut(Exercice::STATUT_PLANIFICATION, 'ACCEPT');
    $planif->initialiserPlanification($this->auteur->id);
    $bl = BudgetLigne::where('id_exercicebudgetaire', $planif->id)->first();
    if (!$bl) {
        $bl = BudgetLigne::create([
            'id_budgetligne' => 'BL-A-1', 'id_exercicebudgetaire' => $planif->id,
            'isvalide' => 0, 'id_user' => $this->auteur->id,
        ]);
    }

    $r = $this->post(route('finance.modifications-budgetaires.store'), [
        'exercice_id'                 => $planif->id,
        'type_modification'           => 'ajout',
        'objetmodification'           => 'Apport OK',
        'montant_modification'        => 50_000,
        'budget_ligne_destination_id' => $bl->id,
    ]);
    $r->assertRedirect();
    expect(ModificationBudgetaire::where('objetmodification', 'Apport OK')->exists())->toBeTrue();
});

it('la vue create alerte si aucun exercice éligible', function () {
    actingAsSuperAdmin();
    // Tous en clôturé/annulé
    mkExStatut(Exercice::STATUT_CLOTURE, 'ONLY-CLOS');
    mkExStatut(Exercice::STATUT_ANNULE, 'ONLY-ANN');

    $r = $this->get(route('finance.modifications-budgetaires.create'));
    $r->assertOk();
    $r->assertSee('Aucun exercice éligible');
});
