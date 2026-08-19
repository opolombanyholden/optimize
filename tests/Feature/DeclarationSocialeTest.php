<?php

use App\Models\DeclarationSociale;
use App\Models\Employee;
use App\Services\Rh\DeclarationSocialeBuilder;
use App\Services\Rh\PaieCalculator;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\RhRubriquesSeeder())->run();
});

function makeDeclEmp(array $attrs = []): Employee
{
    return Employee::create(array_merge([
        'noms'             => 'Test',
        'prenoms'          => 'Decl',
        'matricule'        => 'D-' . random_int(10000, 99999),
        'email'            => 'd' . random_int(1000, 9999) . '@test.local',
        'salaire_base'     => 600_000,
        'matricule_cnss'   => 'CNSS-' . random_int(10000, 99999),
        'matricule_cnamgs' => 'CNAMGS-' . random_int(10000, 99999),
        'type_contrat'     => 'CDI',
        'date_embauche'    => now()->subYears(2),
        'statut'           => 1,
    ], $attrs));
}

function genererBulletinValide(Employee $emp, string $debut, string $fin, float $salaireBase): \App\Models\Paie
{
    $calc = new PaieCalculator();
    $r = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => $debut,
        'fin'          => $fin,
        'salaire_base' => $salaireBase,
    ]);
    $paie = $calc->persister($r);
    $paie->update(['statut' => 1]);
    return $paie;
}

it('refuse un organisme inconnu', function () {
    $b = new DeclarationSocialeBuilder();
    expect(fn() => $b->calculer('inconnu', 2026, 5, null))
        ->toThrow(\InvalidArgumentException::class);
});

it('exige un trimestre pour CNSS (périodicité trimestrielle)', function () {
    $b = new DeclarationSocialeBuilder();
    expect(fn() => $b->calculer('cnss', 2026, null, null))
        ->toThrow(\InvalidArgumentException::class);
});

it('exige un mois pour CNAMGS (périodicité mensuelle)', function () {
    $b = new DeclarationSocialeBuilder();
    expect(fn() => $b->calculer('cnamgs', 2026, null, null))
        ->toThrow(\InvalidArgumentException::class);
});

it('agrège les bulletins du mois pour CNAMGS', function () {
    $emp = makeDeclEmp(['salaire_base' => 600_000]);
    genererBulletinValide($emp, '2026-05-01', '2026-05-31', 600_000);

    $b = new DeclarationSocialeBuilder();
    $r = $b->calculer('cnamgs', 2026, 5, null);

    expect($r['periode']['periodicite'])->toBe('mensuelle');
    expect($r['periode']['date_debut'])->toBe('2026-05-01');
    expect($r['periode']['date_fin'])->toBe('2026-05-31');
    expect($r['totaux']['nombre_employes'])->toBe(1);
    expect($r['totaux']['total_brut'])->toBeGreaterThan(0);
});

it('agrège les bulletins du trimestre pour CNSS', function () {
    $emp = makeDeclEmp(['salaire_base' => 600_000]);
    genererBulletinValide($emp, '2026-04-01', '2026-04-30', 600_000);
    genererBulletinValide($emp, '2026-05-01', '2026-05-31', 600_000);
    genererBulletinValide($emp, '2026-06-01', '2026-06-30', 600_000);

    $b = new DeclarationSocialeBuilder();
    $r = $b->calculer('cnss', 2026, null, 2); // T2 = avril, mai, juin

    expect($r['periode']['periodicite'])->toBe('trimestrielle');
    expect($r['periode']['date_debut'])->toBe('2026-04-01');
    expect($r['periode']['date_fin'])->toBe('2026-06-30');
    expect($r['totaux']['nombre_employes'])->toBe(1);
    expect($r['lignes'][0]['nb_jours_travailles'])->toBe(90); // 3 mois × 30
});

it('plafonne CNSS à 1.500.000 XAF par mois', function () {
    $emp = makeDeclEmp(['salaire_base' => 2_000_000]);
    genererBulletinValide($emp, '2026-05-01', '2026-05-31', 2_000_000);

    $b = new DeclarationSocialeBuilder();
    $r = $b->calculer('cnss', 2026, null, 2);

    $ligne = $r['lignes'][0];
    // Brut plafonné = min(brut, 1.500.000 × nb_mois_inclus)
    // Ici 1 bulletin × 1.500.000 = 1.500.000 max
    expect($ligne['brut_plafonne'])->toBeLessThanOrEqual(1_500_000);
});

it('ne sélectionne que les bulletins validés ou payés', function () {
    $emp = makeDeclEmp();
    // Bulletin en brouillon (statut=0) — doit être exclu
    $calc = new PaieCalculator();
    $r1 = $calc->calculer([
        'employee_id'  => $emp->id,
        'debut'        => '2026-05-01',
        'fin'          => '2026-05-31',
        'salaire_base' => 600_000,
    ]);
    $calc->persister($r1); // statut=0 par défaut

    $b = new DeclarationSocialeBuilder();
    $r = $b->calculer('cnamgs', 2026, 5, null);
    expect($r['totaux']['nombre_employes'])->toBe(0);
});

it('génère un code unique formaté par périodicité', function () {
    $emp = makeDeclEmp();
    genererBulletinValide($emp, '2026-05-01', '2026-05-31', 500_000);
    $b = new DeclarationSocialeBuilder();
    $resultat = $b->calculer('cnamgs', 2026, 5, null);
    $declaration = $b->persister($resultat);

    expect($declaration->code)->toBe('CNAMGS-2026-05');
});

it('persiste les lignes avec snapshot identité', function () {
    $emp = makeDeclEmp(['noms' => 'Obame', 'prenoms' => 'Jean', 'matricule' => 'M-001']);
    genererBulletinValide($emp, '2026-05-01', '2026-05-31', 500_000);

    $b = new DeclarationSocialeBuilder();
    $declaration = $b->persister($b->calculer('cnamgs', 2026, 5, null));

    $l = $declaration->lignes->first();
    expect($l->noms)->toBe('Obame');
    expect($l->prenoms)->toBe('Jean');
    expect($l->matricule_employeur)->toBe('M-001');
    expect($l->matricule_organisme)->toContain('CNAMGS-');
});

it('FNH n\'agrège que la cotisation patronale (pas de salariale)', function () {
    $emp = makeDeclEmp();
    genererBulletinValide($emp, '2026-05-01', '2026-05-31', 500_000);

    $b = new DeclarationSocialeBuilder();
    $r = $b->calculer('fnh', 2026, 5, null);

    expect($r['totaux']['total_cot_salariale'])->toBe(0.0);
    expect($r['totaux']['total_cot_patronale'])->toBeGreaterThan(0);
});
