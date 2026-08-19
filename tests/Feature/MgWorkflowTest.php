<?php

use App\Models\Dysfonctionnement;
use App\Models\Immobilisation;
use App\Models\ImmobilisationDotation;
use App\Models\Intervention;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    seedRoles();
    $this->auteur = User::factory()->create();
    $this->immo = Immobilisation::create([
        'code' => 'IMM-TEST-01',
        'designation' => 'Ordinateur portable Dell',
        'categorie' => 'materiel_informatique',
        'valeur_acquisition' => 1_200_000,
        'valeur_nette_comptable' => 1_200_000,
        'valeur_residuelle' => 0,
        'duree_amortissement' => 36,
        'methode_amortissement' => Immobilisation::METHODE_LINEAIRE,
        'etat' => 'neuf',
        'statut' => 1,
    ]);
});

// ═════════ CRUD Immobilisation ═════════

it('crée une immobilisation via HTTP', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('mg.immobilisations.store'), [
        'code' => 'IMM-002', 'designation' => 'Imprimante HP',
        'categorie' => 'materiel_informatique',
        'valeur_acquisition' => 800_000, 'duree_amortissement' => 24,
        'methode_amortissement' => 'lineaire', 'etat' => 'neuf',
    ]);
    $r->assertRedirect();
    expect(Immobilisation::where('code', 'IMM-002')->exists())->toBeTrue();
    // VNC initiale = valeur d'acquisition si non fournie
    expect((float) Immobilisation::where('code', 'IMM-002')->first()->valeur_nette_comptable)->toBe(800_000.0);
});

// ═════════ Amortissements ═════════

it('calcule la dotation mensuelle théorique linéaire', function () {
    // 1 200 000 / 36 mois = 33 333,33 (arrondi)
    expect($this->immo->dotation_mensuelle)->toBe(33_333.33);
});

it('enregistre une dotation mensuelle + met à jour VNC + cumul', function () {
    actingAsSuperAdmin();
    $this->post(route('mg.immobilisations.dotation', $this->immo), ['periode' => '2027-01'])->assertRedirect();

    $this->immo->refresh();
    expect((float) $this->immo->amortissement_cumule)->toBe(33_333.33);
    expect((float) $this->immo->valeur_nette_comptable)->toBe(1_166_666.67);
    expect(ImmobilisationDotation::where('immobilisation_id', $this->immo->id)->count())->toBe(1);
});

it('refuse une double dotation sur la même période', function () {
    actingAsSuperAdmin();
    $this->post(route('mg.immobilisations.dotation', $this->immo), ['periode' => '2027-01']);
    $this->post(route('mg.immobilisations.dotation', $this->immo), ['periode' => '2027-01'])
        ->assertSessionHas('error');
    expect(ImmobilisationDotation::where('immobilisation_id', $this->immo->id)->count())->toBe(1);
});

it('plafonne la dernière dotation à la VNC restante', function () {
    // Immo presque amortie
    $this->immo->update([
        'valeur_nette_comptable' => 20_000,
        'amortissement_cumule' => 1_180_000,
        'valeur_residuelle' => 0,
    ]);
    $dotation = $this->immo->enregistrerDotation(Carbon::create(2029, 12, 1));
    expect((float) $dotation->montant)->toBe(20_000.0); // plafonné, pas 33 333
    expect((float) $this->immo->fresh()->valeur_nette_comptable)->toBe(0.0);
    expect($this->immo->fresh()->est_totalement_amorti)->toBeTrue();
});

it('refuse une dotation si immobilisation totalement amortie', function () {
    $this->immo->update(['valeur_nette_comptable' => 0, 'amortissement_cumule' => 1_200_000]);
    expect($this->immo->enregistrerDotation(Carbon::create(2027, 1, 1)))->toBeNull();
});

// ═════════ Sortie immobilisation ═════════

it('sort une immobilisation avec motif', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('mg.immobilisations.sortir', $this->immo), [
        'date_sortie' => '2027-06-30',
        'motif_sortie' => 'Vol déclaré à la police.',
    ]);
    $r->assertRedirect();
    $this->immo->refresh();
    expect($this->immo->date_sortie?->format('Y-m-d'))->toBe('2027-06-30');
    expect($this->immo->etat)->toBe('sortie');
    expect($this->immo->statut)->toBe(0);
});

// ═════════ Dysfonctionnement workflow ═════════

it('signale un dysfonctionnement lié à une immobilisation', function () {
    actingAsSuperAdmin();
    $this->post(route('mg.dysfonctionnements.store'), [
        'label' => 'Écran cassé',
        'immobilisation_id' => $this->immo->id,
        'priorite' => 'haute',
    ])->assertRedirect();

    $d = Dysfonctionnement::latest()->first();
    expect($d->statut)->toBe(Dysfonctionnement::STATUT_SIGNALE);
    expect($d->immobilisation_id)->toBe($this->immo->id);
    expect($d->declarant_id)->toBe(User::role('super-admin')->first()->id);
});

it('workflow dysfonctionnement : signalé → pris en charge → résolu → fermé', function () {
    actingAsSuperAdmin();
    $this->post(route('mg.dysfonctionnements.store'), ['label' => 'Test', 'priorite' => 'normale']);
    $d = Dysfonctionnement::latest()->first();

    $this->post(route('mg.dysfonctionnements.prendre-en-charge', $d))->assertRedirect();
    expect($d->fresh()->statut)->toBe(Dysfonctionnement::STATUT_PRIS_EN_CHARGE);
    expect($d->fresh()->pris_en_charge_at)->not->toBeNull();

    $this->post(route('mg.dysfonctionnements.resoudre', $d), ['commentaire_resolution' => 'Écran remplacé.'])->assertRedirect();
    expect($d->fresh()->statut)->toBe(Dysfonctionnement::STATUT_RESOLU);
    expect($d->fresh()->commentaire_resolution)->toBe('Écran remplacé.');

    $this->post(route('mg.dysfonctionnements.fermer', $d))->assertRedirect();
    expect($d->fresh()->statut)->toBe(Dysfonctionnement::STATUT_FERME);
});

// ═════════ Intervention workflow ═════════

it('planifie une intervention depuis un dysfonctionnement (rattachement direct)', function () {
    actingAsSuperAdmin();
    $d = Dysfonctionnement::create([
        'label' => 'Panne climatiseur', 'immobilisation_id' => $this->immo->id,
        'priorite' => 'haute', 'date_signalement' => now(),
        'statut' => Dysfonctionnement::STATUT_SIGNALE,
    ]);
    $this->post(route('mg.dysfonctionnements.planifier-intervention', $d), [
        'label' => 'Réparation clim',
        'type_intervention' => 'corrective',
    ])->assertRedirect();

    $i = Intervention::latest()->first();
    expect($i->dysfonctionnement_id)->toBe($d->id);
    expect($i->immobilisation_id)->toBe($this->immo->id);
    expect($i->statut)->toBe(Intervention::STATUT_PLANIFIEE);
});

it('workflow intervention : planifiée → en cours → terminée + auto-résolution du dysfonctionnement', function () {
    actingAsSuperAdmin();
    $d = Dysfonctionnement::create([
        'label' => 'Test', 'statut' => Dysfonctionnement::STATUT_PRIS_EN_CHARGE,
        'date_signalement' => now(),
    ]);
    $i = Intervention::create([
        'label' => 'Réparer', 'dysfonctionnement_id' => $d->id,
        'statut' => Intervention::STATUT_PLANIFIEE,
        'type_intervention' => 'corrective',
    ]);

    $this->post(route('mg.interventions.demarrer', $i))->assertRedirect();
    $i->refresh();
    expect($i->statut)->toBe(Intervention::STATUT_EN_COURS);
    expect($i->date_debut)->not->toBeNull();

    $this->post(route('mg.interventions.terminer', $i), [
        'rapport' => 'Fait.', 'cout' => 15000,
        'statut_resolution' => Intervention::RESOLUTION_RESOLU,
    ])->assertRedirect();
    $i->refresh();
    expect($i->statut)->toBe(Intervention::STATUT_TERMINEE);
    expect((float) $i->cout)->toBe(15000.0);
    expect($i->statut_resolution)->toBe(Intervention::RESOLUTION_RESOLU);
    // Le dysfonctionnement rattaché doit être auto-résolu
    expect($d->fresh()->statut)->toBe(Dysfonctionnement::STATUT_RESOLU);
});

it('annule une intervention avec motif', function () {
    actingAsSuperAdmin();
    $i = Intervention::create([
        'label' => 'Test', 'statut' => Intervention::STATUT_PLANIFIEE,
        'type_intervention' => 'preventive',
    ]);
    $this->post(route('mg.interventions.annuler', $i), [
        'motif_annulation' => 'Reporté à la semaine prochaine.',
    ])->assertRedirect();

    expect($i->fresh()->statut)->toBe(Intervention::STATUT_ANNULEE);
    expect($i->fresh()->motif_annulation)->toContain('Reporté');
});

it('refuse démarrage si intervention déjà terminée', function () {
    actingAsSuperAdmin();
    $i = Intervention::create([
        'label' => 'Test', 'statut' => Intervention::STATUT_TERMINEE,
        'type_intervention' => 'corrective',
    ]);
    $this->post(route('mg.interventions.demarrer', $i))->assertSessionHas('error');
});

it('refuse fermeture d\'un dysfonctionnement non résolu', function () {
    actingAsSuperAdmin();
    $d = Dysfonctionnement::create(['label' => 'X', 'statut' => Dysfonctionnement::STATUT_SIGNALE, 'date_signalement' => now()]);
    $this->post(route('mg.dysfonctionnements.fermer', $d))->assertSessionHas('error');
});
