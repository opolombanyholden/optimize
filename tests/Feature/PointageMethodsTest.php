<?php

use App\Models\Employee;
use App\Models\Pointage;
use App\Models\User;
use App\Services\Rh\PointageMethodService;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    seedRoles();
    // En tests, on whitelist explicitement une IP simulée "entreprise"
    config(['pointage.intranet_ips' => ['203.0.113.0/24']]);
    config(['pointage.tolerate_local' => false]);
});

function makeEmpAvecUser(array $attrs = []): Employee
{
    $user = User::create([
        'name'      => 'P',
        'prenoms'   => 'T',
        'email'     => 'pt' . random_int(1000, 9999) . '@test.local',
        'password'  => Hash::make('x'),
        'contact'   => '0',
        'matricule' => 'U' . random_int(100, 999),
        'statut'    => 1,
    ]);
    $user->assignRole('user');
    return Employee::create(array_merge([
        'user_id'      => $user->id,
        'noms'         => 'P', 'prenoms' => 'T',
        'matricule'    => 'PT-' . random_int(1000, 9999),
        'email'        => $user->email,
        'salaire_base' => 500000,
        'type_contrat' => 'CDI',
        'date_embauche' => now()->subYear(),
        'statut'       => 1,
    ], $attrs));
}

// ─── Service ────────────────────────────────────────────────

it('détecte une IP intranet via CIDR', function () {
    $s = new PointageMethodService();
    expect($s->isIntranetIp('203.0.113.42'))->toBeTrue();
    expect($s->isIntranetIp('203.0.113.255'))->toBeTrue();
    expect($s->isIntranetIp('8.8.8.8'))->toBeFalse();
});

it('mode "intranet" si IP whitelistée, sinon "teletravail"', function () {
    $s = new PointageMethodService();
    expect($s->detecterMode('203.0.113.10'))->toBe('intranet');
    expect($s->detecterMode('1.2.3.4'))->toBe('teletravail');
});

it('génère un token unique de 64 chars hex', function () {
    $emp = makeEmpAvecUser();
    $s = new PointageMethodService();
    $token = $s->genererToken($emp);
    expect($token)->toHaveLength(64);
    expect($token)->toMatch('/^[0-9a-f]+$/');
    // Idempotent : second appel retourne le même token
    expect($s->genererToken($emp))->toBe($token);
    // Avec regenerer=true → nouveau token
    expect($s->genererToken($emp, regenerer: true))->not->toBe($token);
});

// ─── Mode QR Code ───────────────────────────────────────────

it('mode QR : scanner un token valide ouvre la page de confirmation', function () {
    $emp = makeEmpAvecUser();
    $s = new PointageMethodService();
    $token = $s->genererToken($emp);

    $this->get("/p/qr/$token")
        ->assertOk()
        ->assertSee($emp->noms)
        ->assertSee($emp->prenoms);
});

it('mode QR : token invalide retourne 404', function () {
    $this->get('/p/qr/invalid-token-12345')->assertNotFound();
});

it('mode QR : confirmation crée le pointage et marque mode_pointage=qr_code', function () {
    $emp = makeEmpAvecUser();
    $s = new PointageMethodService();
    $token = $s->genererToken($emp);

    $this->post("/p/qr/$token")->assertOk();

    $p = Pointage::where('employee_id', $emp->id)->where('date', now()->toDateString())->first();
    expect($p)->not->toBeNull();
    expect($p->mode_pointage)->toBe('qr_code');
    expect($p->statut)->toBe(1); // QR = validé immédiatement
    expect($p->requires_validation_n1)->toBeFalse();
});

it('mode QR : 2e scan le même jour met heure_sortie et calcule les heures', function () {
    $emp = makeEmpAvecUser();
    $s = new PointageMethodService();
    $token = $s->genererToken($emp);

    // 1er scan = entrée
    $this->post("/p/qr/$token")->assertOk();
    $p1 = Pointage::where('employee_id', $emp->id)->first();
    expect($p1->heure_entree)->not->toBeNull();
    expect($p1->heure_sortie)->toBeNull();

    // 2e scan = sortie
    $this->post("/p/qr/$token")->assertOk();
    $p2 = Pointage::where('employee_id', $emp->id)->first();
    expect($p2->heure_sortie)->not->toBeNull();
});

// ─── Mode Intranet ───────────────────────────────────────────

it('mode intranet : self-service depuis IP whitelistée → validé immédiatement', function () {
    $emp = makeEmpAvecUser();
    $this->actingAs($emp->user);

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])
         ->post('/pointage')->assertRedirect();

    $p = Pointage::where('employee_id', $emp->id)->first();
    expect($p)->not->toBeNull();
    expect($p->mode_pointage)->toBe('intranet');
    expect($p->statut)->toBe(1);
    expect($p->requires_validation_n1)->toBeFalse();
    expect($p->ip_address)->toBe('203.0.113.50');
});

// ─── Mode Télétravail ────────────────────────────────────────

it('mode télétravail : self-service depuis IP non whitelistée → en attente N+1', function () {
    $emp = makeEmpAvecUser();
    $this->actingAs($emp->user);

    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
         ->post('/pointage')->assertRedirect();

    $p = Pointage::where('employee_id', $emp->id)->first();
    expect($p)->not->toBeNull();
    expect($p->mode_pointage)->toBe('teletravail');
    expect($p->statut)->toBe(0); // brouillon
    expect($p->requires_validation_n1)->toBeTrue();
    expect($p->ip_address)->toBe('8.8.8.8');
});

// ─── Validation N+1 ──────────────────────────────────────────

it('N+1 peut approuver un pointage télétravail de son subordonné', function () {
    $manager = makeEmpAvecUser(['noms' => 'Manager', 'matricule' => 'MGR-1']);
    $subordonne = makeEmpAvecUser(['noms' => 'Sub', 'matricule' => 'SUB-1', 'superieur_hierarchique' => $manager->id]);

    // Sub fait un pointage télétravail
    $p = Pointage::create([
        'employee_id'            => $subordonne->id,
        'date'                   => now()->toDateString(),
        'heure_entree'           => '09:00:00',
        'mode_pointage'          => 'teletravail',
        'statut'                 => 0,
        'requires_validation_n1' => true,
        'ip_address'             => '1.2.3.4',
    ]);

    // Manager se connecte et approuve
    $this->actingAs($manager->user);
    $this->post("/pointage/validation-n1/{$p->id}", [
        'decision'    => 'approuve',
        'commentaire' => 'OK validé',
    ])->assertRedirect();

    $p->refresh();
    expect($p->statut)->toBe(1);
    expect($p->requires_validation_n1)->toBeFalse();
    expect($p->validation_n1_decision)->toBe('approuve');
    expect($p->validation_n1_par)->toBe($manager->user->id);
});

it('N+1 peut rejeter un pointage avec motif', function () {
    $manager = makeEmpAvecUser(['noms' => 'M', 'matricule' => 'MGR-R']);
    $sub = makeEmpAvecUser(['noms' => 'S', 'matricule' => 'SUB-R', 'superieur_hierarchique' => $manager->id]);
    $p = Pointage::create([
        'employee_id'            => $sub->id,
        'date'                   => now()->toDateString(),
        'mode_pointage'          => 'teletravail',
        'statut'                 => 0,
        'requires_validation_n1' => true,
    ]);

    $this->actingAs($manager->user);
    $this->post("/pointage/validation-n1/{$p->id}", [
        'decision'    => 'rejete',
        'commentaire' => 'Pas autorisé ce jour-là',
    ])->assertRedirect();

    $p->refresh();
    expect($p->validation_n1_decision)->toBe('rejete');
    expect($p->statut)->toBe(0); // pas validé pour la paie
});

it('un employé ne peut pas valider le pointage d\'un collègue non subordonné', function () {
    $manager = makeEmpAvecUser(['noms' => 'M', 'matricule' => 'MGR-X']);
    $sub = makeEmpAvecUser(['noms' => 'Pas son sub', 'matricule' => 'OUT-1']); // pas de hiérarchique
    $autre = makeEmpAvecUser(['noms' => 'Autre user', 'matricule' => 'OUT-2']);
    $p = Pointage::create([
        'employee_id'            => $sub->id,
        'date'                   => now()->toDateString(),
        'mode_pointage'          => 'teletravail',
        'statut'                 => 0,
        'requires_validation_n1' => true,
    ]);

    // L'employé "autre" essaie de valider — refus 403
    $this->actingAs($autre->user);
    $this->post("/pointage/validation-n1/{$p->id}", ['decision' => 'approuve'])->assertForbidden();
});

it('super-admin peut valider n\'importe quel pointage', function () {
    actingAsSuperAdmin();
    $emp = makeEmpAvecUser();
    $p = Pointage::create([
        'employee_id'            => $emp->id,
        'date'                   => now()->toDateString(),
        'mode_pointage'          => 'teletravail',
        'statut'                 => 0,
        'requires_validation_n1' => true,
    ]);
    $this->post("/pointage/validation-n1/{$p->id}", ['decision' => 'approuve'])->assertRedirect();
    expect($p->fresh()->statut)->toBe(1);
});

// ─── QR génération PNG ──────────────────────────────────────

it('admin peut télécharger le PNG du QR code', function () {
    actingAsSuperAdmin();
    $emp = makeEmpAvecUser();
    $resp = $this->get("/rh/pointages/qr/{$emp->id}/image");
    $resp->assertOk();
    expect($resp->headers->get('Content-Type'))->toContain('image/png');
    // Signature PNG : commence par les 8 octets magiques 89 50 4E 47 0D 0A 1A 0A
    expect(substr($resp->getContent(), 0, 8))->toBe("\x89PNG\r\n\x1A\n");
});

it('admin peut régénérer le token (l\'ancien devient invalide)', function () {
    actingAsSuperAdmin();
    $emp = makeEmpAvecUser();
    $service = new PointageMethodService();
    $oldToken = $service->genererToken($emp);

    $this->post("/rh/pointages/qr/{$emp->id}/regenerer")->assertRedirect();

    $emp->refresh();
    expect($emp->pointage_token)->not->toBe($oldToken);
    // L'ancien QR ne marche plus
    $this->get("/p/qr/$oldToken")->assertNotFound();
});

// ─── Rate limit anti-abuse QR ────────────────────────────────

it('rate limit : 6e scan QR dans la même minute est refusé', function () {
    $emp = makeEmpAvecUser();
    $token = (new PointageMethodService())->genererToken($emp);

    for ($i = 1; $i <= 5; $i++) {
        $this->post("/p/qr/$token")->assertOk();
    }
    // 6e tentative
    $resp = $this->post("/p/qr/$token");
    $resp->assertOk();
    $resp->assertSee('Trop de tentatives');
});
