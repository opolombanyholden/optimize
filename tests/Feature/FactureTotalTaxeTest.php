<?php

use App\Models\Facture;
use App\Models\Intranet\ContactOrganisation;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->auteur = User::factory()->create();
    $this->frn = ContactOrganisation::create([
        'nom' => 'Frn X', 'raison_sociale' => 'FRN X SA', 'type' => 'fournisseur',
        'actif' => true, 'created_by' => $this->auteur->id,
    ]);
});

function payloadFactureTaxe(array $extra = []): array {
    return array_merge([
        'sens' => 'depense', 'tiers_type' => 'fournisseur',
        'tiers_source' => 'organisation', 'tiers_id' => test()->frn->id,
        'date_emission' => '2027-01-15', 'objet' => 'Achat', 'montant_ht' => 100_000,
    ], $extra);
}

it('total taxe direct : TTC = HT + Taxe (pas de % à saisir)', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.factures.store'), payloadFactureTaxe([
        'montant_tva' => 18_000, // saisie directe
    ]))->assertRedirect();

    $f = Facture::latest()->first();
    expect((float) $f->montant_ht)->toBe(100_000.0);
    expect((float) $f->montant_tva)->toBe(18_000.0);
    expect((float) $f->montant_ttc)->toBe(118_000.0);
    // taux rétro-calculé à titre indicatif
    expect((float) $f->taux_tva)->toBe(18.0);
});

it('total taxe = 0 : TTC = HT', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.factures.store'), payloadFactureTaxe([
        'montant_tva' => 0,
    ]))->assertRedirect();

    $f = Facture::latest()->first();
    expect((float) $f->montant_tva)->toBe(0.0);
    expect((float) $f->montant_ttc)->toBe(100_000.0);
    expect((float) $f->taux_tva)->toBe(0.0);
});

it('total taxe non entier : TTC calculé correctement', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.factures.store'), payloadFactureTaxe([
        'montant_ht'  => 33_333.34,
        'montant_tva' => 5_666.67,
    ]))->assertRedirect();

    $f = Facture::latest()->first();
    expect((float) $f->montant_ttc)->toBe(39_000.01);
});

it('rétrocompatibilité : si seul taux_tva est envoyé (ancienne UX), le montant est calculé', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.factures.store'), payloadFactureTaxe([
        'taux_tva' => 10, // pas de montant_tva → fallback
    ]))->assertRedirect();

    $f = Facture::latest()->first();
    expect((float) $f->montant_tva)->toBe(10_000.0); // 100000 × 10%
    expect((float) $f->montant_ttc)->toBe(110_000.0);
});

it('vue create : le label est "Total Taxe" (pas "Taux TVA")', function () {
    actingAsSuperAdmin();
    $r = $this->get('/finance/factures/create?sens=depense');
    $r->assertOk();
    $r->assertSee('Total Taxe');
    $r->assertDontSee('Taux TVA');
});
