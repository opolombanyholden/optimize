<?php

use App\Models\Exercice;
use App\Models\Ligne;
use App\Models\Titre;

beforeEach(function () {
    seedRoles();
    $this->auteur = \App\Models\User::factory()->create();

    // Référentiel test : 2 titres depense (1 ligne chacun), 1 titre recette (1 ligne), 1 titre mixte (1 ligne)
    $td = Titre::create(['imputation' => 'TD',  'libelle' => 'Dépenses', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $ti = Titre::create(['imputation' => 'TDI', 'libelle' => 'Investissement', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $tr = Titre::create(['imputation' => 'TR',  'libelle' => 'Recettes', 'type_ligne' => 'recette', 'id_user' => $this->auteur->id]);
    $tm = Titre::create(['imputation' => 'TM',  'libelle' => 'Mixte',    'type_ligne' => 'mixte',   'id_user' => $this->auteur->id]);
    $mk = fn($t, $ca) => Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => $ca, 'libelle' => 'L'.$ca, 'nature' => 'x', 'id_user' => $this->auteur->id]);
    $mk($td, 71001); $mk($ti, 71002); $mk($tr, 71003); $mk($tm, 71004);
});

function mkExerciceTp(string $type): Exercice
{
    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    return Exercice::create([
        'exercice' => 'EX-TP-' . strtoupper($type) . '-' . random_int(100, 999),
        'libelle'  => 'TP ' . $type,
        'statut'   => Exercice::STATUT_PLANIFICATION,
        'validation_statut'  => 0,
        'id_user'  => test()->auteur->id,
        'type_planification' => $type,
    ]);
}

it('typesLigneAcceptes retourne les bons types selon le périmètre', function () {
    $dep = mkExerciceTp('depense');
    $rec = mkExerciceTp('recette');
    $mix = mkExerciceTp('mixte');
    expect($dep->typesLigneAcceptes())->toBe(['depense']);
    expect($rec->typesLigneAcceptes())->toBe(['recette']);
    expect($mix->typesLigneAcceptes())->toBe(['depense', 'recette', 'mixte']);
});

it('initialiserPlanification pour un exercice dépense ne matérialise que les lignes de titres depense', function () {
    $ex = mkExerciceTp('depense');
    $ex->initialiserPlanification($this->auteur->id);
    // 2 titres depense (TD + TDI) → 2 lignes seulement
    expect($ex->budgetLignes()->count())->toBe(2);
});

it('initialiserPlanification pour un exercice recette ne matérialise que la ligne recette', function () {
    $ex = mkExerciceTp('recette');
    $ex->initialiserPlanification($this->auteur->id);
    expect($ex->budgetLignes()->count())->toBe(1);
});

it('initialiserPlanification pour un exercice mixte matérialise toutes les lignes', function () {
    $ex = mkExerciceTp('mixte');
    $ex->initialiserPlanification($this->auteur->id);
    expect($ex->budgetLignes()->count())->toBe(4);
});

it('la page planification affiche uniquement les titres du périmètre', function () {
    actingAsSuperAdmin();
    $r = $this->post('/finance/exercices', [
        'exercice' => 'EX-VIEW-DEP', 'libelle' => 'View depense',
        'datedebut' => '2028-01-01', 'datefin' => '2028-12-31',
        'type_planification' => 'depense',
    ]);
    $r->assertRedirect();
    $ex = Exercice::where('exercice', 'EX-VIEW-DEP')->firstOrFail();
    $planif = $this->get(route('finance.exercices.planification', $ex));
    $planif->assertOk();
    $titres = $planif->viewData('titres');
    expect($titres->pluck('type_ligne')->unique()->all())->toEqual(['depense']);
});

it('changer le type de planification en édition matérialise les nouvelles lignes', function () {
    actingAsSuperAdmin();
    $this->post('/finance/exercices', [
        'exercice' => 'EX-SWITCH', 'libelle' => 'Switch',
        'type_planification' => 'depense',
    ]);
    $ex = Exercice::where('exercice', 'EX-SWITCH')->firstOrFail();
    $countAvant = $ex->budgetLignes()->count();

    // Passe en mixte via update
    $this->put("/finance/exercices/{$ex->id}", [
        'libelle' => 'Switch', 'type_planification' => 'mixte',
    ])->assertRedirect();

    $countApres = $ex->fresh()->budgetLignes()->count();
    expect($countApres)->toBeGreaterThan($countAvant);
});

it('création POST par défaut → mixte', function () {
    actingAsSuperAdmin();
    $this->post('/finance/exercices', [
        'exercice' => 'EX-DEFAUT', 'libelle' => 'Défaut',
    ])->assertRedirect();
    $ex = Exercice::where('exercice', 'EX-DEFAUT')->firstOrFail();
    expect($ex->type_planification)->toBe('mixte');
});

// ═════════ RÉCONCILIATION APRÈS CHANGEMENT DE TYPE ═════════

it('changer mixte → depense retire automatiquement les lignes hors scope', function () {
    actingAsSuperAdmin();
    $this->post('/finance/exercices', [
        'exercice' => 'EX-M2D', 'libelle' => 'M2D', 'type_planification' => 'mixte',
    ]);
    $ex = Exercice::where('exercice', 'EX-M2D')->firstOrFail();
    expect($ex->budgetLignes()->count())->toBe(4);
    expect($ex->budgetLignes()->nonRetirees()->count())->toBe(4);

    // Bascule en depense
    $this->put("/finance/exercices/{$ex->id}", [
        'libelle' => 'M2D', 'type_planification' => 'depense',
    ])->assertRedirect();

    $ex->refresh();
    expect($ex->budgetLignes()->count())->toBe(4); // aucune supprimée (historique)
    expect($ex->budgetLignes()->nonRetirees()->count())->toBe(2); // seules les depense visibles
    expect($ex->budgetLignes()->retirees()->count())->toBe(2);    // recette + mixte retirées
});

it('changer depense → mixte remet automatiquement les lignes précédemment auto-retirées', function () {
    actingAsSuperAdmin();
    $this->post('/finance/exercices', [
        'exercice' => 'EX-CYCLE', 'libelle' => 'Cycle', 'type_planification' => 'mixte',
    ]);
    $ex = Exercice::where('exercice', 'EX-CYCLE')->firstOrFail();

    // mixte → depense : retire recette+mixte
    $this->put("/finance/exercices/{$ex->id}", ['libelle' => 'Cycle', 'type_planification' => 'depense']);
    $ex->refresh();
    expect($ex->budgetLignes()->retirees()->count())->toBe(2);

    // depense → mixte : remet automatiquement
    $this->put("/finance/exercices/{$ex->id}", ['libelle' => 'Cycle', 'type_planification' => 'mixte']);
    $ex->refresh();
    expect($ex->budgetLignes()->retirees()->count())->toBe(0);
    expect($ex->budgetLignes()->nonRetirees()->count())->toBe(4);
});

it('la réconciliation refuse de retirer une ligne hors scope avec engagement', function () {
    $ex = mkExerciceTp('mixte');
    $ex->initialiserPlanification($this->auteur->id);

    // Simule un engagement sur la ligne recette
    $blRecette = $ex->budgetLignes()->whereHas('ligne.titre', fn($q) => $q->where('type_ligne', 'recette'))->first();
    expect($blRecette)->not->toBeNull();
    $blRecette->update(['engagement' => 100_000]);

    // Bascule vers depense → la ligne recette a engagement → ne peut être retirée
    $ex->update(['type_planification' => 'depense']);
    $recap = $ex->reconcilierAvecTypePlanification();
    expect($recap['bloquees']->count())->toBe(1);
    expect($blRecette->fresh()->retiree_de_planification)->toBeFalse();
});
