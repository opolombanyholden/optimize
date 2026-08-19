<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\ModificationBudgetaire;
use App\Services\Finance\ModificationBudgetaireService;

beforeEach(function () {
    seedRoles();
});

function makeExerciceWf(int $statut = 2): Exercice
{
    Exercice::where('statut', 2)->update(['statut' => 3]);
    return Exercice::create([
        'exercice'  => 'WF-' . random_int(100, 999),
        'libelle'   => 'WF Test',
        'statut'    => $statut,
        'datedebut' => '2026-01-01',
        'datefin'   => '2026-12-31',
    ]);
}

function makeLigneWf(Exercice $ex, float $dotation = 1_000_000): BudgetLigne
{
    return BudgetLigne::create([
        'id_budgetligne'        => 'L-' . random_int(1000, 9999),
        'id_exercicebudgetaire' => $ex->id,
        'dotation_etat'         => $dotation,
        'engagement'            => 0,
        'isvalide'              => 0,
        'id_user'               => auth()->id() ?? 1,
    ]);
}

// ─── MODIFICATIONS BUDGÉTAIRES — Workflow ──────────────────

it('crée une modification budgétaire en brouillon', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    $src = makeLigneWf($ex);
    $dst = makeLigneWf($ex);

    $this->post('/finance/modifications-budgetaires', [
        'exercice_id'                 => $ex->id,
        'type_modification'           => 'transfert',
        'objetmodification'           => 'Test transfert',
        'montant_modification'        => 50_000,
        'budget_ligne_source_id'      => $src->id,
        'budget_ligne_destination_id' => $dst->id,
    ])->assertRedirect();

    $mod = ModificationBudgetaire::where('objetmodification', 'Test transfert')->first();
    expect($mod)->not->toBeNull();
    expect($mod->statut)->toBe(0); // brouillon
});

it('exige une source pour un transfert', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    $dst = makeLigneWf($ex);

    $this->post('/finance/modifications-budgetaires', [
        'exercice_id'                 => $ex->id,
        'type_modification'           => 'transfert',
        'objetmodification'           => 'Sans source',
        'montant_modification'        => 50_000,
        'budget_ligne_destination_id' => $dst->id,
    ])->assertSessionHasErrors('budget_ligne_source_id');
});

it('un ajout ne requiert PAS de source', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    $dst = makeLigneWf($ex);

    $this->post('/finance/modifications-budgetaires', [
        'exercice_id'                 => $ex->id,
        'type_modification'           => 'ajout',
        'objetmodification'           => 'Apport sur ligne',
        'montant_modification'        => 30_000,
        'budget_ligne_destination_id' => $dst->id,
    ])->assertRedirect();

    expect(ModificationBudgetaire::where('objetmodification', 'Apport sur ligne')->exists())->toBeTrue();
});

it('soumet, approuve, applique une modification — transfert effectif sur les lignes', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    $src = makeLigneWf($ex, 1_000_000);
    $dst = makeLigneWf($ex, 500_000);

    $mod = ModificationBudgetaire::create([
        'exercice_id'                  => $ex->id,
        'type_modification'            => 'transfert',
        'objetmodification'            => 'Transfert workflow',
        'montant_modification'         => 200_000,
        'budget_ligne_source_id'       => $src->id,
        'budget_ligne_destination_id'  => $dst->id,
        'id_user'                      => auth()->id(),
        'statut'                       => 0,
    ]);

    // Soumettre
    $this->post("/finance/modifications-budgetaires/{$mod->id}/soumettre")->assertRedirect();
    expect($mod->fresh()->statut)->toBe(1);

    // Approuver
    $this->post("/finance/modifications-budgetaires/{$mod->id}/approuver")->assertRedirect();
    expect($mod->fresh()->statut)->toBe(2);

    // Appliquer
    $this->post("/finance/modifications-budgetaires/{$mod->id}/appliquer")->assertRedirect();
    expect($mod->fresh()->statut)->toBe(3);

    // Les soldes (transfert) doivent être ajustés
    expect((float) $src->fresh()->transfert)->toBe(-200_000.0);
    expect((float) $dst->fresh()->transfert)->toBe(200_000.0);
});

it('rejette une modification : statut → 4 avec motif', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    $src = makeLigneWf($ex);
    $dst = makeLigneWf($ex);

    $mod = ModificationBudgetaire::create([
        'exercice_id'                  => $ex->id,
        'type_modification'            => 'transfert',
        'objetmodification'            => 'À rejeter',
        'montant_modification'         => 50_000,
        'budget_ligne_source_id'       => $src->id,
        'budget_ligne_destination_id'  => $dst->id,
        'id_user'                      => auth()->id(),
        'statut'                       => 1, // soumise
    ]);

    $this->post("/finance/modifications-budgetaires/{$mod->id}/rejeter", [
        'motif_rejet' => 'Justification insuffisante',
    ])->assertRedirect();

    $mod->refresh();
    expect($mod->statut)->toBe(4);
    expect($mod->motif_rejet)->toBe('Justification insuffisante');
});

it('refuse d\'appliquer si solde source insuffisant', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    // Ligne source à 100K seulement, on veut transférer 500K
    $src = makeLigneWf($ex, 100_000);
    $dst = makeLigneWf($ex);

    $mod = ModificationBudgetaire::create([
        'exercice_id'                  => $ex->id,
        'type_modification'            => 'transfert',
        'objetmodification'            => 'Trop gros',
        'montant_modification'         => 500_000,
        'budget_ligne_source_id'       => $src->id,
        'budget_ligne_destination_id'  => $dst->id,
        'id_user'                      => auth()->id(),
        'statut'                       => 1,
    ]);

    // L'approbation doit échouer car solde insuffisant
    $this->post("/finance/modifications-budgetaires/{$mod->id}/approuver")
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($mod->fresh()->statut)->toBe(1); // reste soumise
});

it('annule une application : repasse en approuvée et restaure les soldes', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    $src = makeLigneWf($ex, 1_000_000);
    $dst = makeLigneWf($ex);

    $mod = ModificationBudgetaire::create([
        'exercice_id'                  => $ex->id,
        'type_modification'            => 'transfert',
        'objetmodification'            => 'Test annulation',
        'montant_modification'         => 100_000,
        'budget_ligne_source_id'       => $src->id,
        'budget_ligne_destination_id'  => $dst->id,
        'id_user'                      => auth()->id(),
        'statut'                       => 2, // approuvée
    ]);

    (new ModificationBudgetaireService())->appliquer($mod, auth()->id());
    expect($mod->fresh()->statut)->toBe(3);
    expect((float) $src->fresh()->transfert)->toBe(-100_000.0);

    $this->post("/finance/modifications-budgetaires/{$mod->id}/annuler")->assertRedirect();

    $mod->refresh();
    expect($mod->statut)->toBe(2); // retour approuvée
    expect((float) $src->fresh()->transfert)->toBe(0.0); // soldes restaurés
    expect((float) $dst->fresh()->transfert)->toBe(0.0);
});

// ─── LIGNES BUDGÉTAIRES — Validation ──────────────────────

it('valide une ligne budgétaire (isvalide 0 → 1)', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    $l = makeLigneWf($ex);

    $this->post("/finance/budgets/{$l->id}/valider")->assertRedirect();
    expect((int) $l->fresh()->isvalide)->toBe(1);
});

it('refuse de valider une ligne avec budget nul', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    $l = makeLigneWf($ex, 0); // dotation à 0

    $this->post("/finance/budgets/{$l->id}/valider")
        ->assertRedirect()
        ->assertSessionHas('error');
    expect((int) $l->fresh()->isvalide)->toBe(0);
});

it('refuse de dévalider une ligne ayant des engagements', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    $l = makeLigneWf($ex);
    $l->update(['isvalide' => 1, 'engagement' => 50_000]);

    $this->post("/finance/budgets/{$l->id}/devalider")
        ->assertRedirect()
        ->assertSessionHas('error');
    expect((int) $l->fresh()->isvalide)->toBe(1);
});

it('refuse de supprimer une ligne validée', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    $l = makeLigneWf($ex);
    $l->update(['isvalide' => 1]);

    $this->delete("/finance/budgets/{$l->id}")
        ->assertRedirect()
        ->assertSessionHas('error');
    expect(BudgetLigne::find($l->id))->not->toBeNull();
});

// ─── EXPORTS PDF ───────────────────────────────────────────

it('génère le PDF budget pour un exercice', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    makeLigneWf($ex);

    $resp = $this->get("/finance/exports/budget/{$ex->id}");
    $resp->assertOk();
    expect($resp->headers->get('Content-Type'))->toContain('application/pdf');
    expect(substr($resp->getContent(), 0, 5))->toBe('%PDF-');
});

it('génère le PDF grand-livre', function () {
    actingAsSuperAdmin();
    $resp = $this->get('/finance/exports/grand-livre');
    $resp->assertOk();
    expect(substr($resp->getContent(), 0, 5))->toBe('%PDF-');
});

it('génère le PDF balance des comptes', function () {
    actingAsSuperAdmin();
    $ex = makeExerciceWf();
    $resp = $this->get("/finance/exports/balance/{$ex->id}");
    $resp->assertOk();
    expect(substr($resp->getContent(), 0, 5))->toBe('%PDF-');
});
