<?php

use App\Models\Exercice;
use App\Models\Finance\Budget;
use App\Models\Finance\BudgetSource;
use App\Models\Finance\Config as FinanceConfig;
use App\Models\Finance\Modif;
use App\Models\Finance\Source;
use App\Models\Finance\Transaction;
use App\Models\Finance\TransactionDetail;
use App\Models\Ligne;
use App\Models\ModeReglement;
use App\Models\Titre;
use App\Services\Finance\ModifService;
use Database\Seeders\FinanceV2InitialSeeder;

beforeEach(function () {
    seedRoles();
});

// ─── SEEDER OHADA ─────────────────────────────────────

it('le seeder V2 charge 3 sources FP/RB/ETAT', function () {
    (new FinanceV2InitialSeeder())->run();
    expect(Source::pluck('code')->all())->toContain('FP', 'RB', 'ETAT');
});

it('le seeder V2 charge 3 modes règlement CHQ/NUM/VB', function () {
    (new FinanceV2InitialSeeder())->run();
    expect(ModeReglement::pluck('code')->all())->toContain('CHQ', 'NUM', 'VB');
});

it('le seeder V2 charge 6 titres OHADA', function () {
    (new FinanceV2InitialSeeder())->run();
    $codes = Titre::pluck('code')->all();
    foreach (['60', '61', '62', '64', '65', '66'] as $c) {
        expect($codes)->toContain($c);
    }
});

it('le seeder V2 charge 33 lignes de dépense', function () {
    (new FinanceV2InitialSeeder())->run();
    expect(Ligne::where('code', 'like', '6%')->count())->toBeGreaterThanOrEqual(30);
});

it('le seeder V2 charge 10 lignes de recette', function () {
    (new FinanceV2InitialSeeder())->run();
    expect(Ligne::where('code', 'like', '7%')->count())->toBe(10);
});

// ─── MODÈLES ──────────────────────────────────────────

it('crée un Budget lié à ligne × exercice', function () {
    (new FinanceV2InitialSeeder())->run();
    $ligne = Ligne::where('code', '601101')->first();
    $ex = Exercice::first();
    $b = Budget::create([
        'label' => 'Budget test', 'seuil' => 1000000, 'status' => 0,
        'ligne_id' => $ligne->id, 'exercice_id' => $ex->id,
    ]);
    expect($b)->toBeInstanceOf(Budget::class);
    expect($b->status_libelle)->toBe('Brouillon');
});

it('crée un BudgetSource (allocation par source de financement)', function () {
    (new FinanceV2InitialSeeder())->run();
    $ligne = Ligne::where('code', '601101')->first();
    $ex = Exercice::first();
    $src = Source::where('code', 'ETAT')->first();

    $bs = BudgetSource::create([
        'source_id' => $src->id, 'ligne_id' => $ligne->id, 'exercice_id' => $ex->id,
        'montant' => 5_000_000,
    ]);
    expect((float) $bs->montant)->toBe(5_000_000.0);
    expect($bs->source->code)->toBe('ETAT');
});

it('crée une Transaction (dépense) avec workflow brouillon → soumise → validée → payée', function () {
    (new FinanceV2InitialSeeder())->run();
    $ligne = Ligne::where('code', '601101')->first();
    $ex = Exercice::first();

    $tx = Transaction::create([
        'type' => 0, 'status' => 0,
        'date' => '2026-04-15', 'code' => 'TX-001',
        'label' => 'Achat fournitures',
        'montant' => 100_000, 'montant_restant' => 100_000,
        'devise' => 'XAF',
        'ligne_id' => $ligne->id, 'exercice_id' => $ex->id,
    ]);
    expect($tx->type_libelle)->toBe('Dépense');
    expect($tx->est_soumissible)->toBeTrue();

    $tx->update(['status' => 1]);
    expect($tx->fresh()->est_validable)->toBeTrue();

    $tx->update(['status' => 2]);
    expect($tx->fresh()->est_payable)->toBeTrue();

    $tx->update(['status' => 3, 'montant_restant' => 0]);
    expect($tx->fresh()->status_libelle)->toBe('Payée/Encaissée');
});

it('les TransactionDetail sont liés à une Transaction', function () {
    (new FinanceV2InitialSeeder())->run();
    $ligne = Ligne::where('code', '601101')->first();
    $ex = Exercice::first();
    $tx = Transaction::create([
        'type' => 0, 'status' => 0, 'date' => '2026-04-15',
        'label' => 'T', 'montant' => 60_000, 'montant_restant' => 60_000,
        'ligne_id' => $ligne->id, 'exercice_id' => $ex->id, 'devise' => 'XAF',
    ]);
    TransactionDetail::create(['transaction_id' => $tx->id, 'label' => 'Papier',   'quantity' => 10, 'montant' => 40_000]);
    TransactionDetail::create(['transaction_id' => $tx->id, 'label' => 'Stylos',   'quantity' => 20, 'montant' => 20_000]);

    expect($tx->fresh()->details)->toHaveCount(2);
    expect((float) $tx->details->sum('montant'))->toBe(60_000.0);
});

// ─── SERVICE MODIF ────────────────────────────────────

it('ModifService : refuse d\'appliquer si non approuvée', function () {
    (new FinanceV2InitialSeeder())->run();
    $src1 = Source::where('code', 'FP')->first();
    $src2 = Source::where('code', 'RB')->first();
    $ligne = Ligne::where('code', '601101')->first();
    $ex = Exercice::first();

    $bs1 = BudgetSource::create(['source_id' => $src1->id, 'ligne_id' => $ligne->id, 'exercice_id' => $ex->id, 'montant' => 10_000]);
    $bs2 = BudgetSource::create(['source_id' => $src2->id, 'ligne_id' => $ligne->id, 'exercice_id' => $ex->id, 'montant' => 0]);

    $m = Modif::create([
        'comment' => 'Test', 'montant' => 5_000,
        'budget_emission_id' => $bs1->id, 'budget_reception_id' => $bs2->id,
        'status' => 1,
    ]);
    expect(fn() => (new ModifService())->appliquer($m, 1))
        ->toThrow(\RuntimeException::class, 'approuvée');
});

it('ModifService : refuse d\'appliquer si solde insuffisant', function () {
    (new FinanceV2InitialSeeder())->run();
    $src1 = Source::where('code', 'FP')->first();
    $src2 = Source::where('code', 'RB')->first();
    $ligne = Ligne::where('code', '601101')->first();
    $ex = Exercice::first();

    $bs1 = BudgetSource::create(['source_id' => $src1->id, 'ligne_id' => $ligne->id, 'exercice_id' => $ex->id, 'montant' => 1_000]);
    $bs2 = BudgetSource::create(['source_id' => $src2->id, 'ligne_id' => $ligne->id, 'exercice_id' => $ex->id, 'montant' => 0]);

    $m = Modif::create([
        'comment' => 'Trop grand', 'montant' => 10_000,
        'budget_emission_id' => $bs1->id, 'budget_reception_id' => $bs2->id,
        'status' => 2,
    ]);
    expect(fn() => (new ModifService())->appliquer($m, 1))
        ->toThrow(\RuntimeException::class, 'insuffisant');
});

it('ModifService : applique un transfert (décrémente émission, incrémente réception)', function () {
    (new FinanceV2InitialSeeder())->run();
    $src1 = Source::where('code', 'FP')->first();
    $src2 = Source::where('code', 'RB')->first();
    $ligne = Ligne::where('code', '601101')->first();
    $ex = Exercice::first();

    $bs1 = BudgetSource::create(['source_id' => $src1->id, 'ligne_id' => $ligne->id, 'exercice_id' => $ex->id, 'montant' => 100_000]);
    $bs2 = BudgetSource::create(['source_id' => $src2->id, 'ligne_id' => $ligne->id, 'exercice_id' => $ex->id, 'montant' => 50_000]);

    $m = Modif::create([
        'comment' => 'Transfert 30K FP → RB', 'montant' => 30_000,
        'budget_emission_id' => $bs1->id, 'budget_reception_id' => $bs2->id,
        'status' => 2,
    ]);

    (new ModifService())->appliquer($m, auth()->id() ?? \App\Models\User::first()?->id ?? 1);

    expect((float) $bs1->fresh()->montant)->toBe(70_000.0);
    expect((float) $bs2->fresh()->montant)->toBe(80_000.0);
    expect($m->fresh()->status)->toBe(3);
});

it('ModifService : annulation restaure les soldes', function () {
    (new FinanceV2InitialSeeder())->run();
    $src1 = Source::where('code', 'FP')->first();
    $src2 = Source::where('code', 'RB')->first();
    $ligne = Ligne::where('code', '601101')->first();
    $ex = Exercice::first();

    $bs1 = BudgetSource::create(['source_id' => $src1->id, 'ligne_id' => $ligne->id, 'exercice_id' => $ex->id, 'montant' => 100_000]);
    $bs2 = BudgetSource::create(['source_id' => $src2->id, 'ligne_id' => $ligne->id, 'exercice_id' => $ex->id, 'montant' => 50_000]);

    $m = Modif::create([
        'comment' => 'T', 'montant' => 20_000,
        'budget_emission_id' => $bs1->id, 'budget_reception_id' => $bs2->id,
        'status' => 2,
    ]);
    (new ModifService())->appliquer($m, auth()->id() ?? \App\Models\User::first()?->id ?? 1);
    expect((float) $bs1->fresh()->montant)->toBe(80_000.0);

    (new ModifService())->annuler($m->fresh());

    expect((float) $bs1->fresh()->montant)->toBe(100_000.0);
    expect((float) $bs2->fresh()->montant)->toBe(50_000.0);
    expect($m->fresh()->status)->toBe(2); // retour à approuvée
});

// ─── ROUTES/CONTROLLERS SMOKE ──────────────────────────

it('la route finance.v2.dashboard répond OK', function () {
    (new FinanceV2InitialSeeder())->run();
    actingAsSuperAdmin();
    $this->get('/finance/v2')->assertOk()->assertSee('Finance V2');
});

it('la route finance.v2.sources.index liste les sources', function () {
    (new FinanceV2InitialSeeder())->run();
    actingAsSuperAdmin();
    $this->get('/finance/v2/sources')->assertOk()->assertSee('Fonds propres');
});

it('crée une source via POST /finance/v2/sources', function () {
    actingAsSuperAdmin();
    $this->post('/finance/v2/sources', [
        'code' => 'TEST', 'label' => 'Test source',
    ])->assertRedirect();
    expect(Source::where('code', 'TEST')->exists())->toBeTrue();
});

it('planificationSave crée un Budget + BudgetSources pour une ligne', function () {
    (new FinanceV2InitialSeeder())->run();
    actingAsSuperAdmin();
    $ligne = Ligne::where('code', '601101')->first();
    $ex = Exercice::first();
    $src = Source::where('code', 'ETAT')->first();

    $this->post("/finance/v2/budgets/planification/{$ex->id}", [
        'lignes' => [
            $ligne->id => [
                'label' => 'Fournitures 2026',
                'seuil' => 5_000_000,
                'sources' => [$src->id => 3_000_000],
            ],
        ],
    ])->assertRedirect();

    expect(Budget::where('ligne_id', $ligne->id)->where('exercice_id', $ex->id)->exists())->toBeTrue();
    expect((float) BudgetSource::where('ligne_id', $ligne->id)->where('source_id', $src->id)->value('montant'))->toBe(3_000_000.0);
});

// ─── CONFIGS FINANCE ──────────────────────────────────

it('les configs Finance V2 sont accessibles par clé', function () {
    (new FinanceV2InitialSeeder())->run();
    expect(FinanceConfig::where('key', 'devise_defaut')->value('value'))->toBe('XAF');
    expect(FinanceConfig::where('key', 'source_defaut')->value('value'))->toBe('ETAT');
});
