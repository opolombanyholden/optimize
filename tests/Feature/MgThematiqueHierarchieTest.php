<?php

use App\Models\Dysfonctionnement;
use App\Models\Intervention;
use App\Models\MgThematique;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->user = User::factory()->create();
});

it('crée une thématique racine puis une sous-thématique', function () {
    actingAsSuperAdmin();
    $this->post(route('referentiel.mg-thematiques.store'), [
        'libelle' => 'Informatique', 'couleur' => '#0d6efd',
    ])->assertRedirect();
    $racine = MgThematique::where('libelle', 'Informatique')->first();

    $this->post(route('referentiel.mg-thematiques.store'), [
        'libelle' => 'Réseau', 'parent_id' => $racine->id,
    ])->assertRedirect();
    $enfant = MgThematique::where('libelle', 'Réseau')->first();

    expect($enfant->parent_id)->toBe($racine->id);
    expect($racine->fresh()->enfants->pluck('id')->all())->toContain($enfant->id);
});

it('calcule le chemin hiérarchique complet', function () {
    $a = MgThematique::create(['libelle' => 'A', 'actif' => true]);
    $b = MgThematique::create(['libelle' => 'B', 'parent_id' => $a->id, 'actif' => true]);
    $c = MgThematique::create(['libelle' => 'C', 'parent_id' => $b->id, 'actif' => true]);
    expect($c->chemin)->toBe('A › B › C');
});

it('empêche une thématique d\'être son propre parent', function () {
    actingAsSuperAdmin();
    $t = MgThematique::create(['libelle' => 'X', 'actif' => true]);
    $this->put(route('referentiel.mg-thematiques.update', $t), ['libelle' => 'X', 'parent_id' => $t->id])
        ->assertSessionHas('error');
});

it('empêche un cycle descendant', function () {
    actingAsSuperAdmin();
    $parent = MgThematique::create(['libelle' => 'P', 'actif' => true]);
    $enfant = MgThematique::create(['libelle' => 'E', 'parent_id' => $parent->id, 'actif' => true]);
    $this->put(route('referentiel.mg-thematiques.update', $parent), ['libelle' => 'P', 'parent_id' => $enfant->id])
        ->assertSessionHas('error');
});

it('refuse la suppression si sous-thématiques existent', function () {
    actingAsSuperAdmin();
    $parent = MgThematique::create(['libelle' => 'P', 'actif' => true]);
    MgThematique::create(['libelle' => 'E', 'parent_id' => $parent->id, 'actif' => true]);
    $this->delete(route('referentiel.mg-thematiques.destroy', $parent))->assertSessionHas('error');
    expect(MgThematique::find($parent->id))->not->toBeNull();
});

it('refuse la suppression si thématique utilisée par un dysfonctionnement', function () {
    actingAsSuperAdmin();
    $t = MgThematique::create(['libelle' => 'Utilisée', 'actif' => true]);
    Dysfonctionnement::create([
        'label' => 'Panne', 'thematique_id' => $t->id,
        'statut' => Dysfonctionnement::STATUT_SIGNALE, 'date_signalement' => now(),
    ]);
    $this->delete(route('referentiel.mg-thematiques.destroy', $t))->assertSessionHas('error');
});

it('refuse la suppression si thématique utilisée par une sous-thématique via intervention', function () {
    actingAsSuperAdmin();
    $parent = MgThematique::create(['libelle' => 'P', 'actif' => true]);
    $enfant = MgThematique::create(['libelle' => 'E', 'parent_id' => $parent->id, 'actif' => true]);
    Intervention::create(['label' => 'I', 'thematique_id' => $enfant->id, 'statut' => Intervention::STATUT_PLANIFIEE]);

    // Enfant a l'intervention → suppression enfant refusée
    $this->delete(route('referentiel.mg-thematiques.destroy', $enfant))->assertSessionHas('error');
    // Parent a un enfant → suppression parent refusée (sous-thématique bloque déjà)
    $this->delete(route('referentiel.mg-thematiques.destroy', $parent))->assertSessionHas('error');
});

it('toggle actif/inactif', function () {
    actingAsSuperAdmin();
    $t = MgThematique::create(['libelle' => 'T', 'actif' => true]);
    $this->post(route('referentiel.mg-thematiques.toggle', $t))->assertRedirect();
    expect($t->fresh()->actif)->toBeFalse();
    $this->post(route('referentiel.mg-thematiques.toggle', $t))->assertRedirect();
    expect($t->fresh()->actif)->toBeTrue();
});

it('affiche l\'arbre depuis la page index', function () {
    actingAsSuperAdmin();
    $parent = MgThematique::create(['libelle' => 'ParentAffiche', 'actif' => true]);
    MgThematique::create(['libelle' => 'EnfantAffiche', 'parent_id' => $parent->id, 'actif' => true]);
    $r = $this->get(route('referentiel.mg-thematiques.index'));
    $r->assertStatus(200);
    $r->assertSee('ParentAffiche');
    $r->assertSee('EnfantAffiche');
});
