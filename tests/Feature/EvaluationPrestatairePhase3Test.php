<?php

use App\Models\CampagneEvaluation;
use App\Models\CritereEvaluation;
use App\Models\EvaluationPrestataire;
use App\Models\Intranet\ContactOrganisation;
use App\Models\ThemeEvaluation;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    $this->user = User::factory()->create();
    $this->prestataire = ContactOrganisation::create([
        'nom' => 'Prest', 'raison_sociale' => 'PREST SA', 'type' => 'fournisseur',
        'actif' => true, 'created_by' => $this->user->id,
    ]);
    $this->theme = ThemeEvaluation::create(['libelle' => 'Qualité', 'ordre' => 0, 'actif' => true]);
    $this->crit1 = CritereEvaluation::create([
        'theme_id' => $this->theme->id, 'libelle' => 'Respect délai',
        'echelle_min' => 1, 'echelle_max' => 5, 'poids' => 2.0, 'actif' => true,
    ]);
    $this->crit2 = CritereEvaluation::create([
        'theme_id' => $this->theme->id, 'libelle' => 'Conformité',
        'echelle_min' => 1, 'echelle_max' => 5, 'poids' => 1.0, 'actif' => true,
    ]);
});

// ═════════ Référentiel critères ═════════

it('gère thèmes et critères via HTTP', function () {
    actingAsSuperAdmin();
    $this->post(route('referentiel.evaluations.themes.store'), ['libelle' => 'Communication'])->assertRedirect();
    expect(ThemeEvaluation::where('libelle', 'Communication')->exists())->toBeTrue();

    $t = ThemeEvaluation::where('libelle', 'Communication')->first();
    $this->post(route('referentiel.evaluations.criteres.store'), [
        'libelle' => 'Réactivité', 'theme_id' => $t->id,
        'echelle_min' => 1, 'echelle_max' => 5, 'poids' => 1.5,
    ])->assertRedirect();
    expect(CritereEvaluation::where('libelle', 'Réactivité')->exists())->toBeTrue();
});

// ═════════ Évaluation ad hoc ═════════

it('enregistre une évaluation ad-hoc + calcule la note pondérée normalisée', function () {
    actingAsSuperAdmin();
    $r = $this->post(route('appro.evaluations.store'), [
        'prestataire_id' => $this->prestataire->id,
        'source' => EvaluationPrestataire::SOURCE_AD_HOC,
        'commentaire' => 'RAS',
        'notes' => [
            ['critere_id' => $this->crit1->id, 'note' => 4], // 4/5 × poids 2 = 8
            ['critere_id' => $this->crit2->id, 'note' => 3], // 3/5 × poids 1 = 3
        ],
    ]);
    $r->assertRedirect();
    $eval = EvaluationPrestataire::latest()->first();
    // Note pondérée normalisée sur 5 : ((4/5*5)*2 + (3/5*5)*1) / (2+1) = (8+3)/3 = 3.67
    expect((float) $eval->note_globale)->toBe(3.67);
    expect($eval->notes->count())->toBe(2);
});

// ═════════ Évaluation post-livraison ═════════

it('peut être rattachée à une commande fournisseur (source = livraison)', function () {
    actingAsSuperAdmin();
    $cmd = \App\Models\CommandeFournisseur::create([
        'numero_commande' => 'CMD-EVA-01', 'fournisseur_id' => $this->prestataire->id,
        'date_commande' => now(), 'statut' => \App\Models\CommandeFournisseur::STATUT_LIVREE,
        'created_by' => $this->user->id,
    ]);
    $this->post(route('appro.evaluations.store'), [
        'prestataire_id' => $this->prestataire->id,
        'source' => EvaluationPrestataire::SOURCE_LIVRAISON,
        'commande_fournisseur_id' => $cmd->id,
        'notes' => [['critere_id' => $this->crit1->id, 'note' => 5]],
    ])->assertRedirect();

    $eval = EvaluationPrestataire::latest()->first();
    expect($eval->source)->toBe('livraison');
    expect($eval->commande_fournisseur_id)->toBe($cmd->id);
    expect((float) $eval->note_globale)->toBe(5.0);
});

// ═════════ Campagnes ═════════

it('crée une campagne avec prestataires ciblés', function () {
    actingAsSuperAdmin();
    $p2 = ContactOrganisation::create(['nom' => 'P2', 'type' => 'fournisseur', 'actif' => true, 'created_by' => $this->user->id]);
    $this->post(route('appro.campagnes.store'), [
        'libelle' => 'Campagne 2027 Q1', 'date_debut' => '2027-01-01', 'date_fin' => '2027-03-31',
        'prestataires' => [$this->prestataire->id, $p2->id],
    ])->assertRedirect();

    $c = CampagneEvaluation::latest()->first();
    expect($c->statut)->toBe(CampagneEvaluation::STATUT_BROUILLON);
    expect($c->prestataires->count())->toBe(2);
});

it('workflow campagne : brouillon → en cours → clôturée', function () {
    actingAsSuperAdmin();
    $c = CampagneEvaluation::create([
        'libelle' => 'X', 'date_debut' => now(), 'statut' => CampagneEvaluation::STATUT_BROUILLON,
        'created_by' => auth()->id(),
    ]);
    $this->post(route('appro.campagnes.lancer', $c))->assertRedirect();
    expect($c->fresh()->statut)->toBe(CampagneEvaluation::STATUT_EN_COURS);

    $this->post(route('appro.campagnes.cloturer', $c))->assertRedirect();
    expect($c->fresh()->statut)->toBe(CampagneEvaluation::STATUT_CLOTUREE);
});

it('évaluation dans le cadre d\'une campagne marque le prestataire comme évalué', function () {
    actingAsSuperAdmin();
    $c = CampagneEvaluation::create(['libelle' => 'C', 'date_debut' => now(), 'statut' => CampagneEvaluation::STATUT_EN_COURS]);
    $c->prestataires()->attach($this->prestataire->id, ['evaluation_faite' => false]);

    $this->post(route('appro.evaluations.store'), [
        'prestataire_id' => $this->prestataire->id,
        'source' => EvaluationPrestataire::SOURCE_CAMPAGNE,
        'campagne_id' => $c->id,
        'notes' => [['critere_id' => $this->crit1->id, 'note' => 4]],
    ])->assertRedirect();

    $pivot = \DB::table('campagne_prestataires')
        ->where('campagne_id', $c->id)->where('prestataire_id', $this->prestataire->id)->first();
    expect($pivot->evaluation_faite)->toBeTrue();
});
