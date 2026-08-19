<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Finance\Ordre;
use App\Models\Finance\OrdreModele;
use App\Models\GrandLivre;
use App\Models\Ligne;
use App\Models\Titre;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $this->auteur = \App\Models\User::factory()->create();

    $t = Titre::create(['imputation' => 'T-WF', 'libelle' => 'Workflow', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $this->ligne = Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 51001, 'libelle' => 'L WF', 'nature' => 'depense', 'id_user' => $this->auteur->id]);

    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $this->exercice = Exercice::create([
        'exercice' => 'EX-WF-' . random_int(100, 999), 'libelle' => 'WF',
        'statut' => Exercice::STATUT_EN_EXECUTION, 'validation_statut' => 2, 'id_user' => $this->auteur->id,
    ]);
    // BL avec un budget de 500 000 → solde disponible
    $this->bl = BudgetLigne::create([
        'id_budgetligne' => 'BL-WF', 'id_exercicebudgetaire' => $this->exercice->id,
        'id_codeanalytique' => $this->ligne->id, 'id_famillecodeanalytique' => $t->id,
        'dotation_etat' => 500_000, 'isvalide' => 1, 'id_user' => $this->auteur->id,
    ]);

    $this->modele = OrdreModele::where('code', 'ordonnance_paiement')->first();

    // Compte de trésorerie requis pour l'exécution des ordres
    $this->compte = \App\Models\Compte::create([
        'code' => 'WF-BQ', 'nom' => 'Compte WF', 'type' => \App\Models\Compte::TYPE_BANQUE,
        'solde' => 10_000_000, 'solde_initial' => 10_000_000, 'devise' => 'XAF',
        'actif' => true, 'effacer' => 0,
    ]);
});

function mkOrdreBrouillon(int $montant = 100_000): Ordre
{
    return Ordre::create([
        'modele_id' => test()->modele->id,
        'numero_ordre' => 'WF-' . random_int(1000, 9999),
        'exercice_id' => test()->exercice->id,
        'budget_ligne_id' => test()->bl->id,
        'compte_id' => test()->compte->id,
        'sens' => 'depense',
        'montant' => $montant,
        'statut' => Ordre::STATUT_BROUILLON,
        'donnees_json' => [
            'nature_depense' => 'Test WF',
            'imputation_budget' => (string) test()->ligne->id_codeanalytique,
            'beneficiaire_raison' => 'Fournisseur X',
            'ref_date' => '2027-01-01',
            'montant' => $montant,
            'mode_reglement' => 'virement',
        ],
        'created_by' => test()->auteur->id,
    ]);
}

// ═════════ Workflow transitions ═════════

it('soumettre : brouillon → soumis avec traces', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon();
    $this->post(route('finance.ordres.soumettre', $o))->assertRedirect();
    $o->refresh();
    expect($o->statut)->toBe(Ordre::STATUT_SOUMIS);
    expect($o->soumis_at)->not->toBeNull();
    expect($o->soumis_par)->not->toBeNull();
});

it('soumettre refuse un ordre sans ligne budgétaire', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon();
    $o->update(['budget_ligne_id' => null]);
    $this->post(route('finance.ordres.soumettre', $o))->assertSessionHas('error');
    expect($o->fresh()->statut)->toBe(Ordre::STATUT_BROUILLON);
});

it('signer : soumis → signé + snapshot des signataires', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon();
    $o->soumettre($this->auteur->id);

    $this->post(route('finance.ordres.signer', $o))->assertRedirect();
    $o->refresh();
    expect($o->statut)->toBe(Ordre::STATUT_SIGNE);
    expect($o->signe_at)->not->toBeNull();
    expect($o->signataires_json)->toBeArray();
    // Le modèle ordonnance_paiement a 3 signataires seedés
    expect(count($o->signataires_json))->toBe(3);
});

it('signer refuse si pas encore soumis', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon();
    $this->post(route('finance.ordres.signer', $o))->assertSessionHas('error');
    expect($o->fresh()->statut)->toBe(Ordre::STATUT_BROUILLON);
});

it('executer : signé → exécuté + écriture Grand Livre créée', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon(100_000);
    $o->soumettre($this->auteur->id);
    $o->signer($this->auteur->id);

    $glAvant = GrandLivre::count();
    $this->post(route('finance.ordres.executer', $o))->assertRedirect()->assertSessionHas('success');

    $o->refresh();
    expect($o->statut)->toBe(Ordre::STATUT_EXECUTE);
    expect($o->grand_livre_id)->not->toBeNull();
    expect(GrandLivre::count())->toBe($glAvant + 1);
});

it('executer : montant_signe_tc est négatif pour une dépense', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon(75_000);
    $o->soumettre($this->auteur->id);
    $o->signer($this->auteur->id);
    $this->post(route('finance.ordres.executer', $o));

    $gl = GrandLivre::find($o->fresh()->grand_livre_id);
    expect((float) $gl->montant_tc)->toBe(75_000.0);
    expect((float) $gl->montant_signe_tc)->toBe(-75_000.0);
    expect($gl->sens)->toBe('depense');
});

it('executer : montant_signe_tc est positif pour une recette', function () {
    actingAsSuperAdmin();
    $modeleRec = OrdreModele::where('code', 'ordre_recette')->first();
    $o = Ordre::create([
        'modele_id' => $modeleRec->id, 'numero_ordre' => 'REC-1', 'exercice_id' => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id, 'compte_id' => $this->compte->id,
        'sens' => 'recette', 'montant' => 200_000,
        'statut' => Ordre::STATUT_BROUILLON, 'created_by' => $this->auteur->id,
        'donnees_json' => [
            'nature_recette' => 'Vente', 'imputation_budget' => (string) $this->ligne->id_codeanalytique,
            'date_emission' => '2027-02-01', 'montant' => 200_000,
        ],
    ]);
    $o->soumettre($this->auteur->id);
    $o->signer($this->auteur->id);
    $this->post(route('finance.ordres.executer', $o));

    $gl = GrandLivre::find($o->fresh()->grand_livre_id);
    expect((float) $gl->montant_signe_tc)->toBe(200_000.0);
    expect($gl->sens)->toBe('recette');
});

it('executer : les champs mappés du modèle sont recopiés dans le GL', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon();
    $o->soumettre($this->auteur->id);
    $o->signer($this->auteur->id);
    $this->post(route('finance.ordres.executer', $o));

    $gl = GrandLivre::find($o->fresh()->grand_livre_id);
    // imputation forcée depuis la ligne budgétaire
    expect($gl->imputation)->toBe((string) $this->ligne->id_codeanalytique);
    expect($gl->nature)->toBe('Test WF');
    // La colonne `beneficiaire` du GL est désormais alimentée par le bloc structuré
    // Bénéficiaire (ordre.beneficiaire_source), pas par un champ modèle. Ce cas est
    // couvert dans OrdreBeneficiaireJustificatifsTest.
    expect($gl->mode_reglement)->toBe('virement');
    expect($gl->num_piece)->toBe($o->numero_ordre);
});

it('executer : refuse si solde budgétaire insuffisant', function () {
    actingAsSuperAdmin();
    // BL avec seulement 500k, ordre à 600k
    $o = mkOrdreBrouillon(600_000);
    $o->soumettre($this->auteur->id);
    $o->signer($this->auteur->id);

    $this->post(route('finance.ordres.executer', $o))->assertSessionHas('error');
    expect($o->fresh()->statut)->toBe(Ordre::STATUT_SIGNE);
});

it('executer : incrémente l\'engagement de la ligne budgétaire', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon(150_000);
    $o->soumettre($this->auteur->id);
    $o->signer($this->auteur->id);
    $engagementAvant = (float) $this->bl->fresh()->engagement;

    $this->post(route('finance.ordres.executer', $o));

    expect((float) $this->bl->fresh()->engagement)->toBe($engagementAvant + 150_000.0);
});

// ═════════ Annulation ═════════

it('annuler : avec motif valide (min 10 caractères)', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon();
    $o->soumettre($this->auteur->id);

    $this->post(route('finance.ordres.annuler', $o), [
        'motif_annulation' => 'Décision hiérarchique de dernière minute.',
    ])->assertRedirect()->assertSessionHas('success');

    $o->refresh();
    expect($o->statut)->toBe(Ordre::STATUT_ANNULE);
    expect($o->motif_annulation)->toContain('hiérarchique');
    expect($o->annule_at)->not->toBeNull();
});

it('annuler : motif trop court refusé', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon();
    $this->post(route('finance.ordres.annuler', $o), ['motif_annulation' => 'court'])
        ->assertSessionHasErrors('motif_annulation');
});

it('annuler : un ordre exécuté ne peut plus être annulé', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon();
    $o->soumettre($this->auteur->id);
    $o->signer($this->auteur->id);
    $this->post(route('finance.ordres.executer', $o));

    $this->post(route('finance.ordres.annuler', $o), [
        'motif_annulation' => 'On aurait bien voulu annuler mais trop tard.',
    ])->assertSessionHas('error');

    expect($o->fresh()->statut)->toBe(Ordre::STATUT_EXECUTE);
});

// ═════════ PDF ═════════

it('endpoint PDF génère un fichier téléchargeable', function () {
    actingAsSuperAdmin();
    $o = mkOrdreBrouillon();

    $r = $this->get(route('finance.ordres.pdf', $o));
    $r->assertOk();
    expect($r->headers->get('content-type'))->toContain('pdf');
});
