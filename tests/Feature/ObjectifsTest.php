<?php

use App\Models\Intranet\Evaluation;
use App\Models\Intranet\Kpi;
use App\Models\Intranet\KpiValeur;
use App\Models\Intranet\Objectif;
use App\Models\User;

beforeEach(function () {
    seedRoles();
});

it('renders the objectifs dashboard', function () {
    actingAsSuperAdmin();
    $response = $this->get('/objectifs');
    $response->assertOk();
});

it('renders the objectifs list', function () {
    actingAsSuperAdmin();
    $response = $this->get('/objectifs/objectifs');
    $response->assertOk();
});

it('creates a strategic objectif via POST', function () {
    $admin = actingAsSuperAdmin();

    $response = $this->post('/objectifs/objectifs', [
        'titre'       => 'OKR Test 2026',
        'code'        => 'TEST-001',
        'description' => 'Description',
        'portee'      => 'organisation',
        'statut'      => 'actif',
        'date_debut'  => now()->toDateString(),
        'date_fin'    => now()->addYear()->toDateString(),
    ]);

    $response->assertRedirect();
    $obj = Objectif::where('code', 'TEST-001')->first();
    expect($obj)->not->toBeNull();
    expect($obj->parent_id)->toBeNull(); // Stratégique
});

it('creates a sub-objective with parent', function () {
    $admin = actingAsSuperAdmin();
    $parent = Objectif::create([
        'titre' => 'Parent', 'code' => 'P1', 'portee' => 'organisation',
        'statut' => 'actif', 'created_by' => $admin->id,
    ]);

    $response = $this->post('/objectifs/objectifs', [
        'titre'     => 'Sub OKR',
        'portee'    => 'service',
        'statut'    => 'actif',
        'parent_id' => $parent->id,
    ]);

    $response->assertRedirect();
    $sub = Objectif::where('titre', 'Sub OKR')->first();
    expect($sub)->not->toBeNull();
    expect($sub->parent_id)->toBe($parent->id);
});

it('cascades progression from sub-objectives to parent', function () {
    $admin = User::factory()->create();
    $parent = Objectif::create([
        'titre' => 'Parent', 'portee' => 'organisation', 'statut' => 'actif',
        'created_by' => $admin->id,
    ]);

    Objectif::create([
        'titre' => 'Sub 1', 'portee' => 'service', 'statut' => 'actif',
        'parent_id' => $parent->id, 'ponderation' => 50,
        'created_by' => $admin->id,
    ]);
    Objectif::create([
        'titre' => 'Sub 2', 'portee' => 'service', 'statut' => 'atteint',
        'parent_id' => $parent->id, 'ponderation' => 50,
        'created_by' => $admin->id,
    ]);

    $parent->refresh();
    // Sub 1 = 0%, Sub 2 = 100% (atteint), pondéré 50/50 → 50%
    expect($parent->progression)->toBe(50);
});

it('computes KPI progression', function () {
    $admin = User::factory()->create();
    $kpi = Kpi::create([
        'titre' => 'Test KPI', 'valeur_cible' => 100,
        'valeur_actuelle' => 75, 'created_by' => $admin->id,
    ]);
    expect($kpi->progression)->toEqual(75);
});

it('records a KPI measure and updates current value', function () {
    actingAsSuperAdmin();
    $admin = User::factory()->create();
    $kpi = Kpi::create([
        'titre' => 'KPI', 'valeur_cible' => 100, 'valeur_actuelle' => 0,
        'created_by' => $admin->id,
    ]);

    $response = $this->post("/objectifs/kpi/{$kpi->id}/valeurs", [
        'valeur'      => 42,
        'date_mesure' => now()->toDateString(),
        'commentaire' => 'Mesure test',
    ]);

    $response->assertRedirect();
    expect(KpiValeur::where('kpi_id', $kpi->id)->count())->toBe(1);

    $kpi->refresh();
    expect((float) $kpi->valeur_actuelle)->toBe(42.0);
});

it('renders the kpi index', function () {
    actingAsSuperAdmin();
    $response = $this->get('/objectifs/kpi');
    $response->assertOk();
});

it('creates an evaluation via POST', function () {
    $admin = actingAsSuperAdmin();
    $evalue = User::factory()->create();

    $response = $this->post('/objectifs/evaluations', [
        'titre'           => 'Évaluation Q1',
        'user_id'         => $evalue->id,
        'evaluateur_id'   => $admin->id,
        'score'           => 75,
        'date_evaluation' => now()->toDateString(),
        'statut'          => 'finalise',
        'points_forts'    => 'Très bon travail',
    ]);

    $response->assertRedirect();
    expect(Evaluation::where('user_id', $evalue->id)->count())->toBe(1);
});

it('renders the plans-action index', function () {
    actingAsSuperAdmin();
    $response = $this->get('/objectifs/plans-action');
    $response->assertOk();
});

it('creates a plan d\'action via POST', function () {
    $admin = actingAsSuperAdmin();
    $obj = Objectif::create([
        'titre' => 'Test', 'portee' => 'organisation', 'statut' => 'actif',
        'created_by' => $admin->id,
    ]);

    $response = $this->post('/objectifs/plans-action', [
        'titre'         => 'Action de test',
        'objectif_id'   => $obj->id,
        'statut'        => 'planifie',
        'priorite'      => 'haute',
        'avancement'    => 0,
        'date_echeance' => now()->addMonth()->toDateString(),
    ]);

    $response->assertRedirect();
    expect(\App\Models\Intranet\PlanAction::where('titre', 'Action de test')->count())->toBe(1);
});

it('auto-sets date_realisation when status becomes realise', function () {
    $admin = actingAsSuperAdmin();
    $obj = Objectif::create([
        'titre' => 'O', 'portee' => 'organisation', 'statut' => 'actif',
        'created_by' => $admin->id,
    ]);
    $plan = \App\Models\Intranet\PlanAction::create([
        'titre' => 'P', 'objectif_id' => $obj->id, 'statut' => 'en_cours',
        'priorite' => 'normale', 'avancement' => 50, 'created_by' => $admin->id,
    ]);

    $this->put("/objectifs/plans-action/{$plan->id}", [
        'titre'       => 'P',
        'objectif_id' => $obj->id,
        'statut'      => 'realise',
        'priorite'    => 'normale',
    ]);

    $plan->refresh();
    expect($plan->statut)->toBe('realise');
    expect($plan->date_realisation)->not->toBeNull();
    expect($plan->avancement)->toBe(100);
});

it('rejects evaluation where evaluator is the same as evaluated', function () {
    $admin = actingAsSuperAdmin();

    $response = $this->post('/objectifs/evaluations', [
        'titre'           => 'Auto-évaluation',
        'user_id'         => $admin->id,
        'evaluateur_id'   => $admin->id,  // Identique → invalid
        'date_evaluation' => now()->toDateString(),
        'statut'          => 'brouillon',
    ]);

    $response->assertSessionHasErrors('evaluateur_id');
});
