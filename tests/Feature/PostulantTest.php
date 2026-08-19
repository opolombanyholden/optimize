<?php

use App\Models\Postulant;
use App\Models\Profil;
use App\Models\Recrutement;

beforeEach(function () {
    seedRoles();
});

function makeRecrutementAvecProfil(): array
{
    $r = Recrutement::create([
        'label'  => 'Dev Backend 2026',
        'debut'  => now()->subWeek(),
        'fin'    => now()->addMonth(),
        'statut' => 0, // ouvert
    ]);
    $p = Profil::create([
        'label'          => 'Senior PHP/Laravel',
        'recrutement_id' => $r->id,
        'statut'         => 1,
    ]);
    return [$r, $p];
}

it('liste des candidatures', function () {
    actingAsSuperAdmin();
    [$r, $p] = makeRecrutementAvecProfil();
    Postulant::create([
        'noms' => 'Dupont', 'prenoms' => 'Jean',
        'email' => 'jean@test.local',
        'age' => '28', 'date_naissance' => '1998-01-01',
        'profil_id' => $p->id, 'recrutement_id' => $r->id,
    ]);
    $this->get('/rh/postulants')->assertOk()->assertSee('Dupont');
});

it('création d\'une candidature', function () {
    actingAsSuperAdmin();
    [$r, $p] = makeRecrutementAvecProfil();

    $this->post('/rh/postulants', [
        'noms' => 'Mbeng', 'prenoms' => 'Paul',
        'email' => 'paul@test.local',
        'contact' => '0600000000',
        'profil_id' => $p->id,
        'recrutement_id' => $r->id,
    ])->assertRedirect();

    expect(Postulant::where('email', 'paul@test.local')->exists())->toBeTrue();
});

it('refuse une candidature sans profil', function () {
    actingAsSuperAdmin();
    $this->post('/rh/postulants', [
        'noms' => 'X', 'prenoms' => 'Y',
        'email' => 'xy@test.local',
    ])->assertSessionHasErrors('profil_id');
});

it('change le statut via le workflow', function () {
    actingAsSuperAdmin();
    [$r, $p] = makeRecrutementAvecProfil();
    $post = Postulant::create([
        'noms' => 'A', 'prenoms' => 'B', 'email' => 'ab@test.local',
        'age' => '', 'date_naissance' => '',
        'profil_id' => $p->id, 'recrutement_id' => $r->id, 'statut' => 0,
    ]);

    $this->post("/rh/postulants/{$post->id}/statut", ['statut' => 2])
         ->assertRedirect();

    expect($post->fresh()->statut)->toBe(2);
});

it('embauche refuse si le statut n\'est pas Retenu', function () {
    actingAsSuperAdmin();
    [$r, $p] = makeRecrutementAvecProfil();
    $post = Postulant::create([
        'noms' => 'C', 'prenoms' => 'D', 'email' => 'cd@test.local',
        'age' => '', 'date_naissance' => '',
        'profil_id' => $p->id, 'recrutement_id' => $r->id, 'statut' => 0, // Nouveau
    ]);

    $resp = $this->post("/rh/postulants/{$post->id}/embaucher", [
        'matricule' => 'EMB-001',
        'date_embauche' => '2026-05-01',
        'type_contrat' => 'CDI',
        'salaire_base' => 500000,
    ]);
    $resp->assertSessionHas('error');
    expect($post->fresh()->embauche)->toBeNull();
});

it('embauche d\'un candidat retenu crée Employee + User + Embauche', function () {
    actingAsSuperAdmin();
    [$r, $p] = makeRecrutementAvecProfil();
    $post = Postulant::create([
        'noms' => 'Embauchable', 'prenoms' => 'Test',
        'email' => 'embauche@test.local',
        'contact' => '0611111111',
        'age' => '30', 'date_naissance' => '1996-01-01',
        'profil_id' => $p->id, 'recrutement_id' => $r->id,
        'statut' => 2, // Retenu
    ]);

    $this->post("/rh/postulants/{$post->id}/embaucher", [
        'matricule'      => 'EMB-100',
        'date_embauche'  => '2026-05-01',
        'type_contrat'   => 'CDI',
        'salaire_base'   => 750000,
        'poste'          => 'Développeur Senior',
        'departement'    => 'IT',
        'commentaire'    => 'Embauche post-entretien validée',
    ])->assertRedirect();

    // Vérifications
    $emp = \App\Models\Employee::where('matricule', 'EMB-100')->first();
    expect($emp)->not->toBeNull();
    expect((float) $emp->salaire_base)->toBe(750000.0);
    expect($emp->user)->not->toBeNull();
    expect($emp->user->must_change_password)->toBeTrue();

    $emb = \App\Models\Embauche::where('postulant_id', $post->id)->first();
    expect($emb)->not->toBeNull();
    expect($emb->employee_id)->toBe($emp->id);

    // Audit log
    $log = \Spatie\Activitylog\Models\Activity::where('log_name', 'embauche')
        ->where('subject_id', $emp->id)->latest()->first();
    expect($log)->not->toBeNull();
});

it('refuse double embauche du même postulant', function () {
    actingAsSuperAdmin();
    [$r, $p] = makeRecrutementAvecProfil();
    $post = Postulant::create([
        'noms' => 'Double', 'prenoms' => 'Embauche', 'email' => 'd@test.local',
        'age' => '', 'date_naissance' => '',
        'profil_id' => $p->id, 'recrutement_id' => $r->id, 'statut' => 2,
    ]);

    $this->post("/rh/postulants/{$post->id}/embaucher", [
        'matricule' => 'DBL-001', 'date_embauche' => '2026-05-01',
        'type_contrat' => 'CDI', 'salaire_base' => 500000,
    ])->assertRedirect();

    // 2e tentative
    $this->post("/rh/postulants/{$post->id}/embaucher", [
        'matricule' => 'DBL-002', 'date_embauche' => '2026-05-01',
        'type_contrat' => 'CDI', 'salaire_base' => 500000,
    ])->assertSessionHas('error');
});

it('refuse suppression d\'un postulant embauché', function () {
    actingAsSuperAdmin();
    [$r, $p] = makeRecrutementAvecProfil();
    $post = Postulant::create([
        'noms' => 'Protege', 'prenoms' => 'Embauche', 'email' => 'pr@test.local',
        'age' => '', 'date_naissance' => '',
        'profil_id' => $p->id, 'recrutement_id' => $r->id, 'statut' => 2,
    ]);
    $this->post("/rh/postulants/{$post->id}/embaucher", [
        'matricule' => 'PROT-001', 'date_embauche' => '2026-05-01',
        'type_contrat' => 'CDI', 'salaire_base' => 500000,
    ])->assertRedirect();

    $this->delete("/rh/postulants/{$post->id}")->assertSessionHas('error');
    expect(Postulant::find($post->id))->not->toBeNull();
});

it('endpoint AJAX profils par recrutement', function () {
    actingAsSuperAdmin();
    [$r, $p] = makeRecrutementAvecProfil();
    Profil::create(['label' => 'Profil 2', 'recrutement_id' => $r->id, 'statut' => 1]);

    $resp = $this->get("/rh/recrutements/{$r->id}/profils");
    $resp->assertOk();
    $data = $resp->json();
    expect($data)->toHaveCount(2);
    expect($data[0]['label'])->toBeString();
});

it('affiche le formulaire de création (régression route order)', function () {
    actingAsSuperAdmin();
    makeRecrutementAvecProfil();
    $this->get('/rh/postulants/create')->assertOk()->assertSee('Nouvelle candidature');
});
