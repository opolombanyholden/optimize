<?php

use App\Models\Employee;
use App\Models\Intranet\Evaluation;
use App\Models\Intranet\Kpi;
use App\Models\Intranet\KpiValeur;
use App\Models\Intranet\Objectif;
use App\Models\Intranet\PlanAction;
use App\Models\User;
use App\Services\Objectif\AlertesService;
use App\Services\Objectif\CascadeEvaluationService;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    seedRoles();
});

function makeUserActif(array $attrs = []): User
{
    $u = User::create(array_merge([
        'name'      => 'U',
        'prenoms'   => 'T',
        'email'     => 'u' . random_int(1000, 9999) . '@test.local',
        'password'  => Hash::make('x'),
        'contact'   => '0',
        'matricule' => 'U' . random_int(100, 999),
        'statut'    => 1,
    ], $attrs));
    $u->assignRole('user');
    return $u;
}

function makeKpi(array $attrs = []): Kpi
{
    if (!isset($attrs['created_by'])) {
        $u = User::first() ?? makeUserActif(['matricule' => 'KPI-' . random_int(1000, 9999)]);
        $attrs['created_by'] = $u->id;
    }
    return Kpi::create(array_merge([
        'titre' => 'Test KPI',
    ], $attrs));
}

function makeObjectif(array $attrs = []): Objectif
{
    // Crée à la demande un User pour created_by (NOT NULL en DB)
    if (!isset($attrs['created_by'])) {
        $u = User::first() ?? makeUserActif(['matricule' => 'CRT-' . random_int(1000, 9999)]);
        $attrs['created_by'] = $u->id;
    }
    return Objectif::create(array_merge([
        'titre'        => 'Test objectif',
        'portee'       => 'organisation',
        'statut'       => 'actif',
        'date_debut'   => now()->subMonth(),
        'date_fin'     => now()->addMonth(),
    ], $attrs));
}

// ─── Phase A : Permissions ────────────────────────────────

it('user sans permission ne peut pas créer un objectif (403)', function () {
    actingAsUser();
    $this->post('/objectifs/objectifs', [
        'titre'  => 'Tentative',
        'portee' => 'organisation',
        'statut' => 'actif',
    ])->assertForbidden();
});

it('user peut consulter le dashboard objectifs', function () {
    actingAsUser();
    $this->get('/objectifs')->assertOk();
});

it('super-admin peut créer un objectif', function () {
    actingAsSuperAdmin();
    $this->post('/objectifs/objectifs', [
        'titre'  => 'Objectif test admin',
        'portee' => 'organisation',
        'statut' => 'actif',
    ])->assertRedirect();
    expect(Objectif::where('titre', 'Objectif test admin')->exists())->toBeTrue();
});

// ─── Phase B.1 : Show PlanAction ──────────────────────────

it('affiche la vue show plan d\'action', function () {
    actingAsSuperAdmin();
    $obj = makeObjectif(['titre' => 'Objectif parent plan']);
    $plan = PlanAction::create([
        'titre'         => 'Plan show test',
        'objectif_id'   => $obj->id,
        'statut'        => 'en_cours',
        'priorite'      => 'haute',
        'avancement'    => 40,
        'date_echeance' => now()->addDays(15),
        'created_by'    => User::first()->id,
    ]);
    $this->get("/objectifs/plans-action/{$plan->id}")
        ->assertOk()
        ->assertSee('Plan show test')
        ->assertSee('Objectif parent plan');
});

// ─── Phase C : Filtres ────────────────────────────────────

it('filtre les objectifs par statut (effet sur la collection $objectifs)', function () {
    actingAsSuperAdmin();
    makeObjectif(['titre' => 'Obj actif',   'statut' => 'actif']);
    makeObjectif(['titre' => 'Obj atteint', 'statut' => 'atteint']);

    $resp = $this->get('/objectifs/objectifs?statut=atteint');
    $resp->assertOk();
    $objs = $resp->viewData('objectifs');
    expect($objs->pluck('titre')->all())->not->toContain('Obj actif');
    expect($objs->pluck('titre')->all())->toContain('Obj atteint');
});

it('filtre les objectifs par recherche texte', function () {
    actingAsSuperAdmin();
    makeObjectif(['titre' => 'Croissance Q1',   'description' => 'Augmenter le CA']);
    makeObjectif(['titre' => 'Réduction coûts', 'description' => 'Optimiser dépenses']);

    $resp = $this->get('/objectifs/objectifs?q=Croissance');
    $objs = $resp->viewData('objectifs');
    expect($objs->pluck('titre')->all())->toContain('Croissance Q1');
    expect($objs->pluck('titre')->all())->not->toContain('Réduction coûts');
});

it('filtre les objectifs en retard uniquement', function () {
    actingAsSuperAdmin();
    makeObjectif(['titre' => 'Filt-Retard',     'statut' => 'actif', 'date_fin' => now()->subWeek()]);
    makeObjectif(['titre' => 'Filt-DansTemps',  'statut' => 'actif', 'date_fin' => now()->addMonth()]);

    $resp = $this->get('/objectifs/objectifs?en_retard=1');
    $objs = $resp->viewData('objectifs');
    expect($objs->pluck('titre')->all())->toContain('Filt-Retard');
    expect($objs->pluck('titre')->all())->not->toContain('Filt-DansTemps');
});

// ─── Phase D.1 : Alertes ──────────────────────────────────

it('AlertesService détecte les objectifs en retard', function () {
    makeObjectif(['titre' => 'Retard 1', 'statut' => 'actif', 'date_fin' => now()->subWeek()]);
    makeObjectif(['titre' => 'Dans temps', 'statut' => 'actif', 'date_fin' => now()->addMonth()]);
    makeObjectif(['titre' => 'Déjà atteint en retard', 'statut' => 'atteint', 'date_fin' => now()->subWeek()]);

    $alertes = (new AlertesService())->objectifsEnRetard();
    expect($alertes->pluck('titre')->all())->toBe(['Retard 1']);
});

it('AlertesService détecte les KPI en zone rouge', function () {
    makeKpi(['titre' => 'KPI rouge', 'valeur_cible' => 100, 'valeur_actuelle' => 30]);
    makeKpi(['titre' => 'KPI vert',  'valeur_cible' => 100, 'valeur_actuelle' => 95]);

    $alertes = (new AlertesService())->kpiEnZoneRouge();
    expect($alertes->pluck('titre')->all())->toBe(['KPI rouge']);
});

it('AlertesService compteurs synthétiques', function () {
    makeObjectif(['titre' => 'R', 'statut' => 'actif', 'date_fin' => now()->subDay()]);
    makeKpi(['titre' => 'Rouge', 'valeur_cible' => 100, 'valeur_actuelle' => 10]);

    $compteurs = (new AlertesService())->compteurs();
    expect($compteurs['total'])->toBeGreaterThan(0);
    expect($compteurs['critiques'])->toBeGreaterThanOrEqual(2); // 1 obj retard + 1 KPI rouge
});

// ─── Phase D.2 : Cascade évaluation ───────────────────────

it('cascade ne crée PAS d\'évaluation pour un objectif organisationnel', function () {
    $obj = makeObjectif(['portee' => 'organisation', 'statut' => 'actif']);
    $obj->update(['statut' => 'atteint']);

    expect(Evaluation::where('objectif_id', $obj->id)->count())->toBe(0);
});

it('cascade ne crée PAS d\'évaluation si pas de responsable', function () {
    $obj = makeObjectif(['portee' => 'individuel', 'responsable_id' => null, 'statut' => 'actif']);
    $obj->update(['statut' => 'atteint']);

    expect(Evaluation::where('objectif_id', $obj->id)->count())->toBe(0);
});

it('cascade crée une évaluation pré-remplie quand objectif individuel atteint', function () {
    $sub = makeUserActif(['name' => 'Sub', 'matricule' => 'CSC-SUB']);
    $manager = makeUserActif(['name' => 'Manager', 'matricule' => 'CSC-MGR']);

    $empMgr = Employee::create([
        'noms' => 'M', 'prenoms' => 'X', 'matricule' => 'CSC-MGR-' . random_int(100,999),
        'email' => $manager->email, 'user_id' => $manager->id,
        'salaire_base' => 500000, 'type_contrat' => 'CDI',
        'date_embauche' => now()->subYear(), 'statut' => 1,
    ]);
    Employee::create([
        'noms' => 'S', 'prenoms' => 'Y', 'matricule' => 'CSC-SUB-' . random_int(100,999),
        'email' => $sub->email, 'user_id' => $sub->id,
        'superieur_hierarchique' => $empMgr->id,
        'salaire_base' => 400000, 'type_contrat' => 'CDI',
        'date_embauche' => now()->subYear(), 'statut' => 1,
    ]);

    $obj = makeObjectif([
        'portee'         => 'individuel',
        'responsable_id' => $sub->id,
        'created_by'     => $manager->id,
        'statut'         => 'actif',
    ]);
    $obj->update(['statut' => 'atteint']);

    $eval = Evaluation::where('objectif_id', $obj->id)->first();
    expect($eval)->not->toBeNull();
    expect($eval->user_id)->toBe($sub->id);
    expect($eval->evaluateur_id)->toBe($manager->id);
    expect($eval->statut)->toBe('brouillon');
    expect($eval->score)->toBeGreaterThan(0);
});

it('cascade ne dédouble pas l\'évaluation si appelée plusieurs fois', function () {
    $sub = makeUserActif(['matricule' => 'CSC-D-SUB']);
    $obj = makeObjectif([
        'portee'         => 'individuel',
        'responsable_id' => $sub->id,
        'statut'         => 'actif',
    ]);

    // 1er changement
    $obj->update(['statut' => 'atteint']);
    $count1 = Evaluation::where('objectif_id', $obj->id)->count();

    // 2e changement (par exemple, basculement non_atteint → atteint)
    $obj->update(['statut' => 'non_atteint']);
    $obj->update(['statut' => 'atteint']);

    expect(Evaluation::where('objectif_id', $obj->id)->count())->toBe($count1);
});
