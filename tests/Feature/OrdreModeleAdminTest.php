<?php

use App\Models\Finance\OrdreModele;
use App\Models\Finance\OrdreModeleChamp;
use App\Models\Finance\OrdreModeleSignataire;

beforeEach(function () {
    seedRoles();
});

it('index affiche les modèles avec counts', function () {
    actingAsSuperAdmin();
    (new \Database\Seeders\OrdreModelesSeeder())->run();

    $r = $this->get(route('finance.referentiels.ordres-modeles.index'));
    $r->assertOk();
    $r->assertSee('Ordre de recette');
    $r->assertSee('Ordonnance de paiement');
});

it('création POST valide un code snake_case et refuse un doublon', function () {
    actingAsSuperAdmin();

    $this->post(route('finance.referentiels.ordres-modeles.store'), [
        'code' => 'MAUVAIS-CODE', 'libelle' => 'X', 'sens' => 'depense',
    ])->assertSessionHasErrors('code');

    $this->post(route('finance.referentiels.ordres-modeles.store'), [
        'code' => 'test_ok', 'libelle' => 'Test OK', 'sens' => 'depense',
    ])->assertRedirect();

    expect(OrdreModele::where('code', 'test_ok')->exists())->toBeTrue();

    $this->post(route('finance.referentiels.ordres-modeles.store'), [
        'code' => 'test_ok', 'libelle' => 'Doublon', 'sens' => 'recette',
    ])->assertSessionHasErrors('code');
});

it('ajout / suppression d\'un champ dans un modèle', function () {
    actingAsSuperAdmin();
    $m = OrdreModele::create(['code' => 'm1', 'libelle' => 'M1', 'sens' => 'depense']);

    $this->post(route('finance.referentiels.ordres-modeles.champs.store', $m), [
        'code_champ' => 'imputation_budget',
        'label_personnalise' => 'IMPUTATION BUDGETAIRE',
        'type_saisie' => 'text',
        'mapping_gl' => 'imputation',
        'largeur_col' => 3,
        'obligatoire' => 1,
    ])->assertRedirect();

    $c = $m->champs()->first();
    expect($c)->not->toBeNull();
    expect($c->obligatoire)->toBeTrue();
    expect($c->mapping_gl)->toBe('imputation');

    $this->delete(route('finance.referentiels.ordres-modeles.champs.destroy', [$m, $c]))
        ->assertRedirect();
    expect($m->champs()->count())->toBe(0);
});

it('type select : les options_texte sont parsées en options_json (code=libellé)', function () {
    actingAsSuperAdmin();
    $m = OrdreModele::create(['code' => 'm2', 'libelle' => 'M2', 'sens' => 'depense']);

    $this->post(route('finance.referentiels.ordres-modeles.champs.store', $m), [
        'code_champ' => 'mode_reglement',
        'label_personnalise' => 'MODE DE REGLEMENT',
        'type_saisie' => 'select',
        'options_texte' => "numeraire=Numéraire\ncheque=Chèque\nvirement=Virement bancaire",
    ])->assertRedirect();

    $c = $m->champs()->first();
    expect($c->options_json)->toBe([
        'numeraire' => 'Numéraire',
        'cheque'    => 'Chèque',
        'virement'  => 'Virement bancaire',
    ]);
});

it('réordonner via AJAX applique les nouveaux ordres', function () {
    actingAsSuperAdmin();
    $m = OrdreModele::create(['code' => 'm3', 'libelle' => 'M3', 'sens' => 'depense']);
    $a = $m->champs()->create(['code_champ' => 'a', 'label_personnalise' => 'A', 'type_saisie' => 'text', 'ordre' => 100]);
    $b = $m->champs()->create(['code_champ' => 'b', 'label_personnalise' => 'B', 'type_saisie' => 'text', 'ordre' => 200]);
    $c = $m->champs()->create(['code_champ' => 'c', 'label_personnalise' => 'C', 'type_saisie' => 'text', 'ordre' => 300]);

    $this->post(route('finance.referentiels.ordres-modeles.champs.reorder', $m), [
        'ordre' => [$c->id, $a->id, $b->id],
    ])->assertJson(['ok' => true]);

    expect($m->fresh()->champs->pluck('code_champ')->all())->toBe(['c', 'a', 'b']);
});

it('ajouter un signataire avec ordre automatique', function () {
    actingAsSuperAdmin();
    $m = OrdreModele::create(['code' => 'm4', 'libelle' => 'M4', 'sens' => 'recette']);

    $this->post(route('finance.referentiels.ordres-modeles.signataires.store', $m), [
        'role_libelle' => "L'AGENT COMPTABLE",
    ])->assertRedirect();
    $this->post(route('finance.referentiels.ordres-modeles.signataires.store', $m), [
        'role_libelle' => 'LE DIRECTEUR GENERAL',
    ])->assertRedirect();

    expect($m->signataires->pluck('ordre')->all())->toBe([10, 20]);
});

it('supprimer un modèle utilisé par des ordres est refusé', function () {
    actingAsSuperAdmin();
    $m = OrdreModele::create(['code' => 'm5', 'libelle' => 'M5', 'sens' => 'depense']);
    $ex = \App\Models\Exercice::create([
        'exercice' => 'EX-DEL', 'libelle' => 'X', 'statut' => 1,
        'id_user' => \App\Models\User::factory()->create()->id,
    ]);
    \App\Models\Finance\Ordre::create([
        'modele_id' => $m->id, 'numero_ordre' => 'PROT-1', 'exercice_id' => $ex->id,
        'sens' => 'depense', 'montant' => 0, 'created_by' => $ex->id_user,
    ]);

    $this->delete(route('finance.referentiels.ordres-modeles.destroy', $m))
        ->assertSessionHas('error');
    expect(OrdreModele::find($m->id))->not->toBeNull();
});

it('mapping_gl invalide est refusé', function () {
    actingAsSuperAdmin();
    $m = OrdreModele::create(['code' => 'm6', 'libelle' => 'M6', 'sens' => 'depense']);

    $this->post(route('finance.referentiels.ordres-modeles.champs.store', $m), [
        'code_champ' => 'x',
        'label_personnalise' => 'X',
        'type_saisie' => 'text',
        'mapping_gl' => 'colonne_inventee_pas_dans_grand_livre',
    ])->assertSessionHasErrors('mapping_gl');
});
