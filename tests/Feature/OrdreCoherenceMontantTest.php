<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Finance\Ordre;
use App\Models\Finance\OrdreModele;
use App\Models\Ligne;
use App\Models\RubriqueOperation;
use App\Models\Titre;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $this->auteur = User::factory()->create();

    $t = Titre::create(['imputation' => 'T-COH', 'libelle' => 'COH', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $this->ligne = Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 91111, 'libelle' => 'L COH', 'nature' => 'depense', 'id_user' => $this->auteur->id]);
    $this->rub1 = RubriqueOperation::create(['code' => 'R-COH-1', 'libelle' => 'R1', 'ligne_id' => $this->ligne->id, 'sens' => 'depense', 'statut' => 1, 'created_by' => $this->auteur->id]);
    $this->rub2 = RubriqueOperation::create(['code' => 'R-COH-2', 'libelle' => 'R2', 'ligne_id' => $this->ligne->id, 'sens' => 'depense', 'statut' => 1, 'created_by' => $this->auteur->id]);

    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $this->exercice = Exercice::create([
        'exercice' => 'EX-COH-' . random_int(100, 999), 'libelle' => 'COH',
        'statut' => Exercice::STATUT_EN_EXECUTION, 'id_user' => $this->auteur->id,
    ]);
    $this->bl = BudgetLigne::create([
        'id_budgetligne' => 'BL-COH', 'id_exercicebudgetaire' => $this->exercice->id,
        'id_codeanalytique' => $this->ligne->id, 'id_famillecodeanalytique' => $t->id,
        'dotation_etat' => 10_000_000, 'isvalide' => 1, 'id_user' => $this->auteur->id,
    ]);
    $this->modele = OrdreModele::where('code', 'ordonnance_paiement')->first();
});

function basePayload(): array {
    return [
        'modele_id'       => test()->modele->id,
        'exercice_id'     => test()->exercice->id,
        'budget_ligne_id' => test()->bl->id,
        'donnees' => [
            'nature_depense' => 'Test', 'imputation_budget' => '0',
            'beneficiaire_raison' => 'Y', 'ref_date' => '2027-01-01',
            'mode_reglement' => 'virement',
        ],
    ];
}

// ═════════ Cohérence stricte ═════════

it('refuse la sauvegarde si total des rubriques ≠ MONTANT EN CHIFFRES', function () {
    actingAsSuperAdmin();

    // Total détails = 100 + 200 = 300 000 ; montant saisi = 500 000 → REJET
    $r = $this->post(route('finance.ordres.store'), array_merge_recursive(basePayload(), [
        'donnees' => ['montant' => 500_000],
        'details' => [
            ['libelle' => 'A', 'rubrique_id' => $this->rub1->id, 'quantite' => 1, 'prix_unitaire' => 100_000],
            ['libelle' => 'B', 'rubrique_id' => $this->rub2->id, 'quantite' => 1, 'prix_unitaire' => 200_000],
        ],
    ]));

    $r->assertSessionHasErrors('donnees.montant');
    expect(Ordre::count())->toBe(0);
});

it('accepte la sauvegarde si total des rubriques = MONTANT EN CHIFFRES', function () {
    actingAsSuperAdmin();

    $r = $this->post(route('finance.ordres.store'), array_merge_recursive(basePayload(), [
        'donnees' => ['montant' => 300_000],
        'details' => [
            ['libelle' => 'A', 'rubrique_id' => $this->rub1->id, 'quantite' => 1, 'prix_unitaire' => 100_000],
            ['libelle' => 'B', 'rubrique_id' => $this->rub2->id, 'quantite' => 1, 'prix_unitaire' => 200_000],
        ],
    ]));

    $r->assertRedirect();
    $o = Ordre::latest()->first();
    expect((float) $o->montant)->toBe(300_000.0);
    expect($o->details->count())->toBe(2);
});

it('accepte si montant saisi vide ou 0 : montant = somme des détails', function () {
    actingAsSuperAdmin();

    $r = $this->post(route('finance.ordres.store'), array_merge_recursive(basePayload(), [
        'donnees' => ['montant' => 0],
        'details' => [
            ['libelle' => 'A', 'quantite' => 5, 'prix_unitaire' => 15_000],
        ],
    ]));
    $r->assertRedirect();
    $o = Ordre::latest()->first();
    expect((float) $o->montant)->toBe(75_000.0);
});

it('sans détails : montant = valeur saisie (aucune contrainte)', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('finance.ordres.store'), array_merge_recursive(basePayload(), [
        'donnees' => ['montant' => 450_000],
        // pas de détails
    ]));
    $r->assertRedirect();
    $o = Ordre::latest()->first();
    expect((float) $o->montant)->toBe(450_000.0);
});

it('tolère un écart d\'arrondi de 1 centime', function () {
    actingAsSuperAdmin();
    // Total détails = 3 × 33 333.34 = 100 000.02 ; montant saisi = 100 000 → OK (écart 0.02 ??)
    // Utilisons plutôt un cas où l'écart est exactement de 1 centime
    // Total = 100 000.01 ; montant = 100 000.00 → écart 0.01 → OK
    $r = $this->post(route('finance.ordres.store'), array_merge_recursive(basePayload(), [
        'donnees' => ['montant' => 100_000.00],
        'details' => [
            ['libelle' => 'A', 'quantite' => 1, 'prix_unitaire' => 100_000.01],
        ],
    ]));
    $r->assertRedirect();
});

it('rejette un écart supérieur à 1 centime', function () {
    actingAsSuperAdmin();
    // Total = 100 000.05 ; montant = 100 000.00 → écart 0.05 → REJET
    $r = $this->post(route('finance.ordres.store'), array_merge_recursive(basePayload(), [
        'donnees' => ['montant' => 100_000.00],
        'details' => [
            ['libelle' => 'A', 'quantite' => 1, 'prix_unitaire' => 100_000.05],
        ],
    ]));
    $r->assertSessionHasErrors('donnees.montant');
});

it('update : rejette si total des rubriques ≠ montant', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), array_merge_recursive(basePayload(), [
        'donnees' => ['montant' => 300_000],
        'details' => [
            ['libelle' => 'A', 'quantite' => 1, 'prix_unitaire' => 300_000],
        ],
    ]));
    $o = Ordre::latest()->first();
    $existDetailId = $o->details->first()->id;

    // Tentative de update : garde le détail à 300k mais change le montant à 999k → REJET
    $r = $this->put(route('finance.ordres.update', $o), array_merge_recursive(basePayload(), [
        'donnees' => ['montant' => 999_000],
        'details' => [
            ['id' => $existDetailId, 'libelle' => 'A', 'quantite' => 1, 'prix_unitaire' => 300_000],
        ],
    ]));
    $r->assertSessionHasErrors('donnees.montant');
    expect((float) $o->fresh()->montant)->toBe(300_000.0);
});
