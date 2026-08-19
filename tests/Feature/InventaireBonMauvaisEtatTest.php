<?php

use App\Models\Emplacement;
use App\Models\Inventaire;
use App\Models\Produit;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->emp = Emplacement::create(['code' => 'ETA-01', 'libelle' => 'Magasin', 'actif' => true]);
    $this->prod = Produit::create([
        'code' => 'ETA-P', 'designation' => 'Article étatable', 'type_article' => 'bien',
        'est_stockable' => true, 'unite_mesure' => 'u', 'prix_unitaire' => 100, 'statut' => 1,
    ]);
    $this->prod->ajusterStockEmplacement($this->emp->id, 10);
});

function makeInventaireEnCours(int $empId): Inventaire
{
    actingAsSuperAdmin();
    $inv = Inventaire::create([
        'numero' => Inventaire::genererNumero(),
        'libelle' => 'Test bon/mauvais', 'date_prevue' => now()->toDateString(),
        'emplacement_id' => $empId, 'responsable_id' => auth()->id(),
        'statut' => Inventaire::STATUT_BROUILLON,
    ]);
    $inv->genererLignes();
    $inv->lancer();
    return $inv;
}

it('la saisie enregistre bon état et mauvais état séparément', function () {
    $inv = makeInventaireEnCours($this->emp->id);
    $ligne = $inv->lignes->first();
    $this->post(route('appro.inventaires.saisie.save', $inv), [
        'lignes' => [
            ['id' => $ligne->id, 'quantite_bon_etat' => 7, 'quantite_mauvais_etat' => 2, 'commentaire' => '2 unités abîmées'],
        ],
    ])->assertRedirect()->assertSessionHas('success');

    $ligne->refresh();
    expect((float) $ligne->quantite_bon_etat)->toBe(7.0);
    expect((float) $ligne->quantite_mauvais_etat)->toBe(2.0);
    // quantite_reelle = bon état (utilisée pour l'écart et la clôture)
    expect((float) $ligne->quantite_reelle)->toBe(7.0);
    expect((float) $ligne->ecart)->toBe(-3.0); // 7 - 10 théorique
    expect($ligne->compte_par)->not->toBeNull();
});

it('clôture inventaire : seul le bon état ajuste le stock livrable', function () {
    $inv = makeInventaireEnCours($this->emp->id);
    $ligne = $inv->lignes->first();
    // 6 bon + 3 mauvais → seuls les 6 doivent être disponibles pour livraison
    $this->post(route('appro.inventaires.saisie.save', $inv), [
        'lignes' => [
            ['id' => $ligne->id, 'quantite_bon_etat' => 6, 'quantite_mauvais_etat' => 3],
        ],
    ]);
    $this->post(route('appro.inventaires.cloturer', $inv))->assertRedirect();

    // Stock produit dans l'emplacement = 6 (bon état seul, mauvais état exclu du livrable)
    expect((float) $this->prod->fresh()->stock_actuel)->toBe(6.0);
    expect((float) $this->prod->stockDans($this->emp->id))->toBe(6.0);
});

it('une ligne sans quantite_bon_etat mais avec mauvais → traitée comme comptée (bon=0)', function () {
    $inv = makeInventaireEnCours($this->emp->id);
    $ligne = $inv->lignes->first();
    // Tout est en mauvais état → bon = null (interprété 0)
    $this->post(route('appro.inventaires.saisie.save', $inv), [
        'lignes' => [
            ['id' => $ligne->id, 'quantite_mauvais_etat' => 5, 'commentaire' => 'Tout HS'],
        ],
    ])->assertRedirect();

    $ligne->refresh();
    expect((float) $ligne->quantite_reelle)->toBe(0.0); // bon = null → 0
    expect((float) $ligne->quantite_mauvais_etat)->toBe(5.0);
    expect($ligne->compte_par)->not->toBeNull();
});

it('la page saisie affiche 2 colonnes distinctes', function () {
    $inv = makeInventaireEnCours($this->emp->id);
    $r = $this->get(route('appro.inventaires.saisie', $inv));
    $r->assertStatus(200);
    $r->assertSee('Bon état');
    $r->assertSee('Mauvais état');
    $r->assertSee('quantite_bon_etat', false);
    $r->assertSee('quantite_mauvais_etat', false);
});
