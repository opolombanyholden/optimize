<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Ligne;
use App\Models\Titre;

beforeEach(function () {
    seedRoles();
    $this->auteur = \App\Models\User::factory()->create();
    $t = Titre::create(['imputation' => 'T-R', 'libelle' => 'Retrait test', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 82001, 'libelle' => 'Ligne R1', 'nature' => 'depense', 'id_user' => $this->auteur->id]);
    Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 82002, 'libelle' => 'Ligne R2', 'nature' => 'depense', 'id_user' => $this->auteur->id]);
});

function mkExRetrait(): Exercice
{
    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $ex = Exercice::create([
        'exercice' => 'EX-RETRAIT-' . random_int(100, 999),
        'libelle'  => 'Test retrait',
        'statut'   => Exercice::STATUT_PLANIFICATION,
        'validation_statut' => 0,
        'id_user'  => test()->auteur->id,
    ]);
    $ex->initialiserPlanification(test()->auteur->id);
    return $ex;
}

it('retire une ligne de la planification et la remet ensuite', function () {
    actingAsSuperAdmin();
    $ex = mkExRetrait();
    $bl = $ex->budgetLignes()->first();

    $this->post(route('finance.exercices.planification.lignes.retirer', [$ex, $bl]))
        ->assertRedirect()->assertSessionHas('success');
    expect($bl->fresh()->retiree_de_planification)->toBeTrue();

    $this->post(route('finance.exercices.planification.lignes.remettre', [$ex, $bl]))
        ->assertRedirect()->assertSessionHas('success');
    expect($bl->fresh()->retiree_de_planification)->toBeFalse();
});

it('une ligne retirée nest plus dans lindex de la grille', function () {
    actingAsSuperAdmin();
    $ex = mkExRetrait();
    $bl = $ex->budgetLignes()->first();
    $bl->update(['retiree_de_planification' => true]);

    $r = $this->get(route('finance.exercices.planification', $ex));
    $r->assertOk();
    $index = $r->viewData('budgetLignesIndex');
    expect($index->has($bl->id_codeanalytique))->toBeFalse();

    $retirees = $r->viewData('lignesRetirees');
    expect($retirees->pluck('id')->all())->toContain($bl->id);
});

function actingAsUserStandardRetrait(): \App\Models\User
{
    $u = \App\Models\User::factory()->create();
    $u->givePermissionTo(['read:exercice', 'update:exercice']);
    test()->actingAs($u);
    return $u;
}

it('refuse le retrait dune ligne ayant des engagements pour un user standard', function () {
    actingAsUserStandardRetrait();
    $ex = mkExRetrait();
    $bl = $ex->budgetLignes()->first();
    $bl->update(['engagement' => 500_000]);

    $this->post(route('finance.exercices.planification.lignes.retirer', [$ex, $bl]))
        ->assertRedirect()->assertSessionHas('error');
    expect($bl->fresh()->retiree_de_planification)->toBeFalse();
});

it('le super-admin peut forcer le retrait dune ligne avec engagement', function () {
    actingAsSuperAdmin();
    $ex = mkExRetrait();
    $bl = $ex->budgetLignes()->first();
    $bl->update(['engagement' => 500_000]);

    $this->post(route('finance.exercices.planification.lignes.retirer', [$ex, $bl]))
        ->assertRedirect()->assertSessionHas('success');
    expect($bl->fresh()->retiree_de_planification)->toBeTrue();
});

it('bloque le retrait lorsque la planification est verrouillée pour un user standard', function () {
    actingAsUserStandardRetrait();
    $ex = mkExRetrait();
    $ex->update(['validation_statut' => 1]);
    $bl = $ex->budgetLignes()->first();

    $this->post(route('finance.exercices.planification.lignes.retirer', [$ex, $bl]))
        ->assertRedirect()->assertSessionHas('error');
    expect($bl->fresh()->retiree_de_planification)->toBeFalse();
});

it('le super-admin peut forcer le retrait quand la planification est verrouillée', function () {
    actingAsSuperAdmin();
    $ex = mkExRetrait();
    $ex->update(['validation_statut' => 1]);
    $bl = $ex->budgetLignes()->first();

    $this->post(route('finance.exercices.planification.lignes.retirer', [$ex, $bl]))
        ->assertRedirect()->assertSessionHas('success');
    expect($bl->fresh()->retiree_de_planification)->toBeTrue();
});

it('planificationSave ignore une ligne retirée soumise dans le POST', function () {
    actingAsSuperAdmin();
    $ex = mkExRetrait();
    $bl = $ex->budgetLignes()->first();
    $bl->update(['retiree_de_planification' => true]);

    // Un client malveillant tente de mettre à jour la ligne retirée
    $r = $this->post(route('finance.exercices.planification.save', $ex), [
        'ref' => [
            $bl->id_codeanalytique => ['dotation_etat' => 999_000],
        ],
    ]);
    $r->assertRedirect();
    // Le montant n'a pas été touché (l'entrée existante n'est pas dans le mask nonRetirees)
    expect((float) $bl->fresh()->dotation_etat)->toBe(0.0);
});

it('les scopes nonRetirees et retirees sont complémentaires', function () {
    $ex = mkExRetrait();
    $ex->budgetLignes()->first()->update(['retiree_de_planification' => true]);

    $actives = $ex->budgetLignes()->nonRetirees()->count();
    $retirees = $ex->budgetLignes()->retirees()->count();
    expect($actives + $retirees)->toBe($ex->budgetLignes()->count());
    expect($retirees)->toBe(1);
});

it('initialiserPlanification ne ressuscite pas une ligne retirée', function () {
    $ex = mkExRetrait();
    $bl = $ex->budgetLignes()->first();
    $bl->update(['retiree_de_planification' => true]);
    $countAvant = $ex->budgetLignes()->count();

    // Rappel de la méthode d'initialisation
    $ex->initialiserPlanification($this->auteur->id);
    expect($ex->budgetLignes()->count())->toBe($countAvant);
    expect($bl->fresh()->retiree_de_planification)->toBeTrue();
});
