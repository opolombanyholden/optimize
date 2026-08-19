<?php

use App\Models\Exercice;

beforeEach(function () {
    seedRoles();
});

function mkExVerrou(int $statut, int $validation = 0): Exercice
{
    // Un seul exercice en exécution possible : basculer tout autre en clôturé
    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    return Exercice::create([
        'exercice'          => 'EX-V-' . random_int(1000, 9999),
        'libelle'           => 'Exercice verrouillage',
        'statut'            => $statut,
        'validation_statut' => $validation,
        'datedebut'         => '2026-01-01',
        'datefin'           => '2026-12-31',
    ]);
}

// ═════════ Règles métier (accessors) ═════════

it('un exercice en planification brouillon est modifiable et supprimable', function () {
    $ex = mkExVerrou(Exercice::STATUT_PLANIFICATION, 0);
    expect($ex->peutEtreModifie())->toBeTrue();
    expect($ex->peutEtreSupprime())->toBeTrue();
    expect($ex->peutEtreAnnule())->toBeFalse();
});

it('un exercice soumis pour validation nest plus modifiable', function () {
    $ex = mkExVerrou(Exercice::STATUT_PLANIFICATION, 1);
    expect($ex->peutEtreModifie())->toBeFalse();
    expect($ex->peutEtreSupprime())->toBeFalse();
});

it('un exercice en exécution ne peut pas être modifié ni supprimé, seulement clôturé ou annulé', function () {
    $ex = mkExVerrou(Exercice::STATUT_EN_EXECUTION, 2);
    expect($ex->peutEtreModifie())->toBeFalse();
    expect($ex->peutEtreSupprime())->toBeFalse();
    expect($ex->peut_cloturer)->toBeTrue();
    expect($ex->peutEtreAnnule())->toBeTrue();
});

it('un exercice clôturé est en lecture seule', function () {
    $ex = mkExVerrou(Exercice::STATUT_CLOTURE, 2);
    expect($ex->peutEtreModifie())->toBeFalse();
    expect($ex->peutEtreSupprime())->toBeFalse();
    expect($ex->peutEtreAnnule())->toBeFalse();
});

it('un exercice annulé est en lecture seule', function () {
    $ex = mkExVerrou(Exercice::STATUT_ANNULE, 2);
    expect($ex->peutEtreModifie())->toBeFalse();
    expect($ex->peutEtreSupprime())->toBeFalse();
    expect($ex->peutEtreAnnule())->toBeFalse();
});

// ═════════ Contrôleur : rejette PUT/DELETE en exécution pour user standard ═════════

function actingAsUserStandardExercice(): \App\Models\User
{
    $u = \App\Models\User::factory()->create();
    $u->givePermissionTo(['read:exercice', 'update:exercice', 'delete:exercice', 'validate:exercice']);
    test()->actingAs($u);
    return $u;
}

it('rejette PUT sur un exercice en exécution pour user standard', function () {
    actingAsUserStandardExercice();
    $ex = mkExVerrou(Exercice::STATUT_EN_EXECUTION, 2);
    $r = $this->put("/finance/exercices/{$ex->id}", ['libelle' => 'Modif interdite']);
    $r->assertRedirect();
    $r->assertSessionHas('error');
    expect($ex->fresh()->libelle)->toBe('Exercice verrouillage');
});

it('rejette DELETE sur un exercice en exécution pour user standard', function () {
    actingAsUserStandardExercice();
    $ex = mkExVerrou(Exercice::STATUT_EN_EXECUTION, 2);
    $r = $this->delete("/finance/exercices/{$ex->id}");
    $r->assertRedirect();
    $r->assertSessionHas('error');
    expect(Exercice::find($ex->id))->not->toBeNull();
});

it('rejette DELETE sur un exercice clôturé pour user standard', function () {
    actingAsUserStandardExercice();
    $ex = mkExVerrou(Exercice::STATUT_CLOTURE, 2);
    $this->delete("/finance/exercices/{$ex->id}")->assertSessionHas('error');
    expect(Exercice::find($ex->id))->not->toBeNull();
});

it('rejette DELETE sur un exercice annulé pour user standard', function () {
    actingAsUserStandardExercice();
    $ex = mkExVerrou(Exercice::STATUT_ANNULE, 2);
    $this->delete("/finance/exercices/{$ex->id}")->assertSessionHas('error');
    expect(Exercice::find($ex->id))->not->toBeNull();
});

it('autorise DELETE sur un exercice brouillon sans lignes', function () {
    actingAsSuperAdmin();
    $ex = mkExVerrou(Exercice::STATUT_PLANIFICATION, 0);
    $this->delete("/finance/exercices/{$ex->id}")
        ->assertRedirect(route('finance.exercices.index'));
    expect(Exercice::find($ex->id))->toBeNull();
});

// ═════════ Annulation ═════════

it('annule un exercice en exécution avec motif valide', function () {
    actingAsSuperAdmin();
    $ex = mkExVerrou(Exercice::STATUT_EN_EXECUTION, 2);
    $r = $this->post("/finance/exercices/{$ex->id}/annuler", [
        'motif_annulation' => 'Décision top management suite au changement de stratégie annuelle.',
    ]);
    $r->assertRedirect();
    $ex->refresh();
    expect((int) $ex->statut)->toBe(Exercice::STATUT_ANNULE);
    expect($ex->motif_annulation)->toContain('changement de stratégie');
    expect($ex->annule_par)->not->toBeNull();
    expect($ex->annule_at)->not->toBeNull();
});

it('rejette annulation sans motif valide (moins de 10 caractères)', function () {
    actingAsSuperAdmin();
    $ex = mkExVerrou(Exercice::STATUT_EN_EXECUTION, 2);
    $r = $this->post("/finance/exercices/{$ex->id}/annuler", ['motif_annulation' => 'court']);
    $r->assertSessionHasErrors('motif_annulation');
    expect((int) $ex->fresh()->statut)->toBe(Exercice::STATUT_EN_EXECUTION);
});

it('rejette annulation sur un exercice non actif (planification)', function () {
    actingAsSuperAdmin();
    $ex = mkExVerrou(Exercice::STATUT_PLANIFICATION, 0);
    $r = $this->post("/finance/exercices/{$ex->id}/annuler", [
        'motif_annulation' => 'Motif suffisamment long pour la validation.',
    ]);
    $r->assertSessionHas('error');
    expect((int) $ex->fresh()->statut)->toBe(Exercice::STATUT_PLANIFICATION);
});

// ═════════ Super-admin : override total (le seul rôle qui peut tout) ═════════

it('le super-admin peut supprimer un exercice en exécution (bypass)', function () {
    actingAsSuperAdmin();
    $ex = mkExVerrou(Exercice::STATUT_EN_EXECUTION, 2);
    $r = $this->delete("/finance/exercices/{$ex->id}");
    $r->assertRedirect(route('finance.exercices.index'));
    expect(Exercice::find($ex->id))->toBeNull();
});

it('le super-admin peut supprimer un exercice clôturé (bypass)', function () {
    actingAsSuperAdmin();
    $ex = mkExVerrou(Exercice::STATUT_CLOTURE, 2);
    $this->delete("/finance/exercices/{$ex->id}")->assertRedirect();
    expect(Exercice::find($ex->id))->toBeNull();
});

it('le super-admin peut supprimer un exercice annulé (bypass)', function () {
    actingAsSuperAdmin();
    $ex = mkExVerrou(Exercice::STATUT_ANNULE, 2);
    $this->delete("/finance/exercices/{$ex->id}")->assertRedirect();
    expect(Exercice::find($ex->id))->toBeNull();
});

it('le super-admin peut modifier un exercice verrouillé (bypass)', function () {
    actingAsSuperAdmin();
    $ex = mkExVerrou(Exercice::STATUT_EN_EXECUTION, 2);
    $this->put("/finance/exercices/{$ex->id}", ['libelle' => 'Modif super-admin'])
        ->assertRedirect();
    expect($ex->fresh()->libelle)->toBe('Modif super-admin');
});

it('la policy accorde tout au super-admin', function () {
    $admin = actingAsSuperAdmin();
    $ex = mkExVerrou(Exercice::STATUT_EN_EXECUTION, 2);
    expect($admin->can('delete', $ex))->toBeTrue();
    expect($admin->can('update', $ex))->toBeTrue();
    expect($admin->can('annuler', $ex))->toBeTrue();
});

it('un utilisateur non-super-admin reste bloqué par les règles métier', function () {
    // Non-super-admin : la policy refuse update/delete même avec les permissions
    $user = \App\Models\User::factory()->create();
    $user->givePermissionTo(['read:exercice', 'update:exercice', 'delete:exercice', 'validate:exercice']);
    $this->actingAs($user);
    $ex = mkExVerrou(Exercice::STATUT_EN_EXECUTION, 2);

    expect($user->can('delete', $ex))->toBeFalse();
    expect($user->can('update', $ex))->toBeFalse();
    expect($user->can('annuler', $ex))->toBeTrue(); // annuler autorisé en exécution
});
