<?php

use App\Models\Finance\Ordre;
use App\Models\Finance\OrdreModele;
use App\Models\Finance\OrdreModeleChamp;
use App\Models\Finance\OrdreModeleSignataire;

beforeEach(function () {
    seedRoles();
});

it('les modèles par défaut sont seedés (ordre_recette, ordonnance_paiement)', function () {
    (new \Database\Seeders\OrdreModelesSeeder())->run();

    $recette = OrdreModele::where('code', 'ordre_recette')->first();
    $depense = OrdreModele::where('code', 'ordonnance_paiement')->first();

    expect($recette)->not->toBeNull();
    expect($depense)->not->toBeNull();
    expect($recette->sens)->toBe('recette');
    expect($depense->sens)->toBe('depense');
});

it('le seeder est idempotent — deux exécutions ne créent pas de doublons', function () {
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    (new \Database\Seeders\OrdreModelesSeeder())->run();

    expect(OrdreModele::where('code', 'ordre_recette')->count())->toBe(1);
    expect(OrdreModele::where('code', 'ordonnance_paiement')->count())->toBe(1);
});

it('un modèle expose ses champs et signataires ordonnés', function () {
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $mod = OrdreModele::where('code', 'ordonnance_paiement')->first();

    // 9 champs depuis retrait de RAISON SOCIALE / ADRESSE (déplacés vers bloc Bénéficiaire)
    expect($mod->champs->count())->toBe(9);
    expect($mod->signataires->count())->toBe(3);
    expect($mod->champs->pluck('ordre')->all())->toBe([10, 20, 30, 40, 50, 80, 90, 100, 110]);
    expect($mod->signataires->pluck('ordre')->all())->toBe([10, 20, 30]);
});

it('genererNumero() applique le format zero-paddé et remplacements', function () {
    $mod = OrdreModele::create([
        'code' => 'test_num', 'libelle' => 'T', 'sens' => 'recette',
        'numerotation_format' => '{n:04d}/BF/{annee}/{code}',
    ]);
    expect($mod->genererNumero(7, 2027))->toBe('0007/BF/2027/test_num');
    expect($mod->genererNumero(123, 2028))->toBe('0123/BF/2028/test_num');
});

it('genererNumero() par défaut sans format applique {n:04d} zero-paddé', function () {
    $mod = OrdreModele::create([
        'code' => 'simple', 'libelle' => 'S', 'sens' => 'depense',
    ]);
    expect($mod->genererNumero(3))->toBe('0003');
});

it('Ordre expose montant_signe négatif pour dépense et positif pour recette', function () {
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $ex = \App\Models\Exercice::create([
        'exercice' => 'EX-ORDRE-1', 'libelle' => 'Test', 'statut' => 1,
        'id_user' => \App\Models\User::factory()->create()->id,
    ]);
    $modDep = OrdreModele::where('code', 'ordonnance_paiement')->first();
    $modRec = OrdreModele::where('code', 'ordre_recette')->first();

    $ordreDep = Ordre::create([
        'modele_id' => $modDep->id, 'numero_ordre' => 'D-1', 'exercice_id' => $ex->id,
        'sens' => 'depense', 'montant' => 100_000, 'created_by' => $ex->id_user,
    ]);
    $ordreRec = Ordre::create([
        'modele_id' => $modRec->id, 'numero_ordre' => 'R-1', 'exercice_id' => $ex->id,
        'sens' => 'recette', 'montant' => 250_000, 'created_by' => $ex->id_user,
    ]);

    expect((float) $ordreDep->montant_signe)->toBe(-100_000.0);
    expect((float) $ordreRec->montant_signe)->toBe(250_000.0);
});

it('workflow méthodes : peutEtreModifie/Soumis/Signe/Execute/Annule', function () {
    $ex = \App\Models\Exercice::create([
        'exercice' => 'EX-WF', 'libelle' => 'WF', 'statut' => 1,
        'id_user' => \App\Models\User::factory()->create()->id,
    ]);
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $mod = OrdreModele::where('code', 'ordre_recette')->first();

    $ordre = Ordre::create([
        'modele_id' => $mod->id, 'numero_ordre' => 'WF-1', 'exercice_id' => $ex->id,
        'sens' => 'recette', 'montant' => 1_000, 'created_by' => $ex->id_user,
        'statut' => Ordre::STATUT_BROUILLON,
    ]);
    expect($ordre->peutEtreModifie())->toBeTrue();
    expect($ordre->peutEtreSoumis())->toBeTrue();
    expect($ordre->peutEtreSigne())->toBeFalse();
    expect($ordre->peutEtreAnnule())->toBeTrue();

    $ordre->update(['statut' => Ordre::STATUT_EXECUTE]);
    expect($ordre->peutEtreModifie())->toBeFalse();
    expect($ordre->peutEtreAnnule())->toBeFalse();
});

it('le champ OrdreModeleChamp expose les colonnes GL mappables', function () {
    expect(OrdreModeleChamp::CHAMPS_GL_DISPONIBLES)->toBeArray();
    expect(OrdreModeleChamp::CHAMPS_GL_DISPONIBLES)->toHaveKeys([
        'date_ecriture', 'imputation', 'beneficiaire', 'montant_tc', 'mode_reglement',
    ]);
});

it('scopes sens : depense/recette filtrent correctement', function () {
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    expect(OrdreModele::depense()->count())->toBe(1);
    expect(OrdreModele::recette()->count())->toBe(1);
});
