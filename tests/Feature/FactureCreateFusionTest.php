<?php

use App\Models\Intranet\ContactOrganisation;

beforeEach(function () {
    seedRoles();
    $this->auteur = \App\Models\User::factory()->create();
    // Un fournisseur + un client via la fusion ContactOrganisation
    $this->frn = ContactOrganisation::create([
        'nom' => 'Alpha Fournisseur', 'raison_sociale' => 'ALPHA SA', 'type' => 'fournisseur',
        'actif' => true, 'statut' => 1, 'created_by' => $this->auteur->id, 'code' => 'FRN-001',
    ]);
    $this->cli = ContactOrganisation::create([
        'nom' => 'Beta Client', 'raison_sociale' => 'BETA SARL', 'type' => 'client',
        'actif' => true, 'statut' => 1, 'created_by' => $this->auteur->id, 'code' => 'CLI-001',
    ]);
});

it('/finance/factures/create?sens=depense se rend sans erreur et liste les fournisseurs', function () {
    actingAsSuperAdmin();
    $r = $this->get('/finance/factures/create?sens=depense');
    $r->assertOk();
    $r->assertSee('ALPHA SA'); // raison sociale du fournisseur listé
});

it('/finance/factures/create?sens=recette liste les clients', function () {
    actingAsSuperAdmin();
    $r = $this->get('/finance/factures/create?sens=recette');
    $r->assertOk();
    $r->assertSee('BETA SARL');
});

it('store accepte un fournisseur ContactOrganisation valide', function () {
    actingAsSuperAdmin();
    $r = $this->post('/finance/factures', [
        'sens'          => 'depense',
        'tiers_type'    => 'fournisseur',
        'tiers_id'      => $this->frn->id,
        'date_emission' => '2027-01-01',
        'objet'         => 'Facture test',
        'montant_ht'    => 100_000,
    ]);
    $r->assertRedirect();
    expect(\App\Models\Facture::where('objet', 'Facture test')->exists())->toBeTrue();
});

it('store refuse un tiers de mauvais type (fournisseur envoyé comme client)', function () {
    actingAsSuperAdmin();
    $r = $this->post('/finance/factures', [
        'sens'          => 'recette',
        'tiers_type'    => 'client',
        'tiers_id'      => $this->frn->id, // fournisseur envoyé pour un tiers_type=client
        'date_emission' => '2027-01-01',
        'objet'         => 'Facture test',
        'montant_ht'    => 100_000,
    ]);
    $r->assertSessionHasErrors('tiers_id');
});
