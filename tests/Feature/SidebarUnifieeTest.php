<?php

use App\Models\User;

beforeEach(function () {
    seedRoles();
});

it('rend correctement la sidebar depuis /appro/commandes (module logistique unifié)', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('appro.commandes.index'));
    $r->assertStatus(200);
    // Le brand affiche le nom unifié
    $r->assertSee('Achats', false);
    $r->assertSee('Moyens Généraux', false);
    // Les 6 accordéons unifiés doivent tous apparaître
    $r->assertSee('Achats fournisseurs');
    $r->assertSee('Demandes internes');
    $r->assertSee('Évaluation prestataires');
    $r->assertSee('Immobilisations');
    $r->assertSee('Maintenance');
    $r->assertSee('Référentiels');
});

it('rend la même sidebar unifiée depuis /mg/immobilisations', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('mg.immobilisations.index'));
    $r->assertStatus(200);
    $r->assertSee('Achats', false);
    $r->assertSee('Moyens Généraux', false);
    $r->assertSee('Achats fournisseurs');
    $r->assertSee('Maintenance');
});

it('rend la sidebar unifiée depuis /referentiel/catalogue', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('referentiel.catalogue.index'));
    $r->assertStatus(200);
    $r->assertSee('Achats', false);
    $r->assertSee('Moyens Généraux', false);
});

it('portail /dashboard affiche une seule carte fusionnée', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('dashboard'));
    $r->assertStatus(200);
    $r->assertSee('Achats', false);
    $r->assertSee('Moyens Généraux', false);
    // Vérifier qu'on n'a plus les 2 anciennes cartes séparées
    $body = $r->getContent();
    $countMod = substr_count($body, 'erp-mod-name">Achats');
    expect($countMod)->toBe(1);
});
