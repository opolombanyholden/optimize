<?php

use App\Models\Exercice;
use App\Models\User;

beforeEach(function () {
    seedRoles();
});

// ─── DASHBOARD ─────────────────────────────────────────────

it('le dashboard finance s\'affiche pour un admin', function () {
    actingAsSuperAdmin();
    $this->get('/finance')
        ->assertOk()
        ->assertSee('Tableau de bord Finance')
        ->assertSee('Exercices')
        ->assertSee('Comptes');
});

it('le dashboard affiche "Aucun exercice en cours" si aucun n\'est statut=2', function () {
    actingAsSuperAdmin();
    Exercice::where('statut', 2)->update(['statut' => 3]);
    $this->get('/finance')
        ->assertOk()
        ->assertSee('Aucun exercice en cours');
});

it('le dashboard affiche le libellé de l\'exercice en cours', function () {
    actingAsSuperAdmin();
    Exercice::where('statut', 2)->update(['statut' => 3]); // clôt tout
    Exercice::create([
        'exercice' => '2026', 'libelle' => 'Exercice 2026 actif',
        'statut'   => 2,
        'datedebut'=> '2026-01-01', 'datefin' => '2026-12-31',
    ]);
    $this->get('/finance')
        ->assertOk()
        ->assertSee('Exercice 2026 actif');
});

it('un user sans permission read:exercice ne peut pas accéder au dashboard', function () {
    $u = User::factory()->create(['statut' => 1]);
    // Pas de rôle assigné = pas de permissions
    $this->actingAs($u)->get('/finance')->assertForbidden();
});

// ─── EXERCICES ─────────────────────────────────────────────

it('liste les exercices', function () {
    actingAsSuperAdmin();
    Exercice::create(['exercice' => 'TEST-X', 'libelle' => 'Mon exercice test', 'statut' => 1]);
    $this->get('/finance/exercices')
        ->assertOk()
        ->assertSee('Mon exercice test');
});

it('crée un exercice planifié et redirige vers la planification budgétaire', function () {
    actingAsSuperAdmin();
    $this->post('/finance/exercices', [
        'libelle'  => 'Exercice 2027',
        'exercice' => '2027',
    ])->assertRedirectContains('/planification');

    $ex = Exercice::where('libelle', 'Exercice 2027')->first();
    expect($ex)->not->toBeNull();
    expect($ex->statut)->toBe(1);
    expect((int) $ex->validation_statut)->toBe(0);
});

it('valide un exercice soumis (statut 1 + validation 1 → statut 2 + validation 2)', function () {
    actingAsSuperAdmin();
    Exercice::where('statut', 2)->update(['statut' => 3]);
    // Exercice en planification soumis pour validation
    $ex = Exercice::create([
        'exercice' => 'V', 'libelle' => 'À valider',
        'statut' => 1, 'validation_statut' => 1,
    ]);
    // Au moins une ligne budgétaire (pré-requis : pas vide)
    \App\Models\BudgetLigne::create([
        'id_budgetligne' => 'BL-V', 'id_exercicebudgetaire' => $ex->id,
        'dotation_etat' => 100_000, 'isvalide' => 0, 'id_user' => auth()->id(),
    ]);

    $this->post("/finance/exercices/{$ex->id}/valider")->assertRedirect();
    expect($ex->fresh()->statut)->toBe(2);
    expect((int) $ex->fresh()->validation_statut)->toBe(2);
});

it('refuse de valider un exercice non-soumis', function () {
    actingAsSuperAdmin();
    Exercice::where('statut', 2)->update(['statut' => 3]);
    $ex = Exercice::create([
        'exercice' => 'NS', 'libelle' => 'Non soumis',
        'statut' => 1, 'validation_statut' => 0, // pas encore soumis
    ]);
    $this->post("/finance/exercices/{$ex->id}/valider")
        ->assertRedirect()
        ->assertSessionHas('error');
    expect($ex->fresh()->statut)->toBe(1);
});

it('refuse de valider si un autre exercice est déjà en cours', function () {
    actingAsSuperAdmin();
    Exercice::where('statut', 2)->update(['statut' => 3]);
    // L'exercice soumis pour validation
    $ex = Exercice::create([
        'exercice' => 'V2', 'libelle' => 'Tentative',
        'statut' => 1, 'validation_statut' => 1,
    ]);
    \App\Models\BudgetLigne::create([
        'id_budgetligne' => 'BL-V2', 'id_exercicebudgetaire' => $ex->id,
        'dotation_etat' => 100, 'isvalide' => 0, 'id_user' => auth()->id(),
    ]);
    // Conflit créé APRÈS exPlanif pour éviter le reset au statut 3
    Exercice::create(['exercice' => 'EC', 'libelle' => 'Déjà en cours', 'statut' => 2]);

    $this->post("/finance/exercices/{$ex->id}/valider")
        ->assertRedirect()
        ->assertSessionHas('error');
    expect($ex->fresh()->statut)->toBe(1);
});

it('clôture un exercice en cours (statut 2 → 3)', function () {
    actingAsSuperAdmin();
    Exercice::where('statut', 2)->update(['statut' => 3]);
    $ex = Exercice::create(['exercice' => 'C', 'libelle' => 'À clôturer', 'statut' => 2]);

    $this->post("/finance/exercices/{$ex->id}/cloturer")->assertRedirect();
    expect($ex->fresh()->statut)->toBe(3);
});

it('refuse de clôturer un exercice non en cours', function () {
    actingAsSuperAdmin();
    $ex = Exercice::create(['exercice' => 'P', 'libelle' => 'Planifié', 'statut' => 1]);

    $this->post("/finance/exercices/{$ex->id}/cloturer")
        ->assertRedirect()
        ->assertSessionHas('error');
    expect($ex->fresh()->statut)->toBe(1);
});

it('refuse de supprimer un exercice en cours pour un user standard', function () {
    $u = \App\Models\User::factory()->create();
    $u->givePermissionTo(['read:exercice', 'delete:exercice']);
    $this->actingAs($u);
    Exercice::where('statut', 2)->update(['statut' => 3]);
    $ex = Exercice::create(['exercice' => 'D', 'libelle' => 'En cours', 'statut' => 2]);

    $this->delete("/finance/exercices/{$ex->id}")
        ->assertRedirect()
        ->assertSessionHas('error');
    expect(Exercice::find($ex->id))->not->toBeNull();
});

it('le super-admin peut forcer la suppression dun exercice en cours', function () {
    actingAsSuperAdmin();
    Exercice::where('statut', 2)->update(['statut' => 3]);
    $ex = Exercice::create(['exercice' => 'D2', 'libelle' => 'En cours', 'statut' => 2]);

    $this->delete("/finance/exercices/{$ex->id}")
        ->assertRedirect(route('finance.exercices.index'));
    expect(Exercice::find($ex->id))->toBeNull();
});

// ─── PERMISSIONS ───────────────────────────────────────────

it('un user simple peut voir les listes mais pas créer/modifier/supprimer', function () {
    actingAsUser();
    // read OK
    $this->get('/finance/exercices')->assertOk();
    $this->get('/finance/budgets')->assertOk();
    $this->get('/finance/comptes')->assertOk();
    $this->get('/finance/grand-livre')->assertOk();
    // create refusé
    $this->get('/finance/exercices/create')->assertForbidden();
    $this->get('/finance/budgets/create')->assertForbidden();
    $this->get('/finance/comptes/create')->assertForbidden();
});
