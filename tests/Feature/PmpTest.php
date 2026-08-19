<?php

use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetPhase;
use App\Models\Intranet\Statut;
use App\Models\Intranet\Tache;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    // Statuts requis par les modèles (Statut::find(1) etc.)
    Statut::firstOrCreate(['libelle' => 'Non démarré'], ['couleur' => '#64748b']);
    Statut::firstOrCreate(['libelle' => 'En cours'],    ['couleur' => '#4F46E5']);
    Statut::firstOrCreate(['libelle' => 'Terminé'],     ['couleur' => '#059669']);
});

it('creates a projet with default attributes', function () {
    $admin = User::factory()->create(['statut' => 1]);

    $projet = Projet::create([
        'nom'         => 'Projet de test',
        'code_projet' => 'TEST-001',
        'description' => 'Description test',
        'date_debut'  => now()->toDateString(),
        'date_fin'    => now()->addMonths(6)->toDateString(),
        'statut_id'   => Statut::where('libelle', 'En cours')->value('id'),
        'created_by'  => $admin->id,
        'devise'      => 'XAF',
        'avancement'  => 0,
    ]);

    expect($projet->id)->not->toBeNull();
    expect($projet->nom)->toBe('Projet de test');
    expect($projet->avancement)->toBe(0);
});

it('cascades avancement from tasks to phase to projet', function () {
    $admin = User::factory()->create();
    $statutEnCours = Statut::where('libelle', 'En cours')->first();

    $projet = Projet::create([
        'nom' => 'Cascade Test', 'created_by' => $admin->id,
        'statut_id' => $statutEnCours->id, 'devise' => 'XAF', 'avancement' => 0,
    ]);

    $phase = $projet->phases()->create([
        'nom' => 'Phase 1', 'ordre' => 1, 'ponderation' => 100,
        'created_by' => $admin->id,
    ]);

    // 2 tâches : 50% et 100% → phase = 75%
    $phase->taches()->create([
        'titre' => 'T1', 'projet_id' => $projet->id,
        'statut_id' => $statutEnCours->id, 'avancement' => 50,
        'ponderation' => 50, 'created_by' => $admin->id,
    ]);
    $phase->taches()->create([
        'titre' => 'T2', 'projet_id' => $projet->id,
        'statut_id' => $statutEnCours->id, 'avancement' => 100,
        'ponderation' => 50, 'created_by' => $admin->id,
    ]);

    $phase->refresh();
    expect($phase->avancement_real)->toBe(75);

    $projet->refresh();
    expect($projet->avancement_real)->toBe(75);
});

it('cascades costs from tasks to phase', function () {
    $admin = User::factory()->create();
    $statut = Statut::where('libelle', 'En cours')->first();

    $projet = Projet::create([
        'nom' => 'Cost Test', 'created_by' => $admin->id,
        'statut_id' => $statut->id, 'devise' => 'XAF', 'avancement' => 0,
    ]);
    $phase = $projet->phases()->create([
        'nom' => 'Phase Cost', 'ordre' => 1, 'created_by' => $admin->id,
    ]);
    $tache = $phase->taches()->create([
        'titre' => 'T avec coût', 'projet_id' => $projet->id,
        'statut_id' => $statut->id, 'created_by' => $admin->id,
        'cout_execution' => 100000, 'devise_cout' => 'XAF',
    ]);

    expect($tache->cout_total_estime)->toBe(100000.0);
    expect($phase->cout_estime)->toBe(100000.0);
    expect($projet->cout_total_estime)->toBe(100000.0);
});

it('locks a projet that has phases', function () {
    $admin = User::factory()->create();
    $statut = Statut::where('libelle', 'En cours')->first();

    $projet = Projet::create([
        'nom' => 'Locked', 'created_by' => $admin->id,
        'statut_id' => $statut->id, 'devise' => 'XAF', 'avancement' => 0,
    ]);
    expect($projet->est_verrouille)->toBeFalse();

    $projet->phases()->create([
        'nom' => 'P', 'ordre' => 1, 'created_by' => $admin->id,
    ]);
    $projet->refresh();
    expect($projet->est_verrouille)->toBeTrue();
});

it('locks a phase with tasks', function () {
    $admin = User::factory()->create();
    $statut = Statut::where('libelle', 'En cours')->first();
    $projet = Projet::create([
        'nom' => 'P', 'created_by' => $admin->id,
        'statut_id' => $statut->id, 'devise' => 'XAF', 'avancement' => 0,
    ]);
    $phase = $projet->phases()->create([
        'nom' => 'Ph', 'ordre' => 1, 'created_by' => $admin->id,
    ]);
    expect($phase->est_verrouille)->toBeFalse();

    $phase->taches()->create([
        'titre' => 'T', 'projet_id' => $projet->id,
        'statut_id' => $statut->id, 'created_by' => $admin->id,
    ]);
    $phase->refresh();
    expect($phase->est_verrouille)->toBeTrue();
});

it('super-admin can modify a locked entity', function () {
    $admin = actingAsSuperAdmin();
    $statut = Statut::where('libelle', 'En cours')->first();

    $projet = Projet::create([
        'nom' => 'Locked but super', 'created_by' => $admin->id,
        'statut_id' => $statut->id, 'devise' => 'XAF', 'avancement' => 0,
    ]);
    $projet->phases()->create([
        'nom' => 'P', 'ordre' => 1, 'created_by' => $admin->id,
    ]);
    $projet->refresh();

    expect($projet->est_verrouille)->toBeTrue();
    expect($projet->peutModifier($admin))->toBeTrue();
});

it('regular user cannot modify a locked entity', function () {
    $admin = User::factory()->create();
    $regular = actingAsUser();
    $statut = Statut::where('libelle', 'En cours')->first();

    $projet = Projet::create([
        'nom' => 'Locked', 'created_by' => $admin->id,
        'statut_id' => $statut->id, 'devise' => 'XAF', 'avancement' => 0,
    ]);
    $projet->phases()->create([
        'nom' => 'P', 'ordre' => 1, 'created_by' => $admin->id,
    ]);
    $projet->refresh();

    expect($projet->peutModifier($regular))->toBeFalse();
});

it('renders the projet dashboard', function () {
    actingAsSuperAdmin();
    $response = $this->get('/projet');
    $response->assertOk();
});

it('creates a tache via projet.taches.store endpoint', function () {
    $admin = actingAsSuperAdmin();
    $statut = Statut::where('libelle', 'En cours')->first();
    $projet = Projet::create([
        'nom' => 'P', 'created_by' => $admin->id,
        'statut_id' => $statut->id, 'devise' => 'XAF', 'avancement' => 0,
    ]);
    $phase = $projet->phases()->create([
        'nom' => 'Ph', 'ordre' => 1, 'created_by' => $admin->id,
    ]);

    $response = $this->post("/projet/{$projet->id}/taches", [
        'titre'           => 'Tâche depuis projet',
        'description'     => 'Test',
        'phase_id'        => $phase->id,
        'statut_id'       => $statut->id,
        'avancement'      => 25,
        'heures_estimees' => 8,
    ]);

    $response->assertRedirect();
    $tache = Tache::where('titre', 'Tâche depuis projet')->first();
    expect($tache)->not->toBeNull();
    expect($tache->projet_id)->toBe($projet->id);
    expect($tache->phase_id)->toBe($phase->id);
});

it('rejects tache creation with phase from another projet', function () {
    $admin = actingAsSuperAdmin();
    $statut = Statut::where('libelle', 'En cours')->first();
    $projet1 = Projet::create([
        'nom' => 'P1', 'created_by' => $admin->id,
        'statut_id' => $statut->id, 'devise' => 'XAF', 'avancement' => 0,
    ]);
    $projet2 = Projet::create([
        'nom' => 'P2', 'created_by' => $admin->id,
        'statut_id' => $statut->id, 'devise' => 'XAF', 'avancement' => 0,
    ]);
    $phaseDeP2 = $projet2->phases()->create([
        'nom' => 'Phase P2', 'ordre' => 1, 'created_by' => $admin->id,
    ]);

    $response = $this->post("/projet/{$projet1->id}/taches", [
        'titre'    => 'Mauvaise affectation',
        'phase_id' => $phaseDeP2->id,  // phase de P2 sur projet P1 → invalide
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('phase_id');
    expect(Tache::where('titre', 'Mauvaise affectation')->exists())->toBeFalse();
});

it('renders the wbs page for a projet', function () {
    $admin = actingAsSuperAdmin();
    $statut = Statut::where('libelle', 'En cours')->first();
    $projet = Projet::create([
        'nom' => 'P', 'created_by' => $admin->id,
        'statut_id' => $statut->id, 'devise' => 'XAF', 'avancement' => 0,
    ]);

    $response = $this->get("/projet/{$projet->id}/wbs");
    $response->assertOk();
    $response->assertSee('Work Breakdown Structure', false);
});
