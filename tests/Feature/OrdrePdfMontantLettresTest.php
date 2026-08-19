<?php

use App\Models\BudgetLigne;
use App\Models\Compte;
use App\Models\Exercice;
use App\Models\Finance\Ordre;
use App\Models\Finance\OrdreModele;
use App\Models\Ligne;
use App\Models\Titre;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $this->auteur = User::factory()->create();

    $t = Titre::create(['imputation' => 'T-PDF', 'libelle' => 'PDF', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $this->ligne = Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 61099, 'libelle' => 'L', 'nature' => 'depense', 'id_user' => $this->auteur->id]);
    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $this->exercice = Exercice::create([
        'exercice' => 'EX-PDF-' . random_int(100, 999), 'libelle' => 'PDF',
        'statut' => Exercice::STATUT_EN_EXECUTION, 'id_user' => $this->auteur->id,
    ]);
    $this->bl = BudgetLigne::create([
        'id_budgetligne' => 'BL-PDF', 'id_exercicebudgetaire' => $this->exercice->id,
        'id_codeanalytique' => $this->ligne->id, 'id_famillecodeanalytique' => $t->id,
        'dotation_etat' => 5_000_000, 'isvalide' => 1, 'id_user' => $this->auteur->id,
    ]);
    $this->compte = Compte::create([
        'code' => 'PDF-BQ', 'nom' => 'Compte PDF', 'type' => Compte::TYPE_BANQUE,
        'solde' => 5_000_000, 'solde_initial' => 5_000_000, 'devise' => 'XAF', 'actif' => true, 'effacer' => 0,
    ]);
    $this->modele = OrdreModele::where('code', 'ordonnance_paiement')->first();
});

function mkOrdrePdf(int $montant): Ordre {
    return Ordre::create([
        'modele_id' => test()->modele->id,
        'numero_ordre' => sprintf('%04d/BF/PR/ANPI-GABON/DG/DFMG/KAE', random_int(1, 9999)),
        'exercice_id' => test()->exercice->id, 'budget_ligne_id' => test()->bl->id, 'compte_id' => test()->compte->id,
        'sens' => 'depense', 'montant' => $montant, 'statut' => Ordre::STATUT_BROUILLON,
        'created_by' => test()->auteur->id,
        'donnees_json' => ['montant' => $montant, 'mode_reglement' => 'virement'],
    ]);
}

// ═════════ Fix filename PDF ═════════

it('endpoint PDF : les "/" du numéro d\'ordre sont remplacés par "-" dans le filename', function () {
    actingAsSuperAdmin();
    $o = mkOrdrePdf(100_000);
    $r = $this->get(route('finance.ordres.pdf', $o));
    $r->assertOk();

    // Le Content-Disposition ne doit pas contenir de "/"
    $cd = $r->headers->get('Content-Disposition');
    expect($cd)->toBeString();
    expect($cd)->not->toContain('/');
    expect($cd)->toContain('-BF-PR-ANPI-GABON-DG-DFMG-KAE');
});

// ═════════ Montant en lettres ═════════

it('montant_en_lettres : convertit un entier en français avec la devise', function () {
    $o = mkOrdrePdf(1_234_567);
    $lettres = strtolower($o->montant_en_lettres);
    expect($lettres)->toContain('un million');
    expect($lettres)->toContain('deux cent trente-quatre mille');
    expect($lettres)->toContain('cinq cent soixante-sept');
    expect($lettres)->toContain('francs cfa');
});

it('montant_en_lettres : gère les centimes', function () {
    $o = mkOrdrePdf(100);
    $o->update(['montant' => 100.75]);
    $lettres = strtolower($o->fresh()->montant_en_lettres);
    expect($lettres)->toContain('cent francs cfa');
    expect($lettres)->toContain(' et ');
    expect($lettres)->toContain('centimes');
});

it('montant_en_lettres : montant zéro', function () {
    $o = mkOrdrePdf(0);
    expect($o->montant_en_lettres)->toBe('Zéro francs CFA');
});

it('la fiche show affiche le montant en lettres', function () {
    actingAsSuperAdmin();
    $o = mkOrdrePdf(500_000);
    $r = $this->get(route('finance.ordres.show', $o));
    $r->assertOk();
    $r->assertSeeText('Arrêté à la somme de');
    // Le case du texte dans le HTML dépend du ucfirst → assertion case-insensitive
    expect(strtolower(strip_tags($r->getContent())))->toContain('cinq cent mille');
    expect(strtolower(strip_tags($r->getContent())))->toContain('francs cfa');
});

it('adapte la devise selon le compte lié (EUR)', function () {
    $compteEur = Compte::create([
        'code' => 'EUR-01', 'nom' => 'Compte Euro', 'type' => Compte::TYPE_BANQUE,
        'devise' => 'EUR', 'solde' => 1000, 'actif' => true, 'effacer' => 0,
    ]);
    $o = mkOrdrePdf(1500);
    $o->update(['compte_id' => $compteEur->id]);
    $lettres = strtolower($o->fresh()->montant_en_lettres);
    expect($lettres)->toContain('mille cinq cents euros');
});
