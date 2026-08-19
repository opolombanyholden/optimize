<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Finance\Ordre;
use App\Models\Finance\OrdreDetail;
use App\Models\Finance\OrdreModele;
use App\Models\Ligne;
use App\Models\RubriqueOperation;
use App\Models\Titre;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $this->auteur = \App\Models\User::factory()->create();

    // Référentiel : 1 titre depense, 1 ligne, 2 rubriques
    $t = Titre::create(['imputation' => 'T-CRT', 'libelle' => 'Créa test', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $this->ligne = Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 81001, 'libelle' => 'Test créa', 'nature' => 'depense', 'id_user' => $this->auteur->id]);
    $this->rub1 = RubriqueOperation::create(['code' => 'R-C1', 'libelle' => 'Rubrique 1', 'ligne_id' => $this->ligne->id, 'sens' => 'depense', 'statut' => 1, 'created_by' => $this->auteur->id]);
    $this->rub2 = RubriqueOperation::create(['code' => 'R-C2', 'libelle' => 'Rubrique 2', 'ligne_id' => $this->ligne->id, 'sens' => 'mixte',   'statut' => 1, 'created_by' => $this->auteur->id]);
    RubriqueOperation::create(['code' => 'R-CR', 'libelle' => 'Recette pure', 'ligne_id' => $this->ligne->id, 'sens' => 'recette', 'statut' => 1, 'created_by' => $this->auteur->id]);

    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $this->exercice = Exercice::create([
        'exercice' => 'EX-CRT-' . random_int(100, 999), 'libelle' => 'Test création',
        'statut' => Exercice::STATUT_PLANIFICATION, 'id_user' => $this->auteur->id,
    ]);
    $this->bl = BudgetLigne::create([
        'id_budgetligne' => 'BL-CRT', 'id_exercicebudgetaire' => $this->exercice->id,
        'id_codeanalytique' => $this->ligne->id, 'isvalide' => 0, 'id_user' => $this->auteur->id,
    ]);

    $this->modeleDepense = OrdreModele::where('code', 'ordonnance_paiement')->first();
    $this->modeleRecette = OrdreModele::where('code', 'ordre_recette')->first();
});

it('index renvoie 200 et affiche les filtres', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('finance.ordres.index'));
    $r->assertOk();
    $r->assertSee('Dépenses');
    $r->assertSee('Recettes');
});

it('create sans modele affiche le sélecteur de modèles', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('finance.ordres.create'));
    $r->assertOk();
    $r->assertSee('Ordre de recette');
    $r->assertSee('Ordonnance de paiement');
});

it('create avec ?modele=id affiche le formulaire dynamique du modèle', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('finance.ordres.create', ['modele' => $this->modeleDepense->id]));
    $r->assertOk();
    $r->assertSee('NATURE DE LA DEPENSE');
    $r->assertSee('IMPUTATION BUDGETAIRE');
});

it('store crée un ordre avec un numéro généré selon le format du modèle', function () {
    actingAsSuperAdmin();

    $r = $this->post(route('finance.ordres.store'), [
        'modele_id'       => $this->modeleDepense->id,
        'exercice_id'     => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id,
        'libelle'         => 'Achat fournitures',
        'donnees'         => [
            'nature_depense'      => 'Fournitures de bureau',
            'imputation_budget'   => '618909',
            'beneficiaire_raison' => 'FOURNI-BUREAU SARL',
            'ref_date'            => '2027-01-15',
            'montant'             => 100000,
            'mode_reglement'      => 'virement',
        ],
    ]);
    $r->assertRedirect();

    $ordre = Ordre::latest()->first();
    expect($ordre)->not->toBeNull();
    expect($ordre->modele_id)->toBe($this->modeleDepense->id);
    expect($ordre->sens)->toBe('depense');
    expect($ordre->statut)->toBe(Ordre::STATUT_BROUILLON);
    expect($ordre->numero_ordre)->toMatch('#^\d{4}/BF/PR/ANPI-GABON/DG/DFMG/KAE$#');
});

it('store échoue si un champ obligatoire du modèle manque', function () {
    actingAsSuperAdmin();

    $r = $this->post(route('finance.ordres.store'), [
        'modele_id'       => $this->modeleDepense->id,
        'exercice_id'     => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id,
        'donnees'         => [
            // manque nature_depense, imputation_budget, beneficiaire_raison…
        ],
    ]);
    $r->assertSessionHasErrors('donnees.nature_depense');
    $r->assertSessionHasErrors('donnees.imputation_budget');
});

it('store persiste les détails ventilés et calcule le montant total', function () {
    actingAsSuperAdmin();

    $this->post(route('finance.ordres.store'), [
        'modele_id'       => $this->modeleDepense->id,
        'exercice_id'     => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id,
        'donnees'         => [
            'nature_depense'      => 'Fournitures',
            'imputation_budget'   => '618',
            'beneficiaire_raison' => 'X',
            'ref_date'            => '2027-01-01',
            'montant'             => 0,
            'mode_reglement'      => 'virement',
        ],
        'details' => [
            ['libelle' => 'Papier',     'rubrique_id' => $this->rub1->id, 'quantite' => 10, 'prix_unitaire' => 3500],
            ['libelle' => 'Cartouches', 'rubrique_id' => $this->rub2->id, 'quantite' => 3,  'prix_unitaire' => 25000],
        ],
    ])->assertRedirect();

    $ordre = Ordre::latest()->first();
    expect($ordre->details->count())->toBe(2);
    expect((float) $ordre->montant)->toBe(110_000.0); // 35000 + 75000
});

it('store filtre silencieusement une rubrique hors sens (recette pour un ordre dépense)', function () {
    actingAsSuperAdmin();
    $rubReceptePure = RubriqueOperation::where('code', 'R-CR')->first();

    $this->post(route('finance.ordres.store'), [
        'modele_id'       => $this->modeleDepense->id,
        'exercice_id'     => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id,
        'donnees'         => [
            'nature_depense' => 'X', 'imputation_budget' => '1',
            'beneficiaire_raison' => 'X', 'ref_date' => '2027-01-01',
            'montant' => 0, 'mode_reglement' => 'numeraire',
        ],
        'details' => [
            ['libelle' => 'Ligne hors sens', 'rubrique_id' => $rubReceptePure->id, 'quantite' => 1, 'prix_unitaire' => 1000],
        ],
    ])->assertRedirect();

    $d = OrdreDetail::where('libelle', 'Ligne hors sens')->first();
    expect($d)->not->toBeNull();
    expect($d->rubrique_id)->toBeNull(); // rubrique invalidée silencieusement
});

it('store refuse un exercice clôturé', function () {
    actingAsSuperAdmin();
    $exCloture = Exercice::create(['exercice' => 'CLOS', 'libelle' => 'Clos', 'statut' => Exercice::STATUT_CLOTURE, 'id_user' => $this->auteur->id]);

    $r = $this->post(route('finance.ordres.store'), [
        'modele_id'       => $this->modeleRecette->id,
        'exercice_id'     => $exCloture->id,
        'budget_ligne_id' => $this->bl->id,
        'donnees'         => [
            'nature_recette' => 'X', 'imputation_budget' => '1',
            'date_emission' => '2027-01-01', 'montant' => 1000,
        ],
    ]);
    $r->assertSessionHasErrors('exercice_id');
});

it('endpoint AJAX rubriques-pour-ligne filtre par sens', function () {
    actingAsSuperAdmin();

    $r = $this->getJson(route('finance.ordres.rubriques-pour-ligne', ['budgetLigne' => $this->bl->id, 'sens' => 'depense']));
    $r->assertOk();
    $codes = collect($r->json())->pluck('code')->all();
    expect($codes)->toContain('R-C1', 'R-C2');
    expect($codes)->not->toContain('R-CR');
});

it('endpoint AJAX budget-lignes pour exercice retourne les lignes non-retirées', function () {
    actingAsSuperAdmin();
    $r = $this->getJson(route('finance.api.exercices.budget-lignes', ['exercice' => $this->exercice->id]));
    $r->assertOk();
    expect(count($r->json()))->toBe(1);
    expect($r->json()[0]['id'])->toBe($this->bl->id);
});

it('destroy en brouillon fonctionne', function () {
    actingAsSuperAdmin();
    $mod = $this->modeleDepense;
    $ordre = Ordre::create([
        'modele_id' => $mod->id, 'numero_ordre' => 'DEL-1', 'exercice_id' => $this->exercice->id,
        'sens' => 'depense', 'montant' => 0, 'created_by' => $this->auteur->id,
        'statut' => Ordre::STATUT_BROUILLON,
    ]);

    $this->delete(route('finance.ordres.destroy', $ordre))
        ->assertRedirect(route('finance.ordres.index'));
    expect(Ordre::find($ordre->id))->toBeNull();
});

it('destroy refuse un ordre soumis pour un user standard', function () {
    $u = \App\Models\User::factory()->create();
    $u->givePermissionTo(['read:operation', 'delete:operation']);
    $this->actingAs($u);

    $ordre = Ordre::create([
        'modele_id' => $this->modeleDepense->id, 'numero_ordre' => 'DEL-2', 'exercice_id' => $this->exercice->id,
        'sens' => 'depense', 'montant' => 0, 'created_by' => $this->auteur->id,
        'statut' => Ordre::STATUT_SOUMIS,
    ]);

    $r = $this->delete(route('finance.ordres.destroy', $ordre));
    $r->assertSessionHas('error');
    expect(Ordre::find($ordre->id))->not->toBeNull();
});

it('imputation est auto-remplie depuis id_codeanalytique de la ligne budgétaire', function () {
    actingAsSuperAdmin();

    // Volontairement, l'utilisateur envoie une imputation « erronée » : elle doit être écrasée
    $r = $this->post(route('finance.ordres.store'), [
        'modele_id'       => $this->modeleDepense->id,
        'exercice_id'     => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id,
        'donnees'         => [
            'nature_depense'      => 'X',
            'imputation_budget'   => '999999-FAKE',
            'beneficiaire_raison' => 'Y',
            'ref_date'            => '2027-01-01',
            'montant'             => 100,
            'mode_reglement'      => 'numeraire',
        ],
    ]);
    $r->assertRedirect();

    $ordre = Ordre::latest()->first();
    // L'imputation stockée = id_codeanalytique de la ligne du référentiel, pas la valeur client
    expect($ordre->donnees_json['imputation_budget'])->toBe((string) $this->ligne->id_codeanalytique);
});

it('endpoint API budget-lignes retourne id_codeanalytique pour auto-fill', function () {
    actingAsSuperAdmin();
    $r = $this->getJson(route('finance.api.exercices.budget-lignes', ['exercice' => $this->exercice->id]));
    $r->assertOk();
    $r->assertJsonFragment(['id_codeanalytique' => $this->ligne->id_codeanalytique]);
});

it('le super-admin peut supprimer un ordre soumis (bypass)', function () {
    actingAsSuperAdmin();
    $ordre = Ordre::create([
        'modele_id' => $this->modeleDepense->id, 'numero_ordre' => 'DEL-3', 'exercice_id' => $this->exercice->id,
        'sens' => 'depense', 'montant' => 0, 'created_by' => $this->auteur->id,
        'statut' => Ordre::STATUT_SOUMIS,
    ]);

    $this->delete(route('finance.ordres.destroy', $ordre))->assertRedirect();
    expect(Ordre::find($ordre->id))->toBeNull();
});
