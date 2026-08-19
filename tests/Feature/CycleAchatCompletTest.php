<?php

use App\Models\CommandeFournisseur;
use App\Models\DevisFournisseur;
use App\Models\Facture;
use App\Models\Intranet\ContactOrganisation;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    seedRoles();
    $this->user = User::factory()->create();
    $this->frnA = ContactOrganisation::create(['nom' => 'Frn A', 'type' => 'fournisseur', 'actif' => true, 'created_by' => $this->user->id]);
    $this->frnB = ContactOrganisation::create(['nom' => 'Frn B', 'type' => 'fournisseur', 'actif' => true, 'created_by' => $this->user->id]);
    $this->commande = CommandeFournisseur::create([
        'numero_commande' => 'CMD-CY-01', 'fournisseur_id' => $this->frnA->id,
        'date_commande' => now(), 'statut' => CommandeFournisseur::STATUT_APPROUVEE,
        'created_by' => $this->user->id,
    ]);
});

// ═════════ Devis ═════════

it('crée un devis avec montants HT/TTC + PJ obligatoires', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('appro.devis-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'fournisseur_id' => $this->frnA->id,
        'date_reception' => '2027-01-15',
        'montant_ht'  => 13000,
        'montant_ttc' => 14000,
        'pieces_jointes' => [UploadedFile::fake()->create('devis.pdf', 100, 'application/pdf')],
    ]);
    $r->assertRedirect();
    $d = DevisFournisseur::latest()->first();
    expect($d->numero)->toStartWith('DEV-');
    expect((float) $d->montant_ht)->toBe(13000.0);
    expect((float) $d->montant_ttc)->toBe(14000.0);
    expect((float) $d->montant_tva)->toBe(1000.0);
    expect($d->piecesJointes->count())->toBe(1);
});

it('refuse la création si pièce jointe manquante', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.devis-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'fournisseur_id' => $this->frnA->id,
        'date_reception' => '2027-01-15',
        'montant_ht'  => 100,
        'montant_ttc' => 118,
    ])->assertSessionHasErrors('pieces_jointes');
    expect(DevisFournisseur::count())->toBe(0);
});

it('refuse la création si montant TTC < HT', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.devis-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_reception' => '2027-01-15',
        'montant_ht'  => 200,
        'montant_ttc' => 100,
        'pieces_jointes' => [UploadedFile::fake()->create('d.pdf', 50, 'application/pdf')],
    ])->assertSessionHasErrors('montant_ttc');
});

it('refuse la création sans montant HT ou TTC', function () {
    actingAsSuperAdmin();
    $this->post(route('appro.devis-fournisseur.store'), [
        'commande_fournisseur_id' => $this->commande->id,
        'date_reception' => '2027-01-15',
        'pieces_jointes' => [UploadedFile::fake()->create('d.pdf', 50, 'application/pdf')],
    ])->assertSessionHasErrors(['montant_ht', 'montant_ttc']);
});

it('une commande peut recevoir plusieurs devis de fournisseurs différents', function () {
    $d1 = DevisFournisseur::create(['numero' => DevisFournisseur::genererNumero(), 'commande_fournisseur_id' => $this->commande->id, 'fournisseur_id' => $this->frnA->id, 'date_reception' => now(), 'statut' => DevisFournisseur::STATUT_RECU, 'montant_ht' => 100000, 'montant_ttc' => 118000]);
    $d2 = DevisFournisseur::create(['numero' => DevisFournisseur::genererNumero(), 'commande_fournisseur_id' => $this->commande->id, 'fournisseur_id' => $this->frnB->id, 'date_reception' => now(), 'statut' => DevisFournisseur::STATUT_RECU, 'montant_ht' => 95000, 'montant_ttc' => 112100]);
    expect($this->commande->devis()->count())->toBe(2);
});

it('sélection d\'un devis avec motivation → rejette les autres devis reçus', function () {
    actingAsSuperAdmin();
    $d1 = DevisFournisseur::create(['numero' => 'DEV-A', 'commande_fournisseur_id' => $this->commande->id, 'fournisseur_id' => $this->frnA->id, 'date_reception' => now(), 'statut' => DevisFournisseur::STATUT_RECU, 'montant_ttc' => 100]);
    $d2 = DevisFournisseur::create(['numero' => 'DEV-B', 'commande_fournisseur_id' => $this->commande->id, 'fournisseur_id' => $this->frnB->id, 'date_reception' => now(), 'statut' => DevisFournisseur::STATUT_RECU, 'montant_ttc' => 90]);

    $this->post(route('appro.devis-fournisseur.selectionner', $d2), [
        'motivation' => 'Moins cher et délai plus court',
    ])->assertRedirect();

    expect($d2->fresh()->statut)->toBe(DevisFournisseur::STATUT_SELECTIONNE);
    expect($d2->fresh()->motivation)->toContain('Moins cher');
    expect($d1->fresh()->statut)->toBe(DevisFournisseur::STATUT_REJETE);
});

it('sélection avec creer_facture=true crée une facture rattachée', function () {
    actingAsSuperAdmin();
    $d = DevisFournisseur::create(['numero' => 'DEV-F', 'commande_fournisseur_id' => $this->commande->id, 'fournisseur_id' => $this->frnA->id, 'date_reception' => now(), 'statut' => DevisFournisseur::STATUT_RECU, 'montant_ht' => 50000, 'montant_tva' => 9000, 'montant_ttc' => 59000]);
    $this->post(route('appro.devis-fournisseur.selectionner', $d), [
        'motivation' => 'Sélectionné', 'creer_facture' => 1,
    ])->assertRedirect();

    $d->refresh();
    expect($d->facture_id)->not->toBeNull();
    $facture = Facture::find($d->facture_id);
    expect($facture->sens)->toBe('depense');
    expect((float) $facture->montant_ttc)->toBe(59000.0);
    expect($facture->tiers_id)->toBe($this->frnA->id);
    // La commande est aussi liée à la facture
    expect($this->commande->fresh()->facture_id)->toBe($facture->id);
});

it('rejeter un devis avec motivation', function () {
    actingAsSuperAdmin();
    $d = DevisFournisseur::create(['numero' => 'DEV-R', 'commande_fournisseur_id' => $this->commande->id, 'fournisseur_id' => $this->frnA->id, 'date_reception' => now(), 'statut' => DevisFournisseur::STATUT_RECU]);
    $this->post(route('appro.devis-fournisseur.rejeter', $d), [
        'motivation' => 'Hors budget alloué',
    ])->assertRedirect();
    expect($d->fresh()->statut)->toBe(DevisFournisseur::STATUT_REJETE);
    expect($d->fresh()->motivation)->toBe('Hors budget alloué');
});

// ═════════ Ordonnancement facture ═════════

it('ordonnance une facture dépense', function () {
    actingAsSuperAdmin();
    $facture = Facture::create([
        'numero' => 'FA-ORD-01', 'sens' => 'depense', 'tiers_source' => 'organisation',
        'tiers_type' => ContactOrganisation::class, 'tiers_id' => $this->frnA->id,
        'date_emission' => now(), 'objet' => 'Test', 'montant_ht' => 100, 'montant_ttc' => 118,
        'montant_regle' => 0, 'statut' => 1, 'created_by' => $this->user->id,
    ]);
    expect($facture->estOrdonnancee())->toBeFalse();
    expect($facture->peutEtreOrdonnancee())->toBeTrue();

    $this->post(route('finance.factures.ordonnancer', $facture), [
        'motivation' => 'Service fait, conforme',
    ])->assertRedirect()->assertSessionHas('success');

    $facture->refresh();
    expect($facture->estOrdonnancee())->toBeTrue();
    expect($facture->motivation_ordonnancement)->toContain('Service fait');
    expect($facture->ordonnee_par)->not->toBeNull();
});

it('refuse ordonnancement sur facture recette', function () {
    actingAsSuperAdmin();
    $facture = Facture::create([
        'numero' => 'FA-REC-01', 'sens' => 'recette', 'tiers_source' => 'organisation',
        'tiers_type' => ContactOrganisation::class, 'tiers_id' => $this->frnA->id,
        'date_emission' => now(), 'objet' => 'Test', 'montant_ht' => 100, 'montant_ttc' => 118,
        'montant_regle' => 0, 'statut' => 1, 'created_by' => $this->user->id,
    ]);
    expect($facture->peutEtreOrdonnancee())->toBeFalse();
    $this->post(route('finance.factures.ordonnancer', $facture))->assertSessionHas('error');
});

it('refuse double ordonnancement', function () {
    actingAsSuperAdmin();
    $facture = Facture::create([
        'numero' => 'FA-DBL', 'sens' => 'depense', 'tiers_source' => 'organisation',
        'tiers_type' => ContactOrganisation::class, 'tiers_id' => $this->frnA->id,
        'date_emission' => now(), 'objet' => 'Test', 'montant_ht' => 100, 'montant_ttc' => 118,
        'montant_regle' => 0, 'statut' => 1, 'created_by' => $this->user->id,
    ]);
    $this->post(route('finance.factures.ordonnancer', $facture), ['motivation' => 'ok']);
    $this->post(route('finance.factures.ordonnancer', $facture))->assertSessionHas('error');
});

// ═════════ Intégration : fiche commande liste devis ═════════

it('la fiche commande affiche la section devis avec bouton d\'ajout', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('appro.commandes.show', $this->commande));
    $r->assertStatus(200);
    $r->assertSee('Devis reçus');
    $r->assertSee(route('appro.devis-fournisseur.create', ['commande_id' => $this->commande->id]), false);
});
