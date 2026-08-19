<?php

use App\Models\Facture;
use App\Models\Intranet\ContactOrganisation;

beforeEach(function () {
    seedRoles();
    $this->auteur = \App\Models\User::factory()->create();
});

function mkOrga(array $data): ContactOrganisation
{
    return ContactOrganisation::create(array_merge([
        'created_by' => test()->auteur->id,
        'nom'        => 'Sans nom',
    ], $data));
}

it('provides typed constants for ContactOrganisation', function () {
    expect(ContactOrganisation::TYPES)->toHaveKeys(['client', 'fournisseur', 'investisseur', 'administration', 'partenaire', 'autre']);
    expect(ContactOrganisation::TYPE_COULEURS['client'])->toBe('success');
    expect(ContactOrganisation::TYPE_ICONES['fournisseur'])->toBe('fa-truck');
});

it('creates a client-typed organisation with B2B fields', function () {
    $o = mkOrga([
        'type'            => 'client',
        'code'            => 'CLI-TEST-01',
        'raison_sociale'  => 'Test SARL',
        'forme_juridique' => 'SARL',
        'nif'             => '12345',
        'nom'             => 'Test SARL',
        'statut'          => 1,
    ]);
    expect($o->type_libelle)->toBe('Client');
    expect($o->nom_affichage)->toBe('Test SARL');
});

it('scopes ContactOrganisation by type', function () {
    mkOrga(['type' => 'client',      'nom' => 'Cli A']);
    mkOrga(['type' => 'fournisseur', 'nom' => 'Frn A']);
    mkOrga(['type' => 'partenaire',  'nom' => 'Part A']);

    expect(ContactOrganisation::client()->count())->toBe(1);
    expect(ContactOrganisation::fournisseur()->count())->toBe(1);
    expect(ContactOrganisation::type('partenaire')->count())->toBe(1);
});

it('search finds organisations by code, raison_sociale and nif', function () {
    mkOrga(['type'=>'client','nom'=>'X','code'=>'CLI-AAA-01','raison_sociale'=>'AlphaCorp','nif'=>'999888']);
    expect(ContactOrganisation::recherche('AlphaCorp')->count())->toBe(1);
    expect(ContactOrganisation::recherche('CLI-AAA')->count())->toBe(1);
    expect(ContactOrganisation::recherche('999888')->count())->toBe(1);
});

it('organisations index shows type tabs', function () {
    actingAsSuperAdmin();
    mkOrga(['type' => 'client',      'nom' => 'CliA']);
    mkOrga(['type' => 'fournisseur', 'nom' => 'FrnA']);

    $r = $this->get(route('intranet.organisations.index'));
    $r->assertOk();
    $r->assertSee('Client');
    $r->assertSee('Fournisseur');
});

it('organisations index filters by type', function () {
    actingAsSuperAdmin();
    mkOrga(['type' => 'client',      'nom' => 'CliUniqueRSTest']);
    mkOrga(['type' => 'fournisseur', 'nom' => 'FrnUniqueRSTest']);

    $r = $this->get(route('intranet.organisations.index', ['type' => 'client']));
    $r->assertOk();
    $r->assertSee('CliUniqueRSTest');
    $r->assertDontSee('FrnUniqueRSTest');
});

it('create form prefills the requested type', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('intranet.organisations.create', ['type' => 'fournisseur']));
    $r->assertOk();
    $r->assertSee('Informations légales');
});

it('Contact reste un individu — pas de champs B2B', function () {
    // Le contact n'a pas de type/raison_sociale/nif etc. — c'est l'organisation qui les porte.
    $contact = new \App\Models\Intranet\Contact(['nom' => 'Doe', 'prenoms' => 'John']);
    expect(in_array('type', $contact->getFillable()))->toBeFalse();
    expect(in_array('raison_sociale', $contact->getFillable()))->toBeFalse();
    expect(in_array('nif', $contact->getFillable()))->toBeFalse();
});

it('redirects legacy /finance/clients to intranet organisations', function () {
    actingAsSuperAdmin();
    $r = $this->get('/finance/clients');
    $r->assertRedirect(route('intranet.organisations.index', ['type' => 'client']));
});

it('redirects legacy /appro/fournisseurs to intranet organisations', function () {
    actingAsSuperAdmin();
    $r = $this->get('/appro/fournisseurs');
    $r->assertRedirect(route('intranet.organisations.index', ['type' => 'fournisseur']));
});

it('exposes contextual statut_relation options per type', function () {
    expect(ContactOrganisation::STATUTS_RELATION['client'])->toHaveKey('prospect');
    expect(ContactOrganisation::STATUTS_RELATION['client'])->toHaveKey('actif');
    expect(ContactOrganisation::STATUTS_RELATION['fournisseur'])->toHaveKey('evaluation');
    expect(ContactOrganisation::STATUTS_RELATION['fournisseur'])->toHaveKey('reference');
    expect(ContactOrganisation::STATUTS_RELATION['investisseur'])->toHaveKey('en_discussion');
    // Chaque type expose son propre cycle
    expect(ContactOrganisation::STATUTS_RELATION['client'])->not->toHaveKey('evaluation');
    expect(ContactOrganisation::STATUTS_RELATION['fournisseur'])->not->toHaveKey('prospect');
});

it('resolves statut_relation libelle from stored value + type', function () {
    $o = mkOrga(['type' => 'client', 'nom' => 'X', 'statut_relation' => 'en_negociation']);
    expect($o->statut_relation_libelle)->toBe('En négociation');
    expect($o->statut_relation_couleur)->toBe('warning');
});

it('Facture::tiersResolu returns a ContactOrganisation filtered by type', function () {
    $cli = mkOrga(['type' => 'client',      'code' => 'CLI-FAC', 'nom' => 'Alpha SA', 'raison_sociale' => 'Alpha SA']);
    $frn = mkOrga(['type' => 'fournisseur', 'code' => 'FRN-FAC', 'nom' => 'Beta SARL', 'raison_sociale' => 'Beta SARL']);

    // Un tiers_id "client" ne doit pas résoudre une organisation fournisseur
    $f = new Facture(['tiers_type' => 'client', 'tiers_id' => $frn->id]);
    expect($f->tiersResolu())->toBeNull();

    // Le mapping correct fonctionne
    $ok = new Facture(['tiers_type' => 'client', 'tiers_id' => $cli->id]);
    expect($ok->tiersResolu()?->id)->toBe($cli->id);

    $okF = new Facture(['tiers_type' => 'fournisseur', 'tiers_id' => $frn->id]);
    expect($okF->tiersResolu()?->id)->toBe($frn->id);
});
