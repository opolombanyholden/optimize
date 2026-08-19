<?php

use App\Models\Employee;
use App\Models\Paie;
use App\Models\Rubrique;
use App\Services\Rh\PaieCalculator;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\RhRubriquesSeeder())->run();
});

function makePaieEmp(array $attrs = []): Employee
{
    return Employee::create(array_merge([
        'noms'           => 'Doe',
        'prenoms'        => 'Jane',
        'matricule'      => 'EMP-' . random_int(10000, 99999),
        'email'          => 'p' . random_int(1000, 9999) . '@test.local',
        'salaire_base'   => 500000,
        'parts_fiscales' => 1.5,
        'type_contrat'   => 'CDI',
        'date_embauche'  => now()->subYears(2),
        'statut'         => 1,
        'matricule_cnss'   => 'CNSS-' . random_int(1000, 9999),
        'matricule_cnamgs' => 'CNAMGS-' . random_int(1000, 9999),
    ], $attrs));
}

it('plafonne CNSS à 1.500.000 XAF (base = min(brut, valeur2))', function () {
    $emp = makePaieEmp(['salaire_base' => 2_000_000]);
    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 2_000_000,
    ]);

    // CNSS salariale = code numérique 2100, libelle_court CNSS_S, plafond 1.500.000 (valeur2), taux 2.5%
    $cnssSal = collect($r['lignes'])->firstWhere('code', '2100');
    $cnssPat = collect($r['lignes'])->firstWhere('code', 'PAT_2110');
    expect($cnssSal)->not->toBeNull()->and($cnssPat)->not->toBeNull();

    // La base est plafonnée à 1.500.000 même si le brut > 1.500.000
    expect($cnssSal['base'])->toBe(1_500_000.0);
    expect($cnssPat['base'])->toBe(1_500_000.0);

    // Montants : 2.5% × 1.500.000 = 37.500 (et non 50.000 sans plafond)
    expect($cnssSal['montant'])->toBe(37_500.0);
    // Montants : 16% × 1.500.000 = 240.000
    expect($cnssPat['montant'])->toBe(240_000.0);
});

it('ne plafonne pas une rubrique avec valeur2=0 (FNH, CFP)', function () {
    $emp = makePaieEmp(['salaire_base' => 3_000_000]);
    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 3_000_000,
    ]);
    // FNH (code 2600) : 2% sans plafond
    $fnh = collect($r['lignes'])->firstWhere('code', '2600');
    expect($fnh)->not->toBeNull();
    expect($fnh['base'])->toBeGreaterThan(2_000_000); // utilise le brut entier
});

it('calcule un bulletin Gabon réaliste : brut > cot > net', function () {
    $emp = makePaieEmp(['salaire_base' => 800_000]);
    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 800_000,
        'primes'       => 50_000,
    ]);

    $b = $r['bulletin'];
    expect($b['salaire_base'])->toBe(800_000.0);
    expect($b['primes'])->toBe(50_000.0);
    expect($b['brut'])->toBeGreaterThanOrEqual(850_000);
    expect($b['cotisations_salariales'])->toBeGreaterThan(0);
    expect($b['cotisations_patronales'])->toBeGreaterThan(0);
    expect($b['net_imposable'])->toBe(round($b['brut'] - $b['cotisations_salariales'], 2));
    expect($b['net_a_payer'])->toBeLessThan($b['brut']);
    expect($b['net_a_payer'])->toBeGreaterThan(0);
});

it('agrège heures sup et indemnités dans le brut', function () {
    $emp = makePaieEmp(['salaire_base' => 500_000]);
    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 500_000,
        'heures_sup'   => 30_000,
        'indemnites'   => 20_000,
        'primes'       => 10_000,
    ]);

    // brut >= 500.000 + 30.000 + 20.000 + 10.000 = 560.000 (+ rubriques gain éventuelles)
    expect($r['bulletin']['brut'])->toBeGreaterThanOrEqual(560_000);
});

it('déduit avances et retenues manuelles du net', function () {
    $emp = makePaieEmp(['salaire_base' => 600_000]);
    $calc = new PaieCalculator();

    $base = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 600_000,
    ]);
    $netSansAvance = $base['bulletin']['net_a_payer'];

    $avecAvance = $calc->calculer([
        'employee_id'        => $emp->id,
        'debut'              => '2026-05-01',
        'fin'                => '2026-05-31',
        'salaire_base'       => 600_000,
        'avances'            => 50_000,
        'retenues_manuelles' => 10_000,
    ]);

    expect($avecAvance['bulletin']['net_a_payer'])->toBe(round($netSansAvance - 60_000, 2));
    expect($avecAvance['bulletin']['avances'])->toBe(50_000.0);
});

it('génère un numéro de bulletin au format B-YYYY-MM-{matricule}', function () {
    $emp = makePaieEmp(['matricule' => 'EMP-42', 'salaire_base' => 400_000]);
    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 400_000,
    ]);
    expect($r['bulletin']['numero_bulletin'])->toBe('B-2026-05-EMP-42');
});

it('ajoute un suffixe -N quand le numéro de bulletin existe déjà', function () {
    $emp = makePaieEmp(['matricule' => 'COL-001', 'salaire_base' => 500_000]);
    $calc = new PaieCalculator();
    $inputs = [
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 500_000,
    ];
    // Premier bulletin
    $r1 = $calc->calculer($inputs);
    $calc->persister($r1);
    expect($r1['bulletin']['numero_bulletin'])->toBe('B-2026-05-COL-001');

    // Second appel : doit suffixer -2
    $r2 = $calc->calculer($inputs);
    expect($r2['bulletin']['numero_bulletin'])->toBe('B-2026-05-COL-001-2');
    $calc->persister($r2);

    // Troisième : -3
    $r3 = $calc->calculer($inputs);
    expect($r3['bulletin']['numero_bulletin'])->toBe('B-2026-05-COL-001-3');
});

it('inclut un snapshot identité figé', function () {
    $emp = makePaieEmp([
        'noms'    => 'Mbeng',
        'prenoms' => 'Pauline',
        'matricule_cnss' => 'CNSS-12345',
    ]);
    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 700_000,
    ]);
    $snap = $r['bulletin']['snapshot_employe'];
    expect($snap['noms'])->toBe('Mbeng');
    expect($snap['prenoms'])->toBe('Pauline');
    expect($snap['matricule_cnss'])->toBe('CNSS-12345');
});

it('cumule annuellement les bulletins validés', function () {
    $emp = makePaieEmp(['salaire_base' => 500_000]);
    $calc = new PaieCalculator();

    // Premier bulletin (mois 1) validé
    $r1 = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-01-01',
        'fin'          => '2026-01-31',
        'salaire_base' => 500_000,
    ]);
    $p1 = $calc->persister($r1);
    $p1->update(['statut' => 1]);

    // Deuxième bulletin (mois 2) — doit cumuler le précédent
    $r2 = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-02-01',
        'fin'          => '2026-02-28',
        'salaire_base' => 500_000,
    ]);

    expect($r2['bulletin']['cumul_annuel_brut'])
        ->toBe(round((float) $p1->brut + (float) $r2['bulletin']['brut'], 2));
});

it('persiste les lignes de rubrique avec base/taux/montant', function () {
    $emp = makePaieEmp(['salaire_base' => 750_000]);
    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 750_000,
    ]);
    $paie = $calc->persister($r);

    expect($paie->rubriques()->count())->toBeGreaterThan(0);
    $cnss = $paie->rubriques()->where('rubriques.code', 'CNSS_S')->first();
    if ($cnss) {
        expect((float) $cnss->pivot->taux)->toBe(2.5);
        expect((float) $cnss->pivot->montant)->toBeGreaterThan(0);
    }
});

it('ne génère pas d\'IRPP négatif sur petit salaire', function () {
    $emp = makePaieEmp(['salaire_base' => 80_000]);
    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 80_000,
    ]);
    expect($r['bulletin']['irpp'])->toBeGreaterThanOrEqual(0);
    expect($r['bulletin']['net_a_payer'])->toBeGreaterThan(0);
});

it('PaieCalculator est idempotent : même input → même résultat', function () {
    $emp = makePaieEmp(['salaire_base' => 600_000]);
    $inputs = [
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 600_000,
        'primes'       => 25_000,
    ];
    $r1 = (new PaieCalculator())->calculer($inputs);
    $r2 = (new PaieCalculator())->calculer($inputs);

    expect($r1['bulletin']['brut'])->toBe($r2['bulletin']['brut']);
    expect($r1['bulletin']['net_a_payer'])->toBe($r2['bulletin']['net_a_payer']);
    expect(count($r1['lignes']))->toBe(count($r2['lignes']));
});
