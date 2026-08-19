<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Finance\Ordre;
use App\Models\Finance\OrdreDetail;
use App\Models\Finance\OrdreModele;
use App\Models\Ligne;
use App\Models\RubriqueOperation;
use App\Models\Titre;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $this->auteur = User::factory()->create();

    // Référentiel minimal : 1 titre + 1 ligne + 3 rubriques (depense/recette/mixte)
    $titre = Titre::create(['imputation' => 'T-D', 'libelle' => 'Détails test', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $this->ligne = Ligne::create(['id_titre' => $titre->id, 'id_codeanalytique' => 71001, 'libelle' => 'Fournitures', 'nature' => 'depense', 'id_user' => $this->auteur->id]);

    RubriqueOperation::create(['code' => 'R-PAP', 'libelle' => 'Papeterie',    'ligne_id' => $this->ligne->id, 'sens' => 'depense', 'statut' => 1, 'created_by' => $this->auteur->id]);
    RubriqueOperation::create(['code' => 'R-CAR', 'libelle' => 'Cartouches',   'ligne_id' => $this->ligne->id, 'sens' => 'depense', 'statut' => 1, 'created_by' => $this->auteur->id]);
    RubriqueOperation::create(['code' => 'R-REC', 'libelle' => 'Vente actifs', 'ligne_id' => $this->ligne->id, 'sens' => 'recette', 'statut' => 1, 'created_by' => $this->auteur->id]);
    RubriqueOperation::create(['code' => 'R-MIX', 'libelle' => 'Régularisation','ligne_id' => $this->ligne->id, 'sens' => 'mixte',   'statut' => 1, 'created_by' => $this->auteur->id]);

    // Exercice + BudgetLigne pointant sur la Ligne
    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $this->exercice = Exercice::create([
        'exercice' => 'EX-DET-' . random_int(100, 999), 'libelle' => 'Détails',
        'statut' => Exercice::STATUT_PLANIFICATION, 'id_user' => $this->auteur->id,
    ]);
    $this->bl = BudgetLigne::create([
        'id_budgetligne' => 'BL-DET', 'id_exercicebudgetaire' => $this->exercice->id,
        'id_codeanalytique' => $this->ligne->id, 'isvalide' => 0, 'id_user' => $this->auteur->id,
    ]);
});

function mkOrdreDetail(string $sens = 'depense'): Ordre
{
    $modele = OrdreModele::where('code', $sens === 'depense' ? 'ordonnance_paiement' : 'ordre_recette')->first();
    return Ordre::create([
        'modele_id' => $modele->id, 'numero_ordre' => 'DET-' . random_int(1000, 9999),
        'exercice_id' => test()->exercice->id, 'budget_ligne_id' => test()->bl->id,
        'sens' => $sens, 'montant' => 0, 'created_by' => test()->auteur->id,
    ]);
}

it('un ordre expose ses détails ordonnés', function () {
    $ord = mkOrdreDetail();
    OrdreDetail::create(['ordre_id' => $ord->id, 'libelle' => 'A', 'quantite' => 1, 'prix_unitaire' => 100, 'ordre' => 20]);
    OrdreDetail::create(['ordre_id' => $ord->id, 'libelle' => 'B', 'quantite' => 2, 'prix_unitaire' => 50,  'ordre' => 10]);

    expect($ord->details->pluck('libelle')->all())->toBe(['B', 'A']);
});

it('OrdreDetail auto-calcule montant = quantite × prix_unitaire au save', function () {
    $ord = mkOrdreDetail();
    $d = OrdreDetail::create([
        'ordre_id' => $ord->id, 'libelle' => 'Papeterie', 'quantite' => 3, 'prix_unitaire' => 12_500,
    ]);
    expect((float) $d->montant)->toBe(37_500.0);
});

it('montant_total_details agrège les lignes de détail', function () {
    $ord = mkOrdreDetail();
    OrdreDetail::create(['ordre_id' => $ord->id, 'libelle' => 'X', 'quantite' => 1, 'prix_unitaire' => 100_000]);
    OrdreDetail::create(['ordre_id' => $ord->id, 'libelle' => 'Y', 'quantite' => 4, 'prix_unitaire' => 25_000]);
    expect((float) $ord->fresh('details')->montant_total_details)->toBe(200_000.0);
});

it('reventiler() aligne le montant total sur la somme des détails', function () {
    $ord = mkOrdreDetail();
    OrdreDetail::create(['ordre_id' => $ord->id, 'libelle' => 'Z', 'quantite' => 1, 'prix_unitaire' => 42_000]);
    $ord->fresh('details')->reventiler();
    expect((float) $ord->fresh()->montant)->toBe(42_000.0);
});

it('ecart_details signale une divergence entre montant total et somme des détails', function () {
    $ord = mkOrdreDetail();
    $ord->update(['montant' => 100_000]);
    OrdreDetail::create(['ordre_id' => $ord->id, 'libelle' => 'incomplet', 'quantite' => 1, 'prix_unitaire' => 30_000]);
    expect((float) $ord->fresh('details')->ecart_details)->toBe(70_000.0);
});

it('rubriquesDisponibles filtre par ligne budgétaire + sens (depense inclut mixte)', function () {
    $ord = mkOrdreDetail('depense');
    $codes = $ord->rubriquesDisponibles()->pluck('code')->all();

    expect($codes)->toContain('R-PAP', 'R-CAR', 'R-MIX'); // depense + mixte
    expect($codes)->not->toContain('R-REC');              // recette pure exclue
});

it('rubriquesDisponibles pour recette exclut les dépenses pures', function () {
    $ord = mkOrdreDetail('recette');
    $codes = $ord->rubriquesDisponibles()->pluck('code')->all();

    expect($codes)->toContain('R-REC', 'R-MIX');
    expect($codes)->not->toContain('R-PAP', 'R-CAR');
});

it('rubriquesDisponibles retourne vide si pas de budget_ligne_id', function () {
    $modele = OrdreModele::where('code', 'ordonnance_paiement')->first();
    $ord = Ordre::create([
        'modele_id' => $modele->id, 'numero_ordre' => 'NBL-1', 'exercice_id' => $this->exercice->id,
        'sens' => 'depense', 'montant' => 0, 'created_by' => $this->auteur->id,
    ]);
    expect($ord->rubriquesDisponibles()->count())->toBe(0);
});
