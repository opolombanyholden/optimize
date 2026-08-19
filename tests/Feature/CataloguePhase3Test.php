<?php

use App\Models\FamilleArticle;
use App\Models\Produit;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->user = User::factory()->create();
});

// ═════════ Familles hiérarchiques ═════════

it('crée une famille racine puis une famille enfant', function () {
    $racine = FamilleArticle::create(['libelle' => 'Fournitures', 'actif' => true]);
    $enfant = FamilleArticle::create(['libelle' => 'Papeterie', 'parent_id' => $racine->id, 'actif' => true]);
    expect($enfant->parent->id)->toBe($racine->id);
    expect($racine->enfants->pluck('id')->all())->toContain($enfant->id);
});

it('calcule le chemin complet d\'une famille profonde', function () {
    $a = FamilleArticle::create(['libelle' => 'A']);
    $b = FamilleArticle::create(['libelle' => 'B', 'parent_id' => $a->id]);
    $c = FamilleArticle::create(['libelle' => 'C', 'parent_id' => $b->id]);
    expect($c->chemin)->toBe('A › B › C');
});

it('empêche une famille d\'être son propre parent (cycle)', function () {
    actingAsSuperAdmin();
    $f = FamilleArticle::create(['libelle' => 'X']);
    $this->put(route('referentiel.familles.update', $f), ['libelle' => 'X', 'parent_id' => $f->id])
        ->assertSessionHas('error');
});

it('empêche de créer un cycle en descendant', function () {
    actingAsSuperAdmin();
    $racine = FamilleArticle::create(['libelle' => 'R']);
    $enfant = FamilleArticle::create(['libelle' => 'E', 'parent_id' => $racine->id]);
    // Tenter de faire racine enfant de son propre enfant
    $this->put(route('referentiel.familles.update', $racine), ['libelle' => 'R', 'parent_id' => $enfant->id])
        ->assertSessionHas('error');
});

it('refuse la suppression d\'une famille avec produits actifs', function () {
    actingAsSuperAdmin();
    $f = FamilleArticle::create(['libelle' => 'F']);
    Produit::create(['designation' => 'Test', 'famille_id' => $f->id, 'type_article' => 'bien', 'est_stockable' => true, 'statut' => 1]);
    $this->delete(route('referentiel.familles.destroy', $f))->assertSessionHas('error');
    expect(FamilleArticle::find($f->id))->not->toBeNull();
});

it('autorise la suppression si tous les produits sont inactifs', function () {
    actingAsSuperAdmin();
    $f = FamilleArticle::create(['libelle' => 'FVide']);
    // Produit inactif (statut = 0) — ne bloque pas
    Produit::create(['designation' => 'X', 'famille_id' => $f->id, 'type_article' => 'bien', 'est_stockable' => true, 'statut' => 0]);
    $this->delete(route('referentiel.familles.destroy', $f))->assertSessionHas('success');
    expect(FamilleArticle::find($f->id))->toBeNull();
});

it('refuse la suppression si sous-famille contient produits actifs (récursif)', function () {
    actingAsSuperAdmin();
    $parent = FamilleArticle::create(['libelle' => 'Parent']);
    $enfant = FamilleArticle::create(['libelle' => 'Enfant', 'parent_id' => $parent->id]);
    Produit::create(['designation' => 'A', 'famille_id' => $enfant->id, 'type_article' => 'bien', 'est_stockable' => true, 'statut' => 1]);
    // Suppression du parent bloquée à cause du produit actif dans l'enfant
    $this->delete(route('referentiel.familles.destroy', $parent))->assertSessionHas('error');
    expect(FamilleArticle::find($parent->id))->not->toBeNull();
});

it('refuse la suppression si sous-famille existante même sans produit', function () {
    actingAsSuperAdmin();
    $parent = FamilleArticle::create(['libelle' => 'P']);
    FamilleArticle::create(['libelle' => 'E', 'parent_id' => $parent->id]);
    $this->delete(route('referentiel.familles.destroy', $parent))->assertSessionHas('error');
});

it('toggle actif/inactif via POST', function () {
    actingAsSuperAdmin();
    $f = FamilleArticle::create(['libelle' => 'Toggle', 'actif' => true]);
    $this->post(route('referentiel.familles.toggle', $f))->assertRedirect();
    expect($f->fresh()->actif)->toBeFalse();
    $this->post(route('referentiel.familles.toggle', $f))->assertRedirect();
    expect($f->fresh()->actif)->toBeTrue();
});

it('update permet de modifier le parent + protège contre cycle descendant', function () {
    actingAsSuperAdmin();
    $a = FamilleArticle::create(['libelle' => 'A']);
    $b = FamilleArticle::create(['libelle' => 'B', 'parent_id' => $a->id]);
    // Déplacer B sous rien
    $this->put(route('referentiel.familles.update', $b), ['libelle' => 'B', 'parent_id' => null])->assertRedirect();
    expect($b->fresh()->parent_id)->toBeNull();
    // Créer cycle : A sous B (déjà enfant → hors sous-arbre car B est racine maintenant)
    $c = FamilleArticle::create(['libelle' => 'C', 'parent_id' => $a->id]);
    $this->put(route('referentiel.familles.update', $a), ['libelle' => 'A', 'parent_id' => $c->id])->assertSessionHas('error');
});

// ═════════ Catalogue + alertes stock ═════════

it('crée un bien stockable via le catalogue (stock initial via ajustement)', function () {
    actingAsSuperAdmin();
    $emp = \App\Models\Emplacement::create(['code' => 'INIT', 'libelle' => 'Init', 'actif' => true]);
    $r = $this->post(route('referentiel.catalogue.store'), [
        'designation' => 'Ramette A4',
        'type_article' => 'bien',
        'est_stockable' => true,
        'unite_mesure' => 'ramette',
        'prix_unitaire' => 3500,
        'seuil_alerte' => 5,
        'statut' => 1,
    ]);
    $r->assertRedirect();
    $p = Produit::where('designation', 'Ramette A4')->first();
    expect($p->type_article)->toBe('bien');
    expect($p->est_stockable)->toBeTrue();
    // stock_actuel calculé : 0 à la création → rupture tant qu'on n'a pas ajusté
    expect($p->etat_stock)->toBe('rupture');
    // Ajustement initial → passe en 'ok'
    $p->ajusterStockEmplacement($emp->id, 20);
    expect($p->fresh()->etat_stock)->toBe('ok');
});

it('détecte état alerte quand stock ≤ seuil', function () {
    $p = Produit::create([
        'designation' => 'Encre', 'type_article' => 'bien', 'est_stockable' => true,
        'stock_actuel' => 3, 'seuil_alerte' => 5, 'statut' => 1,
    ]);
    expect($p->etat_stock)->toBe('alerte');
    expect(Produit::alertStock()->pluck('id')->all())->toContain($p->id);
});

it('détecte état rupture quand stock ≤ 0', function () {
    $p = Produit::create([
        'designation' => 'Cartouche', 'type_article' => 'bien', 'est_stockable' => true,
        'stock_actuel' => 0, 'seuil_alerte' => 2, 'statut' => 1,
    ]);
    expect($p->etat_stock)->toBe('rupture');
    expect(Produit::rupture()->pluck('id')->all())->toContain($p->id);
});

it('les services ne sont pas soumis à alerte stock', function () {
    $p = Produit::create([
        'designation' => 'Conseil juridique', 'type_article' => 'service', 'est_stockable' => false,
        'stock_actuel' => 0, 'statut' => 1,
    ]);
    expect($p->etat_stock)->toBe('non_applicable');
    expect(Produit::alertStock()->pluck('id')->all())->not->toContain($p->id);
});
