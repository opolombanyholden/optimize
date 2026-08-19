<?php

use App\Models\Facture;
use App\Models\Intranet\ContactOrganisation;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->user = User::factory()->create();
});

it('liste des fournisseurs sur create facture = même liste que /intranet/organisations?type=fournisseur', function () {
    actingAsSuperAdmin();
    // Fournisseur actif ET fournisseur inactif : les deux doivent apparaître (comme sur /intranet)
    $frnActif   = ContactOrganisation::create(['nom' => 'FrnA-Actif',   'type' => 'fournisseur', 'statut' => 1, 'created_by' => $this->user->id]);
    $frnInactif = ContactOrganisation::create(['nom' => 'FrnB-Inactif', 'type' => 'fournisseur', 'statut' => 0, 'created_by' => $this->user->id]);

    $r = $this->get(route('finance.factures.create', ['sens' => 'depense']));
    $r->assertStatus(200);
    $r->assertSee('FrnA-Actif');
    $r->assertSee('FrnB-Inactif'); // avant : bloqué par ->actif()
});

it('liste des fournisseurs sur edit facture (sans devis) = même liste', function () {
    actingAsSuperAdmin();
    $frnActif   = ContactOrganisation::create(['nom' => 'EditActif', 'type' => 'fournisseur', 'statut' => 1, 'created_by' => $this->user->id]);
    $frnInactif = ContactOrganisation::create(['nom' => 'EditInactif', 'type' => 'fournisseur', 'statut' => 0, 'created_by' => $this->user->id]);
    $facture = Facture::create([
        'numero' => 'FA-ALIGN', 'sens' => 'depense', 'tiers_source' => 'organisation',
        'tiers_type' => 'fournisseur', 'tiers_id' => $frnActif->id,
        'date_emission' => now(), 'objet' => 'Test',
        'montant_ht' => 100, 'montant_ttc' => 118, 'montant_regle' => 0,
        'statut' => 0, 'created_by' => $this->user->id,
    ]);

    $r = $this->get(route('finance.factures.edit', $facture));
    $r->assertStatus(200);
    $r->assertSee('EditActif');
    $r->assertSee('EditInactif');
});
