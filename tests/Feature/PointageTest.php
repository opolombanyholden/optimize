<?php

use App\Models\Employee;
use App\Models\Pointage;
use App\Services\Rh\PaieCalculator;

beforeEach(function () {
    seedRoles();
});

function makeEmpPointage(array $attrs = []): Employee
{
    return Employee::create(array_merge([
        'noms'         => 'P', 'prenoms' => 'T',
        'matricule'    => 'PT-' . random_int(1000, 9999),
        'email'        => 'pt' . random_int(1000, 9999) . '@test.local',
        'salaire_base' => 500000,
        'type_contrat' => 'CDI',
        'date_embauche' => now()->subYear(),
        'statut'       => 1,
    ], $attrs));
}

it('crée un pointage avec décomposition des heures', function () {
    actingAsSuperAdmin();
    $emp = makeEmpPointage();

    $this->post('/rh/pointages', [
        'employee_id' => $emp->id,
        'date'        => '2026-05-15',
        'h_normales'  => 8,
        'h_sup'       => 2,
        'h_nuit'      => 0,
        'h_dimanche'  => 0,
    ])->assertRedirect();

    $p = Pointage::where('employee_id', $emp->id)->first();
    expect($p)->not->toBeNull();
    expect((float) $p->h_normales)->toBe(8.0);
    expect((float) $p->h_sup)->toBe(2.0);
    expect($p->total)->toBe(10.0);
    expect($p->statut)->toBe(0);
});

it('refuse heures hors bornes (>24h)', function () {
    actingAsSuperAdmin();
    $emp = makeEmpPointage();
    $this->post('/rh/pointages', [
        'employee_id' => $emp->id,
        'date'        => '2026-05-15',
        'h_normales'  => 25, // invalide
    ])->assertSessionHasErrors('h_normales');
});

it('upsert : 2e POST sur même (employee, date) met à jour au lieu de dupliquer', function () {
    actingAsSuperAdmin();
    $emp = makeEmpPointage();

    $this->post('/rh/pointages', [
        'employee_id' => $emp->id, 'date' => '2026-05-15', 'h_normales' => 8,
    ])->assertRedirect();
    $this->post('/rh/pointages', [
        'employee_id' => $emp->id, 'date' => '2026-05-15', 'h_normales' => 10,
    ])->assertRedirect();

    expect(Pointage::where('employee_id', $emp->id)->where('date', '2026-05-15')->count())->toBe(1);
    expect((float) Pointage::where('employee_id', $emp->id)->first()->h_normales)->toBe(10.0);
});

it('validation en masse passe statut 0 → 1', function () {
    actingAsSuperAdmin();
    $emp = makeEmpPointage();
    $ids = [];
    for ($i = 1; $i <= 3; $i++) {
        $ids[] = Pointage::create([
            'employee_id' => $emp->id, 'date' => "2026-05-0$i",
            'h_normales' => 8, 'statut' => 0,
        ])->id;
    }

    $this->post('/rh/pointages/valider', ['ids' => $ids])->assertRedirect();

    $statuts = Pointage::whereIn('id', $ids)->pluck('statut')->all();
    expect($statuts)->each->toBe(1);
});

it('pointage validé n\'est plus modifiable (edit/destroy 403)', function () {
    actingAsSuperAdmin();
    $emp = makeEmpPointage();
    $p = Pointage::create([
        'employee_id' => $emp->id, 'date' => '2026-05-15',
        'h_normales' => 8, 'statut' => 1, // validé
    ]);

    $this->get("/rh/pointages/{$p->id}/edit")->assertForbidden();
    $this->delete("/rh/pointages/{$p->id}")->assertForbidden();
});

it('Pointage::totalHeuresSup agrège correctement', function () {
    actingAsSuperAdmin();
    $emp = makeEmpPointage();
    Pointage::create(['employee_id' => $emp->id, 'date' => '2026-05-01', 'h_sup' => 2, 'statut' => 1]);
    Pointage::create(['employee_id' => $emp->id, 'date' => '2026-05-02', 'h_sup' => 3, 'statut' => 1]);
    Pointage::create(['employee_id' => $emp->id, 'date' => '2026-05-03', 'h_sup' => 1, 'statut' => 0]); // brouillon → exclu
    Pointage::create(['employee_id' => $emp->id, 'date' => '2026-06-01', 'h_sup' => 5, 'statut' => 1]); // hors période → exclu

    $total = Pointage::totalHeuresSup($emp->id, '2026-05-01', '2026-05-31');
    expect($total)->toBe(5.0);
});

it('PaieCalculator agrège automatiquement heures_sup depuis pointages validés', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\RhRubriquesSeeder())->run();
    $emp = makeEmpPointage(['salaire_base' => 500000]);

    Pointage::create(['employee_id' => $emp->id, 'date' => '2026-05-01', 'h_sup' => 4, 'statut' => 1]);
    Pointage::create(['employee_id' => $emp->id, 'date' => '2026-05-02', 'h_sup' => 2, 'statut' => 1]);

    $calc = new PaieCalculator();
    // On ne passe PAS heures_sup en input → doit être agrégé depuis pointages
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 500000,
    ]);

    expect($r['bulletin']['heures_sup'])->toBe(6.0);
});

it('PaieCalculator respecte la valeur explicite si heures_sup fournie en input', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\RhRubriquesSeeder())->run();
    $emp = makeEmpPointage();
    Pointage::create(['employee_id' => $emp->id, 'date' => '2026-05-01', 'h_sup' => 100, 'statut' => 1]);

    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 500000,
        'heures_sup'   => 0, // override explicite
    ]);
    expect($r['bulletin']['heures_sup'])->toBe(0.0); // l'input prime sur l'agrégat pointage
});

it('grille mensuelle store en batch', function () {
    actingAsSuperAdmin();
    $emp = makeEmpPointage();

    $jours = [];
    for ($d = 1; $d <= 3; $d++) {
        $jours[] = [
            'date'       => "2026-05-0$d",
            'h_normales' => 8,
            'h_sup'      => $d == 2 ? 2 : 0,
            'h_nuit'     => 0,
            'h_dimanche' => 0,
        ];
    }

    $this->post("/rh/pointages/grille/{$emp->id}", [
        'mois' => 5, 'annee' => 2026, 'jours' => $jours,
    ])->assertRedirect();

    expect(Pointage::where('employee_id', $emp->id)->count())->toBe(3);
    expect((float) Pointage::where('employee_id', $emp->id)->where('date', '2026-05-02')->first()->h_sup)->toBe(2.0);
});

it('grille : ligne entièrement vide supprime un pointage brouillon existant', function () {
    actingAsSuperAdmin();
    $emp = makeEmpPointage();
    Pointage::create([
        'employee_id' => $emp->id, 'date' => '2026-05-01',
        'h_normales' => 8, 'statut' => 0,
    ]);

    $this->post("/rh/pointages/grille/{$emp->id}", [
        'mois' => 5, 'annee' => 2026, 'jours' => [
            ['date' => '2026-05-01', 'h_normales' => 0, 'h_sup' => 0, 'h_nuit' => 0, 'h_dimanche' => 0],
        ],
    ])->assertRedirect();

    expect(Pointage::where('employee_id', $emp->id)->whereDate('date', '2026-05-01')->count())->toBe(0);
});
