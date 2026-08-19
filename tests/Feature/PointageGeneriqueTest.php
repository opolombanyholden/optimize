<?php

use App\Models\Employee;
use App\Models\Pointage;
use App\Services\Rh\PointageMethodService;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    seedRoles();
    // Token générique fixe pour les tests
    config(['pointage.qr_generique_token' => 'TEST_GENERIC_TOKEN_1234567890ABCDEF']);
    config(['pointage.tolerate_local' => true]);
    config(['pointage.intranet_ips' => []]);
});

function makeEmpAvecPin(string $matricule, string $pin = '1234'): Employee
{
    return Employee::create([
        'noms'         => 'Gen',
        'prenoms'      => 'Test',
        'matricule'    => $matricule,
        'email'        => strtolower($matricule) . '@test.local',
        'salaire_base' => 500000,
        'type_contrat' => 'CDI',
        'date_embauche' => now()->subYear(),
        'statut'       => 1,
        'pointage_pin' => $pin, // cast 'hashed' bcrypt
    ]);
}

it('affiche le formulaire si le token est valide', function () {
    $this->get('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF')
        ->assertOk()
        ->assertSee('Votre matricule')
        ->assertSee('Votre PIN');
});

it('token invalide → 404', function () {
    $this->get('/p/g/MAUVAIS_TOKEN')->assertNotFound();
});

it('si aucun token configuré → 404 même avec un faux token', function () {
    config(['pointage.qr_generique_token' => null]);
    $this->get('/p/g/N_IMPORTE_QUOI')->assertNotFound();
});

it('matricule + PIN corrects → pointage créé en mode qr_generique', function () {
    $emp = makeEmpAvecPin('GEN-001', '1234');

    $resp = $this->post('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF', [
        'matricule' => 'GEN-001',
        'pin'       => '1234',
    ]);
    $resp->assertOk()->assertSee('Entrée enregistrée');

    $p = Pointage::where('employee_id', $emp->id)->first();
    expect($p)->not->toBeNull();
    expect($p->mode_pointage)->toBe('qr_generique');
    expect($p->statut)->toBe(1); // validé immédiatement (preuve par PIN)
});

it('mauvais PIN → erreur sans révéler l\'existence du matricule', function () {
    makeEmpAvecPin('GEN-002', '1234');

    $resp = $this->from('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF')
        ->post('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF', [
            'matricule' => 'GEN-002',
            'pin'       => '9999',
        ]);
    $resp->assertSessionHasErrors('pin');
    expect(Pointage::count())->toBe(0);
});

it('matricule inexistant → message générique identique au mauvais PIN', function () {
    $resp = $this->from('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF')
        ->post('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF', [
            'matricule' => 'INEXISTANT',
            'pin'       => '1234',
        ]);
    $resp->assertSessionHasErrors('pin');
});

it('employé inactif (statut=0) ne peut pas pointer', function () {
    $emp = makeEmpAvecPin('GEN-INACTIF', '1234');
    $emp->update(['statut' => 0]);
    $this->from('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF')
        ->post('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF', [
            'matricule' => 'GEN-INACTIF', 'pin' => '1234',
        ])->assertSessionHasErrors('pin');
});

it('rate limit : trop de tentatives bloque pendant la fenêtre', function () {
    config(['pointage.pin_max_tentatives' => 3]);
    makeEmpAvecPin('GEN-RL', '1234');

    // 3 tentatives échouées
    for ($i = 0; $i < 3; $i++) {
        $this->from('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF')
            ->post('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF', [
                'matricule' => 'GEN-RL', 'pin' => '9999',
            ])->assertSessionHasErrors('pin');
    }
    // 4e tentative — même avec le bon PIN, doit échouer (verrou)
    $resp = $this->from('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF')
        ->post('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF', [
            'matricule' => 'GEN-RL', 'pin' => '1234',
        ]);
    $resp->assertSessionHasErrors('pin');
    $errors = session('errors')->getMessages()['pin'][0] ?? '';
    expect($errors)->toContain('Trop de tentatives');
});

it('2e scan le même jour → enregistre la sortie', function () {
    $emp = makeEmpAvecPin('GEN-3', '1234');
    // 1er scan
    $this->post('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF', [
        'matricule' => 'GEN-3', 'pin' => '1234',
    ])->assertOk();
    // 2e scan
    $this->post('/p/g/TEST_GENERIC_TOKEN_1234567890ABCDEF', [
        'matricule' => 'GEN-3', 'pin' => '1234',
    ])->assertOk()->assertSee('Sortie enregistrée');

    $p = Pointage::where('employee_id', $emp->id)->first();
    expect($p->heure_entree)->not->toBeNull();
    expect($p->heure_sortie)->not->toBeNull();
});

// ─── Gestion admin du PIN ─────────────────────────────────

it('admin peut définir le PIN d\'un employé', function () {
    actingAsSuperAdmin();
    $emp = Employee::create([
        'noms' => 'X', 'prenoms' => 'Y', 'matricule' => 'PIN-001',
        'email' => 'pin@test.local', 'salaire_base' => 500000,
        'type_contrat' => 'CDI', 'date_embauche' => now(), 'statut' => 1,
    ]);

    $this->post("/rh/employees/{$emp->id}/pointage-pin", ['pin' => '1234'])
        ->assertRedirect();

    $emp->refresh();
    expect($emp->pointage_pin)->not->toBeNull();
    expect(Hash::check('1234', $emp->pointage_pin))->toBeTrue();
    expect($emp->pointage_pin_changed_at)->not->toBeNull();
});

it('PIN doit faire entre 4 et 6 chiffres', function () {
    actingAsSuperAdmin();
    $emp = Employee::create([
        'noms' => 'X', 'prenoms' => 'Y', 'matricule' => 'PIN-002',
        'email' => 'pin2@test.local', 'salaire_base' => 500000,
        'type_contrat' => 'CDI', 'date_embauche' => now(), 'statut' => 1,
    ]);
    $this->post("/rh/employees/{$emp->id}/pointage-pin", ['pin' => '12'])
        ->assertSessionHasErrors('pin');
    $this->post("/rh/employees/{$emp->id}/pointage-pin", ['pin' => 'abcd'])
        ->assertSessionHasErrors('pin');
    $this->post("/rh/employees/{$emp->id}/pointage-pin", ['pin' => '12345678'])
        ->assertSessionHasErrors('pin');
});

// ─── Admin QR générique ───────────────────────────────────

it('admin voit la page QR générique avec token configuré', function () {
    actingAsSuperAdmin();
    $this->get('/rh/pointages/qr-generique')
        ->assertOk()
        ->assertSee('TEST_GENERIC_TOKEN');
});

it('admin télécharge le PNG du QR générique', function () {
    actingAsSuperAdmin();
    $resp = $this->get('/rh/pointages/qr-generique/image');
    $resp->assertOk();
    expect($resp->headers->get('Content-Type'))->toContain('image/png');
    expect(substr($resp->getContent(), 0, 8))->toBe("\x89PNG\r\n\x1A\n");
});

it('si pas de token configuré, l\'admin voit l\'écran de génération', function () {
    config(['pointage.qr_generique_token' => null]);
    actingAsSuperAdmin();
    $this->get('/rh/pointages/qr-generique')
        ->assertOk()
        ->assertSee('Aucun token configuré');
});
