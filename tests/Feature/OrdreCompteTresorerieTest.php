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

    $t = Titre::create(['imputation' => 'T-C', 'libelle' => 'C', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $this->ligne = Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 71001, 'libelle' => 'L C', 'nature' => 'depense', 'id_user' => $this->auteur->id]);
    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $this->exercice = Exercice::create([
        'exercice' => 'EX-C-' . random_int(100, 999), 'libelle' => 'C',
        'statut' => Exercice::STATUT_EN_EXECUTION, 'id_user' => $this->auteur->id,
    ]);
    $this->bl = BudgetLigne::create([
        'id_budgetligne' => 'BL-C', 'id_exercicebudgetaire' => $this->exercice->id,
        'id_codeanalytique' => $this->ligne->id, 'id_famillecodeanalytique' => $t->id,
        'dotation_etat' => 10_000_000, 'isvalide' => 1, 'id_user' => $this->auteur->id,
    ]);
    $this->modele = OrdreModele::where('code', 'ordonnance_paiement')->first();

    // Trois comptes de trésorerie
    $this->cptBanque = Compte::create(['code' => 'BQ01', 'nom' => 'BGFI Bank', 'type' => Compte::TYPE_BANQUE, 'solde' => 5_000_000, 'solde_initial' => 5_000_000, 'devise' => 'XAF', 'actif' => true, 'effacer' => 0]);
    $this->cptCaisse = Compte::create(['code' => 'CS01', 'nom' => 'Caisse principale', 'type' => Compte::TYPE_CAISSE, 'solde' => 200_000, 'solde_initial' => 200_000, 'devise' => 'XAF', 'actif' => true, 'effacer' => 0]);
    $this->cptWallet = Compte::create(['code' => 'MM01', 'nom' => 'Airtel Money DG', 'type' => Compte::TYPE_ELECTRONIQUE, 'solde' => 500_000, 'solde_initial' => 500_000, 'devise' => 'XAF', 'actif' => true, 'effacer' => 0]);
});

function payloadOrdreCpt(array $extra = []): array {
    $base = [
        'modele_id'       => test()->modele->id,
        'exercice_id'     => test()->exercice->id,
        'budget_ligne_id' => test()->bl->id,
        'donnees' => [
            'nature_depense' => 'X', 'imputation_budget' => '0',
            'beneficiaire_raison' => 'Y', 'ref_date' => '2027-01-01',
            'montant' => 100_000, 'mode_reglement' => 'virement',
        ],
    ];
    // Merge donnees séparément pour permettre l'override des sous-clés
    if (isset($extra['donnees'])) {
        $base['donnees'] = array_merge($base['donnees'], $extra['donnees']);
        unset($extra['donnees']);
    }
    return array_merge($base, $extra);
}

// ═════════ Modèle Compte ═════════

it('Compte expose types Bancaire / Caisse / Électronique / Tiers', function () {
    expect(Compte::TYPES)->toHaveKeys([1, 2, 3, 4]);
    expect(Compte::TYPES[Compte::TYPE_ELECTRONIQUE])->toBe('Électronique (Mobile Money, e-wallet)');
});

it('scopes actif/banque/caisse/electronique/tresorerie filtrent correctement', function () {
    expect(Compte::actif()->count())->toBe(3);
    expect(Compte::banque()->count())->toBe(1);
    expect(Compte::caisse()->count())->toBe(1);
    expect(Compte::electronique()->count())->toBe(1);
    expect(Compte::tresorerie()->count())->toBe(3);
});

it('scope modeReglement : numeraire → caisse ; cheque → banque ; virement → banque + électronique', function () {
    expect(Compte::actif()->modeReglement('numeraire')->pluck('id')->all())
        ->toBe([$this->cptCaisse->id]);
    expect(Compte::actif()->modeReglement('cheque')->pluck('id')->all())
        ->toBe([$this->cptBanque->id]);
    expect(Compte::actif()->modeReglement('virement')->pluck('id')->all())
        ->toContain($this->cptBanque->id, $this->cptWallet->id);
});

// ═════════ Ordre avec compte ═════════

it('store persiste compte_id sur l\'ordre', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), payloadOrdreCpt([
        'compte_id' => $this->cptBanque->id,
    ]))->assertRedirect();

    $o = Ordre::latest()->first();
    expect($o->compte_id)->toBe($this->cptBanque->id);
    expect($o->compte->nom)->toBe('BGFI Bank');
});

it('exécution : refuse si aucun compte n\'a été renseigné', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), payloadOrdreCpt());
    $o = Ordre::latest()->first();
    $o->soumettre($this->auteur->id);
    $o->signer($this->auteur->id);

    $this->post(route('finance.ordres.executer', $o))
        ->assertSessionHas('error');
    expect($o->fresh()->statut)->toBe(Ordre::STATUT_SIGNE);
});

it('exécution : débit d\'un compte pour une dépense (solde diminue)', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), payloadOrdreCpt([
        'compte_id' => $this->cptBanque->id,
        'donnees' => ['montant' => 300_000],
    ]));
    $o = Ordre::latest()->first();
    $o->soumettre($this->auteur->id);
    $o->signer($this->auteur->id);

    $this->post(route('finance.ordres.executer', $o))->assertRedirect();
    expect((float) $this->cptBanque->fresh()->solde)->toBe(4_700_000.0); // 5 000 000 - 300 000
});

it('exécution : crédit d\'un compte pour une recette (solde augmente)', function () {
    actingAsSuperAdmin();
    $modRec = OrdreModele::where('code', 'ordre_recette')->first();

    $o = Ordre::create([
        'modele_id' => $modRec->id, 'numero_ordre' => 'REC-CPT', 'exercice_id' => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id, 'compte_id' => $this->cptCaisse->id,
        'sens' => 'recette', 'montant' => 150_000, 'statut' => Ordre::STATUT_SIGNE,
        'created_by' => $this->auteur->id,
        'donnees_json' => ['montant' => 150_000, 'mode_reglement' => 'numeraire'],
    ]);
    $this->post(route('finance.ordres.executer', $o))->assertRedirect();
    expect((float) $this->cptCaisse->fresh()->solde)->toBe(350_000.0); // 200 000 + 150 000
});

it('l\'écriture au Grand Livre utilise le compte_id de l\'ordre', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), payloadOrdreCpt([
        'compte_id' => $this->cptWallet->id,
    ]));
    $o = Ordre::latest()->first();
    $o->soumettre($this->auteur->id);
    $o->signer($this->auteur->id);
    $this->post(route('finance.ordres.executer', $o));

    $gl = \App\Models\GrandLivre::find($o->fresh()->grand_livre_id);
    expect($gl->compte_id)->toBe($this->cptWallet->id);
});

it('le formulaire liste les comptes actifs de trésorerie', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('finance.ordres.create', ['modele' => $this->modele->id]));
    $r->assertOk();
    $r->assertSee('BGFI Bank');
    $r->assertSee('Caisse principale');
    $r->assertSee('Airtel Money DG');
});

// ═════════ Admin comptes ═════════

it('CRUD compte : store crée avec devise et actif par défaut', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.comptes.store'), [
        'nom' => 'Nouveau compte', 'type' => Compte::TYPE_ELECTRONIQUE,
        'solde_initial' => 100_000,
    ])->assertRedirect();

    $c = Compte::where('nom', 'Nouveau compte')->first();
    expect($c)->not->toBeNull();
    expect($c->type)->toBe(Compte::TYPE_ELECTRONIQUE);
    expect($c->actif)->toBeTrue();
    expect((float) $c->solde_initial)->toBe(100_000.0);
    expect((float) $c->solde)->toBe(100_000.0);
});

it('index affiche les KPI de trésorerie', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('finance.comptes.index'));
    $r->assertOk();
    $r->assertSee('Bancaire');
    $r->assertSee('Caisse');
    $r->assertSee('Électronique');
});

it('suppression refusée si compte utilisé dans un ordre exécuté', function () {
    actingAsSuperAdmin();
    Ordre::create([
        'modele_id' => $this->modele->id, 'numero_ordre' => 'PROT', 'exercice_id' => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id, 'compte_id' => $this->cptBanque->id,
        'sens' => 'depense', 'montant' => 100, 'statut' => Ordre::STATUT_EXECUTE,
        'created_by' => $this->auteur->id,
    ]);
    $this->delete(route('finance.comptes.destroy', $this->cptBanque))
        ->assertSessionHas('error');
    expect(Compte::find($this->cptBanque->id))->not->toBeNull();
});
