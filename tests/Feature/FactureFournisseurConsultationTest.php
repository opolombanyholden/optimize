<?php

use App\Models\CommandeFournisseur;
use App\Models\DevisFournisseur;
use App\Models\Facture;
use App\Models\Intranet\ContactOrganisation;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->user = User::factory()->create();
    // 4 fournisseurs, seuls A et B soumettent des devis pour la commande
    $this->frnA = ContactOrganisation::create(['nom' => 'Frn A', 'type' => 'fournisseur', 'actif' => true, 'created_by' => $this->user->id]);
    $this->frnB = ContactOrganisation::create(['nom' => 'Frn B', 'type' => 'fournisseur', 'actif' => true, 'created_by' => $this->user->id]);
    $this->frnC = ContactOrganisation::create(['nom' => 'Frn C', 'type' => 'fournisseur', 'actif' => true, 'created_by' => $this->user->id]);
    $this->frnD = ContactOrganisation::create(['nom' => 'Frn D', 'type' => 'fournisseur', 'actif' => true, 'created_by' => $this->user->id]);

    $this->commande = CommandeFournisseur::create([
        'numero_commande' => 'CMD-CONS', 'objet' => 'Consultation restreinte',
        'date_commande' => now(), 'statut' => CommandeFournisseur::STATUT_APPROUVEE,
        'created_by' => $this->user->id,
    ]);

    // Devis A (sélectionné, crée facture) + Devis B (rejeté)
    $this->devisA = DevisFournisseur::create([
        'numero' => 'DEV-A', 'commande_fournisseur_id' => $this->commande->id,
        'fournisseur_id' => $this->frnA->id, 'date_reception' => now(),
        'statut' => DevisFournisseur::STATUT_RECU, 'montant_ht' => 100, 'montant_ttc' => 118,
    ]);
    $this->devisB = DevisFournisseur::create([
        'numero' => 'DEV-B', 'commande_fournisseur_id' => $this->commande->id,
        'fournisseur_id' => $this->frnB->id, 'date_reception' => now(),
        'statut' => DevisFournisseur::STATUT_RECU, 'montant_ht' => 90, 'montant_ttc' => 106.20,
    ]);
});

it('edit d\'une facture issue d\'un devis limite la liste aux fournisseurs ayant soumis un devis', function () {
    actingAsSuperAdmin();
    // Sélection du devis A → crée automatiquement la facture liée
    $this->post(route('appro.devis-fournisseur.selectionner', $this->devisA), [
        'motivation' => 'Meilleur', 'creer_facture' => 1,
    ])->assertRedirect();

    $facture = Facture::find($this->devisA->fresh()->facture_id);
    expect($facture)->not->toBeNull();

    $r = $this->get(route('finance.factures.edit', $facture));
    $r->assertStatus(200);
    // Doit voir Frn A et Frn B (ont soumis un devis pour la commande)
    $r->assertSee('Frn A');
    $r->assertSee('Frn B');
    // Ne doit pas voir Frn C et Frn D (aucun devis)
    $r->assertDontSee('Frn C');
    $r->assertDontSee('Frn D');
    // Message contextuel : lien vers la commande d'origine
    $r->assertSee('CMD-CONS');
    $r->assertSee('La liste des fournisseurs est restreinte');
});

it('edit d\'une facture SANS lien devis affiche tous les fournisseurs', function () {
    actingAsSuperAdmin();
    $facture = Facture::create([
        'numero' => 'FA-INDEP', 'sens' => 'depense', 'tiers_source' => 'organisation',
        'tiers_type' => 'fournisseur', 'tiers_id' => $this->frnA->id,
        'date_emission' => now(), 'objet' => 'Facture directe',
        'montant_ht' => 100, 'montant_ttc' => 118, 'montant_regle' => 0,
        'statut' => 0, 'created_by' => $this->user->id,
    ]);
    $r = $this->get(route('finance.factures.edit', $facture));
    $r->assertStatus(200);
    $r->assertSee('Frn A');
    $r->assertSee('Frn B');
    $r->assertSee('Frn C');
    $r->assertSee('Frn D');
});
