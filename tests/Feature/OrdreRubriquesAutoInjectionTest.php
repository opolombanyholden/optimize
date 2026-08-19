<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Finance\OrdreModele;
use App\Models\Ligne;
use App\Models\RubriqueOperation;
use App\Models\Titre;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $this->auteur = User::factory()->create();

    // Ligne « Charges sociales patronales » avec ses sous-rubriques : CNSS, CNAMGS
    $t = Titre::create(['imputation' => 'T-RH', 'libelle' => 'RH', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $this->ligneRh = Ligne::create([
        'id_titre' => $t->id, 'id_codeanalytique' => 21003,
        'libelle' => 'Charges sociales patronales', 'nature' => 'depense',
        'id_user' => $this->auteur->id,
    ]);
    $this->cnss = RubriqueOperation::create([
        'code' => 'R-CNSS', 'libelle' => 'Cotisation CNSS',
        'ligne_id' => $this->ligneRh->id, 'sens' => 'depense', 'statut' => 1,
        'ordre_affichage' => 10, 'created_by' => $this->auteur->id,
    ]);
    $this->cnamgs = RubriqueOperation::create([
        'code' => 'R-CNAMGS', 'libelle' => 'Cotisation CNAMGS',
        'ligne_id' => $this->ligneRh->id, 'sens' => 'depense', 'statut' => 1,
        'ordre_affichage' => 20, 'created_by' => $this->auteur->id,
    ]);

    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $this->exercice = Exercice::create([
        'exercice' => 'EX-AI-' . random_int(100, 999), 'libelle' => 'AI',
        'statut' => Exercice::STATUT_EN_EXECUTION, 'id_user' => $this->auteur->id,
    ]);
    $this->bl = BudgetLigne::create([
        'id_budgetligne' => 'BL-RH', 'id_exercicebudgetaire' => $this->exercice->id,
        'id_codeanalytique' => $this->ligneRh->id, 'id_famillecodeanalytique' => $t->id,
        'isvalide' => 1, 'id_user' => $this->auteur->id,
    ]);
    $this->modele = OrdreModele::where('code', 'ordonnance_paiement')->first();
});

it('endpoint rubriques-pour-ligne retourne les rubriques ordonnées par ordre_affichage', function () {
    actingAsSuperAdmin();
    $r = $this->getJson(route('finance.ordres.rubriques-pour-ligne', ['budgetLigne' => $this->bl->id, 'sens' => 'depense']));
    $r->assertOk();
    $codes = collect($r->json())->pluck('code')->all();
    expect($codes)->toBe(['R-CNSS', 'R-CNAMGS']);
});

it('vue create : contient badge compteur + bouton recharger rubriques', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('finance.ordres.create', ['modele' => $this->modele->id]));
    $r->assertOk();
    $r->assertSee('rubriques-info', false);
    $r->assertSee('btn-reload-rubriques', false);
    $r->assertSee('rubriques-count', false);
    $r->assertSeeText('rubrique(s) prédéfinie(s)');
});

it('endpoint expose code + libelle des rubriques (nécessaires pour l\'injection JS)', function () {
    actingAsSuperAdmin();
    $r = $this->getJson(route('finance.ordres.rubriques-pour-ligne', ['budgetLigne' => $this->bl->id, 'sens' => 'depense']));

    $rubs = collect($r->json());
    expect($rubs->firstWhere('code', 'R-CNSS')['libelle'])->toBe('Cotisation CNSS');
    expect($rubs->firstWhere('code', 'R-CNAMGS')['libelle'])->toBe('Cotisation CNAMGS');
    // id présent pour permettre la sélection dans le select
    expect($rubs->firstWhere('code', 'R-CNSS')['id'])->toBe($this->cnss->id);
});
