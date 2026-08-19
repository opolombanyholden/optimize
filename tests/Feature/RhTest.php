<?php

use App\Models\Absence;
use App\Models\Affilie;
use App\Models\CongeSolde;
use App\Models\Competence;
use App\Models\Depart;
use App\Models\Employee;
use App\Models\EvaluationPerformance;
use App\Models\EvenementCarriere;
use App\Models\Formation;
use App\Models\Mission;
use App\Models\Paie;
use App\Models\Payement;
use App\Models\PayementGlobal;
use App\Models\Planning;
use App\Models\Qualification;
use App\Models\Recrutement;
use App\Models\Rubrique;
use App\Models\Sanction;
use App\Models\TypeEvenementCarriere;
use App\Models\User;
use App\Services\Rh\ExpressionEvaluator;
use App\Services\Rh\PaieCalculator;

beforeEach(function () {
    seedRoles();
});

// HELPERS

function makeEmployee(array $attrs = []): Employee
{
    return Employee::create(array_merge([
        'noms'          => 'Test',
        'prenoms'       => 'Employe',
        'matricule'     => 'EMP-' . random_int(10000, 99999),
        'email'         => 'emp' . random_int(1000, 9999) . '@test.local',
        'salaire_base'  => 500000,
        'type_contrat'  => 'CDI',
        'date_embauche' => now()->subYears(2),
        'statut'        => 1,
    ], $attrs));
}

// VAGUE 1

it('affilies CRUD works', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    $this->get('/rh/affilies')->assertOk();
    $this->get('/rh/affilies/create')->assertOk();
    $resp = $this->post('/rh/affilies', [
        'employee_id'    => $emp->id,
        'liens'          => 'Conjoint',
        'noms'           => 'Dupont',
        'prenoms'        => 'Marie',
        'date_naissance' => '1990-01-01',
    ]);
    $resp->assertRedirect('/rh/affilies');
    expect(Affilie::where('noms', 'Dupont')->exists())->toBeTrue();
});

it('qualifications CRUD works', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    $this->get('/rh/qualifications')->assertOk();
    Qualification::create(['employee_id' => $emp->id, 'label' => 'Master 2 Informatique', 'organisme' => 'UOB', 'statut' => 1]);
    expect(Qualification::count())->toBe(1);
});

it('competences CRUD works', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    $this->get('/rh/competences')->assertOk();
    $this->post('/rh/competences', [
        'employee_id' => $emp->id,
        'label'       => 'PHP / Laravel',
        'niveau'      => 'expert',
    ])->assertRedirect('/rh/competences');
    expect(Competence::where('niveau', 'expert')->exists())->toBeTrue();
});

it('formations CRUD works', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    $f = Formation::create(['employee_id' => $emp->id, 'label' => 'Formation Laravel', 'organisme' => 'Udemy', 'statut' => 0]);
    $this->get('/rh/formations')->assertOk();
    $this->get('/rh/formations/' . $f->id)->assertOk();
});

it('missions CRUD with budget works', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    $this->post('/rh/missions', [
        'employee_id' => $emp->id,
        'label'       => 'Mission Libreville',
        'lieu'        => 'Libreville',
        'debut'       => '2026-05-22',
        'fin'         => '2026-05-25',
        'budget'      => 500000,
    ])->assertRedirect('/rh/missions');
    expect(Mission::where('label', 'Mission Libreville')->exists())->toBeTrue();
});

it('rubriques CRUD with unique code works', function () {
    actingAsSuperAdmin();
    $this->post('/rh/rubriques', [
        'code'        => 'TEST_R1',
        'libelle'     => 'Test Rubrique',
        'type'        => 'gain',
        'base_calcul' => 'fixe',
        'montant_fixe'=> 10000,
    ])->assertRedirect('/rh/rubriques');
    expect(Rubrique::where('code', 'TEST_R1')->exists())->toBeTrue();

    $this->post('/rh/rubriques', [
        'code'        => 'TEST_R1',
        'libelle'     => 'Doublon',
        'type'        => 'gain',
        'base_calcul' => 'fixe',
    ])->assertSessionHasErrors('code');
});

it('evenements carriere CRUD works', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    $type = TypeEvenementCarriere::create(['code' => 'PROMO_TEST', 'libelle' => 'Promotion']);
    $this->post('/rh/evenements-carriere', [
        'employee_id'                => $emp->id,
        'typesevenementscarriere_id' => $type->id,
        'libelle'                    => 'Promotion Lead Dev',
        'date_effet'                 => '2026-06-01',
        'ancien_poste'               => 'Dev Senior',
        'nouveau_poste'              => 'Lead Dev',
    ])->assertRedirect('/rh/evenements-carriere');
    expect(EvenementCarriere::where('libelle', 'Promotion Lead Dev')->exists())->toBeTrue();
});

// VAGUE 2

it('sanctions CRUD works with decideur auto-set', function () {
    $admin = actingAsSuperAdmin();
    $emp = makeEmployee();
    $this->post('/rh/sanctions', [
        'employee_id'       => $emp->id,
        'type'              => 'avertissement',
        'motif'             => 'Retard répété',
        'date_notification' => '2026-05-22',
        'niveau_gravite'    => 'modere',
    ])->assertRedirect('/rh/sanctions');
    $s = Sanction::where('motif', 'Retard répété')->first();
    expect($s)->not->toBeNull();
    expect($s->decidee_par)->toBe($admin->id);
});

it('departs marks employee as parti on statut finalise', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    $this->post('/rh/departs', [
        'employee_id'       => $emp->id,
        'type_depart'       => 'demission',
        'date_notification' => '2026-05-22',
        'date_effet'        => '2026-06-22',
        'statut'            => 2,
    ])->assertRedirect('/rh/departs');
    expect($emp->fresh()->statut)->toBe(3);
});

it('evaluations performance respects status workflow', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    $this->post('/rh/evaluations-performance', [
        'employee_id'     => $emp->id,
        'periode'         => '2026-S1',
        'date_evaluation' => '2026-06-30',
        'note_globale'    => 4,
    ])->assertRedirect('/rh/evaluations-performance');
    $eval = EvaluationPerformance::where('periode', '2026-S1')->first();
    expect($eval->note_globale)->toBe(4);
    expect($eval->statut)->toBe(0);
});

it('conges soldes calcule solde disponible via colonne generated', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    CongeSolde::create([
        'employee_id'       => $emp->id,
        'annee'             => 2026,
        'type_conge'        => 'annuel',
        'droit_annuel'      => 24,
        'report_n_moins_1'  => 5,
        'acquis_periode'    => 0,
        'pris_periode'      => 10,
    ]);
    $solde = CongeSolde::where('employee_id', $emp->id)->first();
    expect((float) $solde->solde_disponible)->toBe(19.0);
});

it('plannings enforces unique employee+date', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    Planning::create(['employee_id' => $emp->id, 'date_jour' => '2026-05-22', 'type_journee' => 'travail', 'heures_prevues' => 8]);
    $this->post('/rh/plannings', [
        'employee_id'  => $emp->id,
        'date_jour'    => '2026-05-22',
        'type_journee' => 'travail',
        'heures_prevues' => 7,
    ])->assertRedirect('/rh/plannings');
    expect(Planning::where('employee_id', $emp->id)->count())->toBe(1);
    expect((float) Planning::where('employee_id', $emp->id)->first()->heures_prevues)->toBe(7.0);
});

// VAGUE 3 — Moteur paie

it('ExpressionEvaluator computes basic arithmetic safely', function () {
    $e = new ExpressionEvaluator();
    expect($e->compute('1+2*3'))->toBe(7.0);
    expect($e->compute('(2+3)*4'))->toBe(20.0);
    expect($e->compute('round(10/3)'))->toBe(3.33);
    expect($e->compute('max(5, 10)'))->toBe(10.0);
    expect($e->compute('min(5, 10)'))->toBe(5.0);
    expect($e->compute('-5 + 10'))->toBe(5.0);
});

it('ExpressionEvaluator rejects malicious input', function () {
    $e = new ExpressionEvaluator();
    expect(fn() => $e->compute('foo("ls")'))->toThrow(\InvalidArgumentException::class);
    expect(fn() => $e->compute('unknown_fn(1)'))->toThrow(\InvalidArgumentException::class);
    expect(fn() => $e->compute('$x = 1'))->toThrow(\InvalidArgumentException::class);
});

it('PaieCalculator produces consistent bulletin with Gabon rubriques', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\RhRubriquesSeeder())->run();
    $emp = makeEmployee(['salaire_base' => 800000]);

    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 800000,
    ]);

    expect($r['bulletin']['salaire_base'])->toBe(800000.0);
    expect($r['bulletin']['brut'])->toBeGreaterThan(800000);
    expect($r['bulletin']['cotisations_salariales'])->toBeGreaterThan(0);
    expect($r['bulletin']['cotisations_patronales'])->toBeGreaterThan(0);
    expect($r['bulletin']['irpp'])->toBeGreaterThan(0);
    expect($r['bulletin']['net_a_payer'])->toBeLessThan($r['bulletin']['brut']);
    expect(count($r['lignes']))->toBeGreaterThan(5);
});

it('PaieCalculator persists bulletin with rubriques pivot', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\RhRubriquesSeeder())->run();
    $emp = makeEmployee(['salaire_base' => 600000]);

    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 600000,
    ]);
    $paie = $calc->persister($r);

    expect($paie)->toBeInstanceOf(Paie::class);
    expect($paie->rubriques()->count())->toBeGreaterThan(0);
    expect((float) $paie->brut)->toBeGreaterThanOrEqual(600000);
});

it('Payement workflow updates paie statut to paye when fully paid', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    $paie = Paie::create([
        'employee_id' => $emp->id,
        'label'       => 'Test paie',
        'debut'       => '2026-05-01',
        'fin'         => '2026-05-31',
        'salaire_base' => 500000,
        'brut'        => 500000,
        'net_a_payer' => 400000,
        'statut'      => 1,
    ]);

    $this->post('/rh/payements', [
        'paie_id'       => $paie->id,
        'employee_id'   => $emp->id,
        'date_payement' => '2026-05-31',
        'montant'       => 400000,
        'mode_payement' => 'virement',
        'banque'        => 'BICIG',
    ])->assertRedirect('/rh/payements');

    expect($paie->fresh()->statut)->toBe(2);
});

it('Payement rejects amount exceeding reste a payer', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    $paie = Paie::create([
        'employee_id' => $emp->id,
        'label'       => 'Test',
        'debut'       => '2026-05-01',
        'fin'         => '2026-05-31',
        'salaire_base'=> 500000,
        'brut'        => 500000,
        'net_a_payer' => 400000,
        'statut'      => 1,
    ]);
    $this->post('/rh/payements', [
        'paie_id'       => $paie->id,
        'employee_id'   => $emp->id,
        'date_payement' => '2026-05-31',
        'montant'       => 500000,
        'mode_payement' => 'especes',
    ])->assertSessionHasErrors('montant');
});

it('PayementGlobal recalcul aggregates correctly', function () {
    actingAsSuperAdmin();
    $emp1 = makeEmployee(['salaire_base' => 500000]);
    $emp2 = makeEmployee(['salaire_base' => 700000]);
    Paie::create(['employee_id' => $emp1->id, 'label' => 'P1', 'debut' => '2026-05-15', 'fin' => '2026-05-31', 'salaire_base' => 500000, 'brut' => 500000, 'net_a_payer' => 400000, 'statut' => 2]);
    Paie::create(['employee_id' => $emp2->id, 'label' => 'P2', 'debut' => '2026-05-15', 'fin' => '2026-05-31', 'salaire_base' => 700000, 'brut' => 700000, 'net_a_payer' => 560000, 'statut' => 1]);

    $this->post('/rh/payements-globals/recalculer', ['annee' => 2026, 'mois' => 5])->assertRedirect();
    $global = PayementGlobal::where('annee', 2026)->where('mois', 5)->first();
    expect($global)->not->toBeNull();
    expect((int) $global->nombre_bulletins)->toBe(2);
    expect((float) $global->masse_salariale_brute)->toBe(1200000.0);
});

// VAGUE 4 — Audit trail & RGPD

it('logs activity when employee is updated', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee(['salaire_base' => 500000]);
    $emp->update(['salaire_base' => 800000]);
    $activity = \Spatie\Activitylog\Models\Activity::where('log_name', 'employee')->latest()->first();
    expect($activity)->not->toBeNull();
    expect($activity->properties->toArray())->toHaveKey('attributes');
});

it('logs activity when paie is created', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    Paie::create([
        'employee_id' => $emp->id,
        'label' => 'Test',
        'debut' => '2026-05-01',
        'fin' => '2026-05-31',
        'salaire_base' => 500000,
        'net_a_payer' => 400000,
        'statut' => 0,
    ]);
    expect(\Spatie\Activitylog\Models\Activity::where('log_name', 'paie')->where('event', 'created')->exists())->toBeTrue();
});

it('exports RGPD data as JSON', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee();
    $resp = $this->get('/rh/employees/' . $emp->id . '/rgpd/export');
    $resp->assertOk();
    $resp->assertHeader('Content-Type', 'application/json');
    expect($resp->json('employee.id'))->toBe($emp->id);
});

it('anonymizes employee via RGPD endpoint', function () {
    actingAsSuperAdmin();
    $emp = makeEmployee(['noms' => 'Sensible', 'email' => 'sensible@x.com']);
    $this->post('/rh/employees/' . $emp->id . '/rgpd/anonymiser', [
        'confirmation' => 'ANONYMISER',
        'motif'        => 'Droit a l\'oubli demande par l\'employe',
    ])->assertRedirect();
    $anon = $emp->fresh();
    expect($anon->noms)->toStartWith('ANON-');
    expect($anon->email)->toBeNull();
    expect($anon->statut)->toBe(3);
});

it('audit log page is accessible', function () {
    actingAsSuperAdmin();
    $this->get('/rh/audit-log')->assertOk();
});

it('rh dashboard loads with kpis', function () {
    actingAsSuperAdmin();
    makeEmployee();
    $this->get('/rh')->assertOk()->assertSee('Employés actifs');
});

// Smoke tests routes index

it('employee accepts valid NIP and rejects malformed NIP', function () {
    actingAsSuperAdmin();
    // Valide
    $resp = $this->post('/rh/employees', [
        'noms'                  => 'TESTNIP',
        'prenoms'               => 'Valide',
        'matricule'             => 'EMP-NIP-OK',
        'date_naissance'        => '1990-12-25',
        'nip'                   => 'A1-2345-19901225',
        'email'                 => 'nipok@test.local',
        'password'              => 'secret12',
        'password_confirmation' => 'secret12',
    ]);
    $resp->assertRedirect('/rh/employees');
    expect(Employee::where('nip', 'A1-2345-19901225')->exists())->toBeTrue();

    // Format invalide
    $this->post('/rh/employees', [
        'noms'                  => 'TESTNIP',
        'prenoms'               => 'Invalide',
        'matricule'             => 'EMP-NIP-KO',
        'nip'                   => 'ABCDEFGHIJKL',
        'email'                 => 'nipko@test.local',
        'password'              => 'secret12',
        'password_confirmation' => 'secret12',
    ])->assertSessionHasErrors('nip');

    // Doublon NIP
    $this->post('/rh/employees', [
        'noms'                  => 'TESTNIP',
        'prenoms'               => 'Doublon',
        'matricule'             => 'EMP-NIP-DUP',
        'nip'                   => 'A1-2345-19901225',
        'email'                 => 'nipdup@test.local',
        'password'              => 'secret12',
        'password_confirmation' => 'secret12',
    ])->assertSessionHasErrors('nip');
});

it('employee NIP is uppercased and trimmed automatically', function () {
    actingAsSuperAdmin();
    $this->post('/rh/employees', [
        'noms'                  => 'TESTUC',
        'prenoms'               => 'Lower',
        'matricule'             => 'EMP-UC-1',
        'nip'                   => '  a1-2345-19901225  ',
        'email'                 => 'lower@test.local',
        'password'              => 'secret12',
        'password_confirmation' => 'secret12',
    ])->assertRedirect('/rh/employees');
    expect(Employee::where('matricule', 'EMP-UC-1')->first()->nip)->toBe('A1-2345-19901225');
});

it('creating an employee auto-creates a user account with role', function () {
    actingAsSuperAdmin();
    $this->post('/rh/employees', [
        'noms'                  => 'AUTOUSER',
        'prenoms'               => 'Compte',
        'matricule'             => 'EMP-AUTO-1',
        'email'                 => 'auto.user@test.local',
        'password'              => 'secret12',
        'password_confirmation' => 'secret12',
        'role'                  => 'user',
    ])->assertRedirect('/rh/employees');
    $emp = Employee::where('matricule', 'EMP-AUTO-1')->first();
    expect($emp->user_id)->not->toBeNull();
    expect($emp->user->email)->toBe('auto.user@test.local');
    expect($emp->user->hasRole('user'))->toBeTrue();
    expect(\Illuminate\Support\Facades\Hash::check('secret12', $emp->user->password))->toBeTrue();
});

it('employee requires email and password on creation', function () {
    actingAsSuperAdmin();
    $this->post('/rh/employees', [
        'noms'    => 'NoEmail', 'prenoms' => 'X', 'matricule' => 'EMP-NE',
    ])->assertSessionHasErrors(['email', 'password']);
});

it('employee rejects duplicate user email', function () {
    actingAsSuperAdmin();
    User::factory()->create(['email' => 'dup@x.com']);
    $this->post('/rh/employees', [
        'noms' => 'D', 'prenoms' => 'D', 'matricule' => 'EMP-D1',
        'email' => 'dup@x.com',
        'password' => 'secret12', 'password_confirmation' => 'secret12',
    ])->assertSessionHasErrors('email');
});

it('updating an employee never changes the password (security policy)', function () {
    actingAsSuperAdmin();
    $this->post('/rh/employees', [
        'noms' => 'U', 'prenoms' => 'P', 'matricule' => 'EMP-U1',
        'email' => 'updatable@x.com',
        'password' => 'oldpass12', 'password_confirmation' => 'oldpass12',
    ])->assertRedirect();
    $emp = Employee::where('matricule', 'EMP-U1')->first();
    $hashInitial = $emp->user->password;

    // Update sans password : OK, hash inchangé
    $this->put('/rh/employees/' . $emp->id, [
        'noms' => 'U2', 'prenoms' => 'P2', 'email' => 'updatable@x.com',
    ])->assertRedirect();
    expect($emp->user->fresh()->password)->toBe($hashInitial);

    // Update AVEC password : doit être IGNORÉ (politique de sécurité)
    $this->put('/rh/employees/' . $emp->id, [
        'noms' => 'U2', 'prenoms' => 'P2', 'email' => 'updatable@x.com',
        'password' => 'newpass12', 'password_confirmation' => 'newpass12',
    ])->assertRedirect();
    // Le hash NE DOIT PAS avoir changé — passer par /reset-password
    expect($emp->user->fresh()->password)->toBe($hashInitial);
    expect(\Illuminate\Support\Facades\Hash::check('newpass12', $emp->user->fresh()->password))->toBeFalse();
});

it('employee NIP is searchable in index', function () {
    actingAsSuperAdmin();
    Employee::create([
        'noms'      => 'Cherche', 'prenoms' => 'Moi',
        'matricule' => 'EMP-SR-1', 'statut' => 1,
        'nip'       => 'Z9-FIND-19850101',
    ]);
    $this->get('/rh/employees?search=Z9-FIND')->assertOk()->assertSee('Cherche');
});

it('referentiels RH admin CRUD works (6 types)', function () {
    actingAsSuperAdmin();
    foreach (['types-contrat', 'postes', 'departements', 'types-evenement', 'niveaux-qualification', 'nationalites'] as $type) {
        // Index OK
        $this->get("/admin/referentiel-rh/$type")->assertOk();

        // Création
        $code = strtoupper(\Illuminate\Support\Str::random(8));
        $this->post("/admin/referentiel-rh/$type", [
            'code'    => $code,
            'libelle' => 'Test ' . $type,
        ])->assertRedirect();

        $model = [
            'types-contrat'         => \App\Models\Referentiel\TypeContrat::class,
            'postes'                => \App\Models\Referentiel\Poste::class,
            'departements'          => \App\Models\Referentiel\Departement::class,
            'types-evenement'       => \App\Models\TypeEvenementCarriere::class,
            'niveaux-qualification' => \App\Models\Referentiel\NiveauQualification::class,
            'nationalites'          => \App\Models\Referentiel\Nationalite::class,
        ][$type];
        expect($model::where('code', $code)->exists())->toBeTrue();

        // Doublon code → erreur
        $this->post("/admin/referentiel-rh/$type", [
            'code' => $code, 'libelle' => 'Doublon',
        ])->assertSessionHasErrors('code');
    }
});

it('employee create form lists nationalites from referentiel', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\ReferentielsRhSeeder())->run();
    $resp = $this->get('/rh/employees/create');
    $resp->assertOk();
    $resp->assertSee('Gabonaise');
    $resp->assertSee('Française');
    $resp->assertSee('Camerounaise');
});

it('superieur hierarchique is resolved by poste (follows the role, not the person)', function () {
    actingAsSuperAdmin();

    // Crée un poste "Lead Dev Test" dans le référentiel
    $posteLead = \App\Models\Referentiel\Poste::firstOrCreate(
        ['code' => 'LEAD_DEV_TEST'],
        ['libelle' => 'Lead Dev Test', 'statut' => 1, 'ordre' => 99]
    );

    // 1er titulaire : Alice
    $alice = Employee::create([
        'noms' => 'Alice', 'prenoms' => 'A', 'matricule' => 'EMP-LEAD-1',
        'poste' => $posteLead->libelle, 'statut' => 1,
    ]);

    // Junior qui reporte au poste Lead Dev (pas à Alice directement)
    $junior = Employee::create([
        'noms' => 'Junior', 'prenoms' => 'J', 'matricule' => 'EMP-JR-1',
        'poste' => 'Développeur backend', 'statut' => 1,
        'superieur_poste_id' => $posteLead->id,
    ]);

    expect($junior->superieurActuel()?->id)->toBe($alice->id);

    // Alice change de poste → Marie devient le nouveau Lead Dev
    $alice->update(['poste' => 'Architect']);
    $marie = Employee::create([
        'noms' => 'Marie', 'prenoms' => 'M', 'matricule' => 'EMP-LEAD-2',
        'poste' => $posteLead->libelle, 'statut' => 1,
    ]);

    // Sans toucher au junior, son supérieur actuel est désormais Marie
    expect($junior->fresh()->superieurActuel()?->id)->toBe($marie->id);
});

it('superieurActuel returns null when poste is vacant', function () {
    actingAsSuperAdmin();
    $posteOrphan = \App\Models\Referentiel\Poste::firstOrCreate(
        ['code' => 'POSTE_VACANT_TEST'],
        ['libelle' => 'Poste Vacant Test', 'statut' => 1]
    );
    $emp = Employee::create([
        'noms' => 'Orphelin', 'prenoms' => 'O', 'matricule' => 'EMP-ORPH-1',
        'statut' => 1, 'superieur_poste_id' => $posteOrphan->id,
    ]);
    expect($emp->superieurActuel())->toBeNull();
});

it('subordonnesActuels lists employees reporting to my poste', function () {
    actingAsSuperAdmin();
    $posteManager = \App\Models\Referentiel\Poste::firstOrCreate(
        ['code' => 'MANAGER_TEST'],
        ['libelle' => 'Manager Test', 'statut' => 1]
    );
    $manager = Employee::create([
        'noms' => 'Mgr', 'prenoms' => 'M', 'matricule' => 'EMP-MGR-1',
        'poste' => $posteManager->libelle, 'statut' => 1,
    ]);
    Employee::create([
        'noms' => 'Sub1', 'prenoms' => 'S', 'matricule' => 'EMP-SUB-1',
        'statut' => 1, 'superieur_poste_id' => $posteManager->id,
    ]);
    Employee::create([
        'noms' => 'Sub2', 'prenoms' => 'S', 'matricule' => 'EMP-SUB-2',
        'statut' => 1, 'superieur_poste_id' => $posteManager->id,
    ]);
    expect($manager->subordonnesActuels()->count())->toBe(2);
});

it('employee saves nationalite chosen from referentiel', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\ReferentielsRhSeeder())->run();
    $this->post('/rh/employees', [
        'noms'                  => 'NATTEST',
        'prenoms'               => 'X',
        'matricule'             => 'EMP-NAT-1',
        'nationalite'           => 'Gabonaise',
        'email'                 => 'nat@test.local',
        'password'              => 'secret12',
        'password_confirmation' => 'secret12',
    ])->assertRedirect('/rh/employees');
    expect(Employee::where('matricule', 'EMP-NAT-1')->first()->nationalite)->toBe('Gabonaise');
});

it('employee saves urbaine localisation fields', function () {
    actingAsSuperAdmin();
    $this->post('/rh/employees', [
        'noms' => 'LOCURB', 'prenoms' => 'X', 'matricule' => 'EMP-LU-1',
        'email' => 'urb@test.local',
        'password' => 'secret12', 'password_confirmation' => 'secret12',
        'pays'             => 'Gabon',
        'province'         => 'Estuaire',
        'departement_geo'  => 'Komo-Mondah',
        'prefecture'       => 'Libreville',
        'sous_prefecture'  => 'Akanda',
        'zone_type'        => 'urbaine',
        'commune'          => 'Libreville',
        'arrondissement'   => '3eme arrondissement',
        'quartier_loc'     => 'Glass',
    ])->assertRedirect('/rh/employees');
    $emp = Employee::where('matricule', 'EMP-LU-1')->first();
    expect($emp->zone_type)->toBe('urbaine');
    expect($emp->pays)->toBe('Gabon');
    expect($emp->commune)->toBe('Libreville');
    expect($emp->quartier_loc)->toBe('Glass');
});

it('employee saves rurale localisation fields', function () {
    actingAsSuperAdmin();
    $this->post('/rh/employees', [
        'noms' => 'LOCRUR', 'prenoms' => 'X', 'matricule' => 'EMP-LR-1',
        'email' => 'rur@test.local',
        'password' => 'secret12', 'password_confirmation' => 'secret12',
        'pays'                 => 'Gabon',
        'province'             => 'Woleu-Ntem',
        'zone_type'            => 'rurale',
        'canton'               => 'Canton Ekobah',
        'regroupement_village' => 'Mvomekak',
        'village'              => 'Akok',
    ])->assertRedirect('/rh/employees');
    $emp = Employee::where('matricule', 'EMP-LR-1')->first();
    expect($emp->zone_type)->toBe('rurale');
    expect($emp->canton)->toBe('Canton Ekobah');
    expect($emp->village)->toBe('Akok');
});

it('employee rejects invalid zone_type', function () {
    actingAsSuperAdmin();
    $this->post('/rh/employees', [
        'noms' => 'LOCERR', 'prenoms' => 'X', 'matricule' => 'EMP-ERR-1',
        'email' => 'err@test.local',
        'password' => 'secret12', 'password_confirmation' => 'secret12',
        'zone_type' => 'autre',
    ])->assertSessionHasErrors('zone_type');
});

it('localisation referentiel admin CRUD works for all 11 types', function () {
    actingAsSuperAdmin();
    $types = [
        'pays-loc', 'provinces', 'departements-admin', 'prefectures', 'sous-prefectures',
        'communes', 'arrondissements', 'quartiers', 'cantons', 'regroupements-village', 'villages',
    ];
    foreach ($types as $t) {
        $this->get("/admin/referentiel-rh/$t")->assertOk();
        $code = strtoupper(\Illuminate\Support\Str::random(10));
        $this->post("/admin/referentiel-rh/$t", [
            'code' => $code, 'libelle' => 'Loc test ' . $t,
        ])->assertRedirect();
        expect(\App\Models\Referentiel\LocaliteAdmin::where('code', $code)->exists())->toBeTrue();
    }
    // Vérifie l'unicité scopée par type : même code OK pour deux types différents
    $code = 'CODE_UNI_TEST_XYZ';
    $this->post('/admin/referentiel-rh/pays-loc',  ['code' => $code, 'libelle' => 'X1'])->assertRedirect();
    $this->post('/admin/referentiel-rh/provinces', ['code' => $code, 'libelle' => 'X2'])->assertRedirect();
    expect(\App\Models\Referentiel\LocaliteAdmin::where('code', $code)->count())->toBe(2);
});

it('localisations seeded are exposed as options for tom select', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\LocalisationsAdminSeeder())->run();
    $resp = $this->get('/admin/referentiel-rh/villages/options?q=Akok');
    $resp->assertOk();
    expect(count($resp->json()))->toBeGreaterThan(0);
    expect($resp->json()[0]['libelle'])->toBe('Akok');
});

it('employee create form lists localisations from referentiel', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\LocalisationsAdminSeeder())->run();
    $resp = $this->get('/rh/employees/create');
    $resp->assertOk()
        ->assertSee('Estuaire')
        ->assertSee('Libreville')
        ->assertSee('Glass')
        ->assertSee('Akok');
});

it('qualification accepts niveau from referentiel', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\ReferentielsRhSeeder())->run();
    $emp = makeEmployee();
    $this->post('/rh/qualifications', [
        'employee_id' => $emp->id,
        'label'       => 'Licence en informatique',
        'organisme'   => 'UOB',
        'niveau'      => 'Licence / Bac+3',
    ])->assertRedirect('/rh/qualifications');
    expect(Qualification::where('niveau', 'Licence / Bac+3')->exists())->toBeTrue();
});

it('qualification create form lists niveaux from referentiel', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\ReferentielsRhSeeder())->run();
    $resp = $this->get('/rh/qualifications/create');
    $resp->assertOk();
    $resp->assertSee('Baccalauréat');
    $resp->assertSee('Master 2 / Bac+5');
    $resp->assertSee('Doctorat / PhD / Bac+8');
});

it('evenement carriere create form lists types and postes from referentiel', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\ReferentielsRhSeeder())->run();
    $resp = $this->get('/rh/evenements-carriere/create');
    $resp->assertOk();
    // Types d'évènement seedés visibles
    $resp->assertSee('Promotion');
    $resp->assertSee('Mutation interne');
    // Postes en ancien_poste / nouveau_poste
    $resp->assertSee('Chef de projet');
});

it('referentiel options endpoint returns JSON', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\ReferentielsRhSeeder())->run();
    $resp = $this->get('/admin/referentiel-rh/types-contrat/options');
    $resp->assertOk();
    $resp->assertHeader('Content-Type', 'application/json');
    expect(count($resp->json()))->toBeGreaterThan(0);
    // Filtrage
    $resp2 = $this->get('/admin/referentiel-rh/types-contrat/options?q=CDI');
    expect(count($resp2->json()))->toBe(1);
});

it('employee create form lists postes from referentiel', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\ReferentielsRhSeeder())->run();
    $this->get('/rh/employees/create')->assertOk()->assertSee('Chef de projet');
});

it('campagne de paie : crée, génère bulletins, valide', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\RhRubriquesSeeder())->run();

    // Sélection via échantillon pour rendre le test robuste à l'état initial de la DB
    // (la DB de test peut contenir des employés résiduels créés par d'autres tests).
    $emps = collect(range(1, 3))->map(fn($i) => makeEmployee([
        'salaire_base' => 500000 + $i * 100000,
        'matricule'    => 'CP-' . $i,
    ]));
    $ech = \App\Models\EchantillonPaie::create([
        'code'    => 'ECH-CAMP-TEST-' . random_int(1000, 9999),
        'libelle' => 'Test campagne',
        'statut'  => true,
    ]);
    $ech->employes()->attach($emps->pluck('id')->all());

    // 1. Création campagne (par_echantillon — 3 employés explicites)
    $this->post('/rh/campagnes-paie', [
        'libelle'        => 'Test campagne avril',
        'annee'          => 2026,
        'mois'           => 4,
        'periodicite'    => 'mensuelle',
        'simulation'     => '0',
        'mode_selection' => 'par_echantillon',
        'echantillon_id' => $ech->id,
    ])->assertRedirect('/rh/campagnes-paie');

    $campagne = \App\Models\CampagnePaie::where('annee', 2026)->where('mois', 4)->first();
    expect($campagne)->not->toBeNull();
    expect($campagne->code)->toStartWith('CP-2026-04');
    expect($campagne->statut)->toBe(0);
    expect($campagne->employes->count())->toBe(3);

    // 2. Génération des bulletins
    $this->post("/rh/campagnes-paie/{$campagne->id}/generer")->assertRedirect();
    $campagne->refresh();
    expect($campagne->statut)->toBe(1);
    expect($campagne->nombre_bulletins)->toBe(3);
    expect((float) $campagne->masse_brute)->toBeGreaterThan(0);

    // Vérifie qu'un bulletin a bien le campagne_paie_id
    $bulletin = \App\Models\Paie::where('campagne_paie_id', $campagne->id)->first();
    expect($bulletin)->not->toBeNull();
    expect($bulletin->statut)->toBe(0); // brouillon

    // 3. Validation
    $this->post("/rh/campagnes-paie/{$campagne->id}/valider")->assertRedirect();
    $campagne->refresh();
    expect($campagne->statut)->toBe(2);
    expect($campagne->validee_par)->not->toBeNull();
    expect($bulletin->fresh()->statut)->toBe(1); // validé

    // 4. Régénérer = idempotent (n'ajoute pas de doublons)
    $this->post("/rh/campagnes-paie/{$campagne->id}/generer")->assertRedirect();
    expect(\App\Models\Paie::where('campagne_paie_id', $campagne->id)->count())->toBe(3);
});

it('campagne de paie en mode simulation', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\RhRubriquesSeeder())->run();
    makeEmployee(['salaire_base' => 600000, 'matricule' => 'SIM-1']);

    $this->post('/rh/campagnes-paie', [
        'libelle'        => 'Simulation salaires +5%',
        'annee'          => 2026,
        'mois'           => 6,
        'periodicite'    => 'mensuelle',
        'simulation'     => '1',
        'mode_selection' => 'tous_actifs',
    ])->assertRedirect();

    $c = \App\Models\CampagnePaie::simulation()->first();
    expect($c)->not->toBeNull();
    expect($c->simulation)->toBeTrue();
    expect($c->code)->toStartWith('SIM-2026-06');
});

it('campagne supprimable seulement si modifiable', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\RhRubriquesSeeder())->run();
    makeEmployee(['salaire_base' => 500000, 'matricule' => 'DEL-1']);

    $this->post('/rh/campagnes-paie', [
        'libelle' => 'À supprimer', 'annee' => 2026, 'mois' => 7,
        'periodicite' => 'mensuelle', 'mode_selection' => 'tous_actifs',
    ])->assertRedirect();
    $c = \App\Models\CampagnePaie::where('mois', 7)->first();

    // Brouillon → suppression OK
    $this->delete("/rh/campagnes-paie/{$c->id}")->assertRedirect('/rh/campagnes-paie');
    expect(\App\Models\CampagnePaie::find($c->id))->toBeNull();
});

it('all RH index routes return 200 for super-admin', function () {
    actingAsSuperAdmin();
    $routes = [
        '/rh/employees', '/rh/absences', '/rh/paie', '/rh/recrutements',
        '/rh/affilies', '/rh/qualifications', '/rh/competences', '/rh/formations',
        '/rh/missions', '/rh/rubriques', '/rh/evenements-carriere',
        '/rh/sanctions', '/rh/departs', '/rh/evaluations-performance',
        '/rh/conges-soldes', '/rh/plannings',
        '/rh/payements', '/rh/payements-globals?annee=2026',
        '/rh', '/rh/audit-log',
    ];
    foreach ($routes as $url) {
        $resp = $this->get($url);
        expect($resp->status())->toBe(200, "URL $url returned " . $resp->status());
    }
});
