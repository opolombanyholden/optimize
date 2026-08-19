<?php

use App\Models\CampagnePaie;
use App\Models\Compte;
use App\Models\Employee;
use App\Models\Exercice;
use App\Models\GrandLivre;
use App\Models\Paie;
use App\Services\Finance\PaieEcritureService;
use App\Services\Rh\PaieCalculator;
use Database\Seeders\ComptesPaieOhadaSeeder;
use Database\Seeders\RhRubriquesSeeder;

beforeEach(function () {
    seedRoles();
    (new RhRubriquesSeeder())->run();
    (new ComptesPaieOhadaSeeder())->run();
});

function makeExerciceEcritures(): Exercice
{
    Exercice::where('statut', 2)->update(['statut' => 3]);
    return Exercice::create([
        'exercice' => '2026',
        'libelle'  => 'Test 2026',
        'statut'   => 2,
        'datedebut'=> '2026-01-01',
        'datefin'  => '2026-12-31',
    ]);
}

function makeCampagneEcritures(int $nbEmployes = 3, float $salaireBase = 600_000): CampagnePaie
{
    $campagne = CampagnePaie::create([
        'code'        => 'CP-2026-05-TEST',
        'libelle'     => 'Test Paie Mai',
        'annee'       => 2026,
        'mois'        => 5,
        'periodicite' => 'mensuelle',
        'simulation'  => false,
        'statut'      => 1,
        'date_debut'  => '2026-05-01',
        'date_fin'    => '2026-05-31',
        'created_by'  => auth()->id(),
    ]);

    $calc = new PaieCalculator();
    for ($i = 0; $i < $nbEmployes; $i++) {
        $emp = Employee::create([
            'noms'    => "Emp$i",
            'prenoms' => 'Test',
            'matricule' => 'E-' . random_int(10000, 99999),
            'email'   => "ec$i" . random_int(100, 999) . '@t.l',
            'salaire_base' => $salaireBase,
            'type_contrat' => 'CDI',
            'date_embauche'=> now()->subYears(2),
            'statut'  => 1,
            'matricule_cnss'   => 'CNSS-' . random_int(100, 999),
            'matricule_cnamgs' => 'CNAM-' . random_int(100, 999),
        ]);
        $r = $calc->calculer([
            'employee_id'  => $emp->id,
            'debut'        => '2026-05-01',
            'fin'          => '2026-05-31',
            'salaire_base' => $salaireBase,
        ]);
        $r['bulletin']['campagne_paie_id'] = $campagne->id;
        $r['bulletin']['statut'] = 1;
        $calc->persister($r);
    }
    return $campagne->fresh();
}

// ─── SERVICE GÉNÉRATION ÉCRITURES ──────────────────────────

it('exige un exercice en cours pour générer les écritures', function () {
    actingAsSuperAdmin();
    Exercice::where('statut', 2)->update(['statut' => 3]);
    $campagne = makeCampagneEcritures(2);
    $service = new PaieEcritureService();

    expect(fn() => $service->genererPourCampagne($campagne))
        ->toThrow(\RuntimeException::class, 'Aucun exercice');
});

it('refuse de générer si aucun bulletin validé', function () {
    actingAsSuperAdmin();
    makeExerciceEcritures();
    $campagne = CampagnePaie::create([
        'code' => 'CP-EMPTY', 'libelle' => 'Vide',
        'annee' => 2026, 'mois' => 5, 'periodicite' => 'mensuelle',
        'simulation' => false, 'statut' => 1,
        'date_debut' => '2026-05-01', 'date_fin' => '2026-05-31',
    ]);
    $service = new PaieEcritureService();

    expect(fn() => $service->genererPourCampagne($campagne))
        ->toThrow(\RuntimeException::class, 'Aucun bulletin');
});

it('génère des écritures équilibrées (Σ débits = Σ crédits)', function () {
    actingAsSuperAdmin();
    makeExerciceEcritures();
    $campagne = makeCampagneEcritures(3, 800_000);
    $service = new PaieEcritureService();

    $r = $service->genererPourCampagne($campagne, auth()->id());

    expect($r['created'])->toBeGreaterThan(0);
    expect($r['equilibre'])->toBeTrue();
    expect(abs($r['delta']))->toBeLessThan(0.5); // tolérance arrondi
});

it('est idempotent : 2e appel ne crée pas de doublon', function () {
    actingAsSuperAdmin();
    makeExerciceEcritures();
    $campagne = makeCampagneEcritures(2);
    $service = new PaieEcritureService();

    $r1 = $service->genererPourCampagne($campagne, auth()->id());
    $r2 = $service->genererPourCampagne($campagne, auth()->id());

    expect($r1['created'])->toBeGreaterThan(0);
    expect($r2['created'])->toBe(0);
    expect($r2['existant'])->toBe($r1['created']);
});

it('attribue le bon num_lot et journal', function () {
    actingAsSuperAdmin();
    makeExerciceEcritures();
    $campagne = makeCampagneEcritures(1);
    $service = new PaieEcritureService();

    $r = $service->genererPourCampagne($campagne, auth()->id());

    expect($r['num_lot'])->toBe("PAIE-2026-05-CP{$campagne->id}");

    $ecriture = GrandLivre::where('ref_piece', $campagne->code)->where('nature', 'paie')->first();
    expect($ecriture->journal)->toBe('PAIE');
    expect($ecriture->nature)->toBe('paie');
    expect($ecriture->type_piece)->toBe('OD');
});

it('crée bien les écritures DÉBIT (charges) et CRÉDIT (tiers)', function () {
    actingAsSuperAdmin();
    makeExerciceEcritures();
    $campagne = makeCampagneEcritures(2, 800_000);
    $service = new PaieEcritureService();

    $service->genererPourCampagne($campagne, auth()->id());

    $debits  = GrandLivre::where('ref_piece', $campagne->code)->where('nature', 'paie')->where('sens', 'debit')->get();
    $credits = GrandLivre::where('ref_piece', $campagne->code)->where('nature', 'paie')->where('sens', 'credit')->get();

    expect($debits->count())->toBeGreaterThan(0);
    expect($credits->count())->toBeGreaterThan(0);

    // Au moins le compte 661100 (Salaire de base) en débit
    expect($debits->pluck('compte_general')->all())->toContain('661100');
    // Et le compte 421100 (Personnel rémunérations dues) en crédit
    expect($credits->pluck('compte_general')->all())->toContain('421100');
});

it('annule les écritures d\'une campagne', function () {
    actingAsSuperAdmin();
    makeExerciceEcritures();
    $campagne = makeCampagneEcritures(2);
    $service = new PaieEcritureService();

    $service->genererPourCampagne($campagne, auth()->id());
    expect(GrandLivre::where('ref_piece', $campagne->code)->where('nature', 'paie')->count())->toBeGreaterThan(0);

    $supprimes = $service->annulerPourCampagne($campagne);

    expect($supprimes)->toBeGreaterThan(0);
    expect(GrandLivre::where('ref_piece', $campagne->code)->where('nature', 'paie')->count())->toBe(0);
});

// ─── HOOK AUTO SUR VALIDATION ──────────────────────────────

it('la validation d\'une campagne réelle crée les écritures automatiquement', function () {
    actingAsSuperAdmin();
    makeExerciceEcritures();
    $campagne = makeCampagneEcritures(2);

    expect(GrandLivre::where('ref_piece', $campagne->code)->where('nature', 'paie')->count())->toBe(0);

    $this->post("/rh/campagnes-paie/{$campagne->id}/valider")->assertRedirect();

    $campagne->refresh();
    expect($campagne->statut)->toBe(2);
    expect(GrandLivre::where('ref_piece', $campagne->code)->where('nature', 'paie')->count())->toBeGreaterThan(0);
});

it('la validation d\'une campagne en mode simulation NE crée PAS d\'écritures', function () {
    actingAsSuperAdmin();
    makeExerciceEcritures();
    $campagne = makeCampagneEcritures(2);
    $campagne->update(['simulation' => true]);

    $this->post("/rh/campagnes-paie/{$campagne->id}/valider")->assertRedirect();

    expect(GrandLivre::where('ref_piece', $campagne->code)->where('nature', 'paie')->count())->toBe(0);
});

it('la validation ne bloque pas si la compta plante (pas d\'exercice)', function () {
    actingAsSuperAdmin();
    Exercice::where('statut', 2)->update(['statut' => 3]); // aucun exercice en cours
    $campagne = makeCampagneEcritures(2);

    $this->post("/rh/campagnes-paie/{$campagne->id}/valider")->assertRedirect();

    // Campagne validée même si pas d'écritures
    expect($campagne->fresh()->statut)->toBe(2);
    expect(GrandLivre::where('ref_piece', $campagne->code)->where('nature', 'paie')->count())->toBe(0);
});

// ─── FILTRE GRAND-LIVRE PAR CAMPAGNE ───────────────────────

it('on peut filtrer les écritures du grand-livre par campagne_paie_id', function () {
    actingAsSuperAdmin();
    makeExerciceEcritures();
    $campagne = makeCampagneEcritures(2);
    (new PaieEcritureService())->genererPourCampagne($campagne, auth()->id());

    $resp = $this->get("/finance/grand-livre?ref_piece={$campagne->code}");
    $resp->assertOk();
    // Le filtre fonctionne : on retrouve au moins une écriture liée à la campagne (libellé contient le code)
    $resp->assertSee($campagne->code);
});
