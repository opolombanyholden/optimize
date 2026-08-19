<?php

use App\Models\EchantillonPaie;
use App\Models\Employee;

beforeEach(function () {
    seedRoles();
});

function makeEch(array $attrs = []): EchantillonPaie
{
    return EchantillonPaie::create(array_merge([
        'code'    => 'ECH-' . random_int(1000, 9999),
        'libelle' => 'Test Échantillon',
        'statut'  => true,
    ], $attrs));
}

function makeEmpForEch(array $attrs = []): Employee
{
    return Employee::create(array_merge([
        'noms'         => 'Test',
        'prenoms'      => 'Echantillon',
        'matricule'    => 'EE-' . random_int(1000, 9999),
        'email'        => 'ee' . random_int(1000, 9999) . '@test.local',
        'salaire_base' => 500000,
        'departement'  => 'Commercial',
        'type_contrat' => 'CDI',
        'statut'       => 1,
    ], $attrs));
}

it('liste les échantillons', function () {
    actingAsSuperAdmin();
    makeEch(['libelle' => 'Force de vente']);
    $this->get('/rh/echantillons-paie')->assertOk()->assertSee('Force de vente');
});

it('affiche le formulaire de création (régression : route order create vs {id})', function () {
    actingAsSuperAdmin();
    // S'il existait un échantillon ayant pour id "create" Laravel l'intercepterait — c'est impossible
    // mais l'ordre des routes est aussi crucial : create DOIT être déclaré avant {echantillons_paie}.
    $this->get('/rh/echantillons-paie/create')->assertOk()->assertSee('Nouvel échantillon');
});

it('crée un échantillon avec employés', function () {
    actingAsSuperAdmin();
    $e1 = makeEmpForEch();
    $e2 = makeEmpForEch();

    $this->post('/rh/echantillons-paie', [
        'libelle'      => 'Commerciaux Libreville',
        'description'  => 'Équipe terrain',
        'couleur'      => '#16A34A',
        'icone'        => 'fa-handshake',
        'statut'       => 1,
        'employee_ids' => [$e1->id, $e2->id],
    ])->assertRedirect('/rh/echantillons-paie');

    $ech = EchantillonPaie::where('libelle', 'Commerciaux Libreville')->first();
    expect($ech)->not->toBeNull();
    expect($ech->code)->toStartWith('ECH-');
    expect($ech->employes()->count())->toBe(2);
});

it('refuse un échantillon sans employés', function () {
    actingAsSuperAdmin();
    $this->post('/rh/echantillons-paie', [
        'libelle'      => 'Vide',
        'employee_ids' => [],
    ])->assertSessionHasErrors('employee_ids');
});

it('modifie la liste d\'employés via sync (PUT)', function () {
    actingAsSuperAdmin();
    $emps = collect(range(1, 3))->map(fn() => makeEmpForEch());
    $ech = makeEch();
    $ech->employes()->attach([$emps[0]->id, $emps[1]->id]);

    $this->put("/rh/echantillons-paie/{$ech->id}", [
        'libelle'      => $ech->libelle,
        'employee_ids' => [$emps[1]->id, $emps[2]->id],
    ])->assertRedirect();

    $ids = $ech->employes()->pluck('employees.id')->sort()->values()->all();
    expect($ids)->toEqual([$emps[1]->id, $emps[2]->id]);
});

it('refuse un code dupliqué', function () {
    actingAsSuperAdmin();
    $e = makeEmpForEch();
    makeEch(['code' => 'ECH-UNIQUE']);

    $this->post('/rh/echantillons-paie', [
        'libelle'      => 'Doublon',
        'code'         => 'ECH-UNIQUE',
        'employee_ids' => [$e->id],
    ])->assertSessionHasErrors('code');
});

it('un user simple peut consulter mais pas créer un échantillon', function () {
    $e = makeEmpForEch();
    actingAsUser();
    // read:paie est implicite pour les users (rôle user a tous les read:*)
    $this->get('/rh/echantillons-paie')->assertOk();
    // mais pas create:paie
    $this->post('/rh/echantillons-paie', [
        'libelle' => 'Tentative', 'employee_ids' => [$e->id],
    ])->assertForbidden();
});

it('création d\'une campagne via mode par_echantillon utilise les employés du groupe', function () {
    actingAsSuperAdmin();
    $emps = collect(range(1, 3))->map(fn() => makeEmpForEch());
    $emp_inactif = makeEmpForEch(['statut' => 0]); // doit être exclu
    $ech = makeEch(['libelle' => 'Test Group']);
    $ech->employes()->attach($emps->push($emp_inactif)->pluck('id')->all());

    $this->post('/rh/campagnes-paie', [
        'libelle'        => 'Campagne via échantillon',
        'annee'          => 2026,
        'mois'           => 5,
        'periodicite'    => 'mensuelle',
        'mode_selection' => 'par_echantillon',
        'echantillon_id' => $ech->id,
    ])->assertRedirect();

    $camp = \App\Models\CampagnePaie::where('libelle', 'Campagne via échantillon')->first();
    expect($camp)->not->toBeNull();
    // 3 actifs (l'inactif exclu)
    expect($camp->employes()->count())->toBe(3);
});

it('mode par_echantillon exige echantillon_id', function () {
    actingAsSuperAdmin();
    $this->post('/rh/campagnes-paie', [
        'libelle'        => 'Test sans ech',
        'annee'          => 2026,
        'mois'           => 5,
        'periodicite'    => 'mensuelle',
        'mode_selection' => 'par_echantillon',
    ])->assertSessionHasErrors('echantillon_id');
});

it('affiche un échantillon avec sa liste d\'employés (show)', function () {
    actingAsSuperAdmin();
    $emp = makeEmpForEch(['noms' => 'Mbeng', 'prenoms' => 'Paul']);
    $ech = makeEch(['libelle' => 'Commerciaux']);
    $ech->employes()->attach($emp->id);

    $resp = $this->get("/rh/echantillons-paie/{$ech->id}");
    $resp->assertOk()
         ->assertSee('Commerciaux')
         ->assertSee('Mbeng');
});

it('lien depuis show vers la création de campagne pré-sélectionne l\'échantillon', function () {
    actingAsSuperAdmin();
    $emp = makeEmpForEch();
    $ech = makeEch();
    $ech->employes()->attach($emp->id);

    $resp = $this->get("/rh/campagnes-paie/create?echantillon={$ech->id}");
    $resp->assertOk();
    // Le radio msEch doit être checké
    $resp->assertSee('id="msEch"', false);
});
