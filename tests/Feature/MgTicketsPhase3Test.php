<?php

use App\Models\Dysfonctionnement;
use App\Models\Intervention;
use App\Models\MgThematique;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->user = User::factory()->create();
    $this->thematique = MgThematique::create([
        'libelle' => 'Informatique', 'couleur' => '#0d6efd', 'actif' => true,
    ]);
});

it('génère un ticket_ref automatique à la création', function () {
    $d = Dysfonctionnement::create([
        'label' => 'Panne PC',
        'statut' => Dysfonctionnement::STATUT_SIGNALE,
        'date_signalement' => now(),
    ]);
    expect($d->ticket_ref)->toStartWith('TCK-');
    expect($d->ticket_ref)->toMatch('/TCK-\d{4}-\d{4}/');
});

it('permet de rattacher une thématique à un dysfonctionnement', function () {
    actingAsSuperAdmin();
    $this->post(route('mg.dysfonctionnements.store'), [
        'label' => 'Écran cassé',
        'thematique_id' => $this->thematique->id,
        'priorite' => 'normale',
    ])->assertRedirect();

    $d = Dysfonctionnement::latest()->first();
    expect($d->thematique_id)->toBe($this->thematique->id);
    expect($d->thematique->libelle)->toBe('Informatique');
});

it('un admin priorise un ticket (priorité admin + moment intervention)', function () {
    actingAsSuperAdmin();
    $d = Dysfonctionnement::create([
        'label' => 'X', 'statut' => Dysfonctionnement::STATUT_SIGNALE, 'date_signalement' => now(),
    ]);
    $this->post(route('mg.dysfonctionnements.prioriser', $d), [
        'priorite_admin' => 'haute',
        'moment_intervention' => '2027-02-01T14:30',
    ])->assertRedirect();

    $d->refresh();
    expect($d->priorite_admin)->toBe('haute');
    expect($d->moment_intervention?->format('Y-m-d H:i'))->toBe('2027-02-01 14:30');
    expect($d->priorise_par)->not->toBeNull();
});

it('intervention terminée avec résolution partielle ne clôture PAS le ticket', function () {
    actingAsSuperAdmin();
    $d = Dysfonctionnement::create([
        'label' => 'X', 'statut' => Dysfonctionnement::STATUT_PRIS_EN_CHARGE, 'date_signalement' => now(),
    ]);
    $i = Intervention::create([
        'label' => 'Test', 'dysfonctionnement_id' => $d->id,
        'statut' => Intervention::STATUT_EN_COURS,
    ]);
    $this->post(route('mg.interventions.terminer', $i), [
        'nature_probleme' => 'Alimentation défectueuse',
        'pistes_solution' => 'Remplacer bloc',
        'solution_appliquee' => 'Remplacement bloc',
        'resultat' => 'Fonctionne mais chauffe',
        'statut_resolution' => Intervention::RESOLUTION_PARTIEL,
    ])->assertRedirect();

    expect($i->fresh()->statut_resolution)->toBe('partiel');
    // Le ticket reste ouvert (STATUT_PRIS_EN_CHARGE, PAS résolu)
    expect($d->fresh()->statut)->toBe(Dysfonctionnement::STATUT_PRIS_EN_CHARGE);
});

it('intervention terminée avec résolution RESOLU clôture le ticket', function () {
    actingAsSuperAdmin();
    $d = Dysfonctionnement::create([
        'label' => 'X', 'statut' => Dysfonctionnement::STATUT_PRIS_EN_CHARGE, 'date_signalement' => now(),
    ]);
    $i = Intervention::create([
        'label' => 'Test', 'dysfonctionnement_id' => $d->id,
        'statut' => Intervention::STATUT_EN_COURS,
    ]);
    $this->post(route('mg.interventions.terminer', $i), [
        'nature_probleme' => 'Alim défectueuse', 'solution_appliquee' => 'Bloc remplacé',
        'statut_resolution' => Intervention::RESOLUTION_RESOLU,
    ])->assertRedirect();

    expect($d->fresh()->statut)->toBe(Dysfonctionnement::STATUT_RESOLU);
});

it('un ticket peut recevoir plusieurs interventions successives avant clôture', function () {
    actingAsSuperAdmin();
    $d = Dysfonctionnement::create([
        'label' => 'Panne récurrente', 'statut' => Dysfonctionnement::STATUT_PRIS_EN_CHARGE, 'date_signalement' => now(),
    ]);
    // 1ère intervention → non résolue
    $i1 = Intervention::create(['label' => 'I1', 'dysfonctionnement_id' => $d->id, 'statut' => Intervention::STATUT_EN_COURS]);
    $this->post(route('mg.interventions.terminer', $i1), ['statut_resolution' => Intervention::RESOLUTION_NON_RESOLU]);
    expect($d->fresh()->statut)->toBe(Dysfonctionnement::STATUT_PRIS_EN_CHARGE); // toujours ouvert

    // 2ème intervention → partielle
    $i2 = Intervention::create(['label' => 'I2', 'dysfonctionnement_id' => $d->id, 'statut' => Intervention::STATUT_EN_COURS]);
    $this->post(route('mg.interventions.terminer', $i2), ['statut_resolution' => Intervention::RESOLUTION_PARTIEL]);
    expect($d->fresh()->statut)->toBe(Dysfonctionnement::STATUT_PRIS_EN_CHARGE); // toujours ouvert

    // 3ème intervention → résolu
    $i3 = Intervention::create(['label' => 'I3', 'dysfonctionnement_id' => $d->id, 'statut' => Intervention::STATUT_EN_COURS]);
    $this->post(route('mg.interventions.terminer', $i3), ['statut_resolution' => Intervention::RESOLUTION_RESOLU]);
    expect($d->fresh()->statut)->toBe(Dysfonctionnement::STATUT_RESOLU);

    expect($d->fresh()->interventions->count())->toBe(3);
});

it('intervention hérite de la thématique via création avec thematique_id', function () {
    actingAsSuperAdmin();
    $this->post(route('mg.interventions.store'), [
        'label' => 'Maintenance',
        'thematique_id' => $this->thematique->id,
        'type_intervention' => 'preventive',
    ])->assertRedirect();

    $i = Intervention::latest()->first();
    expect($i->thematique_id)->toBe($this->thematique->id);
});

it('validation refuse statut_resolution manquant à la clôture', function () {
    actingAsSuperAdmin();
    $i = Intervention::create(['label' => 'X', 'statut' => Intervention::STATUT_EN_COURS]);
    $this->post(route('mg.interventions.terminer', $i), [
        'rapport' => 'fait',
    ])->assertSessionHasErrors('statut_resolution');
});
