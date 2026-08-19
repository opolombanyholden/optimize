<?php

use App\Models\CommandeInterne;
use App\Models\Produit;
use App\Models\User;
use App\Notifications\CommandeInterneAssigneeN1;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    seedRoles();
    $this->demandeur = User::factory()->create(['name' => 'Demandeur', 'statut' => 1]);
    $this->n1       = User::factory()->create(['name' => 'ValideurN1', 'statut' => 1]);
    $this->produit = Produit::create([
        'code' => 'SN1-P', 'designation' => 'Article X', 'type_article' => 'bien',
        'est_stockable' => true, 'unite_mesure' => 'u', 'prix_unitaire' => 500, 'statut' => 1,
    ]);
});

it('création d\'une commande interne sans N+1 accepté (facultatif)', function () {
    $this->actingAs($this->demandeur);
    $this->post(route('appro.commandes-internes.store'), [
        'objet' => 'Sans N+1',
        // superieur_id absent
        'lignes' => [['produit_id' => $this->produit->id, 'quantite_demandee' => 2]],
    ])->assertRedirect();

    $c = CommandeInterne::latest()->first();
    expect($c->superieur_id)->toBeNull();
    expect($c->statut)->toBe(CommandeInterne::STATUT_BROUILLON);
});

it('soumission sans N+1 → passe directement en TRANSMISE_APPRO (triage Appro)', function () {
    $this->actingAs($this->demandeur);
    $c = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(), 'objet' => 'Direct Appro',
        'demandeur_id' => $this->demandeur->id, 'date_demande' => now()->toDateString(),
        'statut' => CommandeInterne::STATUT_BROUILLON,
    ]);
    $c->lignes()->create(['produit_id' => $this->produit->id, 'designation' => 'X', 'quantite_demandee' => 1]);

    $this->post(route('appro.commandes-internes.soumettre-n1', $c))->assertRedirect();

    $c->refresh();
    expect($c->statut)->toBe(CommandeInterne::STATUT_TRANSMISE_APPRO);
    expect($c->soumise_n1_at)->not->toBeNull();
    expect($c->transmise_appro_at)->not->toBeNull();
    expect($c->superieur_id)->toBeNull();
});

it('soumission avec N+1 → passe en EN_ATTENTE_N1 (comportement classique)', function () {
    $this->actingAs($this->demandeur);
    $c = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(), 'objet' => 'Avec N+1',
        'demandeur_id' => $this->demandeur->id, 'superieur_id' => $this->n1->id,
        'date_demande' => now()->toDateString(), 'statut' => CommandeInterne::STATUT_BROUILLON,
    ]);
    $c->lignes()->create(['produit_id' => $this->produit->id, 'designation' => 'X', 'quantite_demandee' => 1]);

    $this->post(route('appro.commandes-internes.soumettre-n1', $c))->assertRedirect();
    expect($c->fresh()->statut)->toBe(CommandeInterne::STATUT_EN_ATTENTE_N1);
});

it('Appro peut assigner un N+1 après-coup à une commande directement transmise', function () {
    Notification::fake();
    actingAsSuperAdmin();

    $c = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(), 'objet' => 'Assign test',
        'demandeur_id' => $this->demandeur->id, 'date_demande' => now()->toDateString(),
        'statut' => CommandeInterne::STATUT_TRANSMISE_APPRO,
        'soumise_n1_at' => now(), 'transmise_appro_at' => now(),
    ]);
    $c->lignes()->create(['produit_id' => $this->produit->id, 'designation' => 'X', 'quantite_demandee' => 1]);
    expect($c->peutEtreAssignee())->toBeTrue();

    $this->post(route('appro.commandes-internes.assigner-n1', $c), [
        'superieur_id' => $this->n1->id,
        'commentaire_appro' => 'Montant important, validation N+1 requise',
    ])->assertRedirect()->assertSessionHas('success');

    $c->refresh();
    expect($c->superieur_id)->toBe($this->n1->id);
    expect($c->statut)->toBe(CommandeInterne::STATUT_EN_ATTENTE_N1);
    expect($c->commentaire_appro)->toContain('Montant important');

    // Le demandeur a été notifié
    Notification::assertSentTo($this->demandeur, CommandeInterneAssigneeN1::class,
        fn($n) => $n->commande->id === $c->id && $n->n1->id === $this->n1->id);
});

it('assignation N+1 refusée si commande hors état TRANSMISE_APPRO', function () {
    actingAsSuperAdmin();
    $c = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(), 'objet' => 'Wrong status',
        'demandeur_id' => $this->demandeur->id, 'date_demande' => now()->toDateString(),
        'statut' => CommandeInterne::STATUT_BROUILLON,
    ]);
    $this->post(route('appro.commandes-internes.assigner-n1', $c), [
        'superieur_id' => $this->n1->id,
    ])->assertSessionHas('error');
});

it('la notification stockée en DB contient un message clair pour le demandeur', function () {
    actingAsSuperAdmin();
    $c = CommandeInterne::create([
        'numero' => 'CI-NOTIF-1', 'objet' => 'Notif test',
        'demandeur_id' => $this->demandeur->id, 'date_demande' => now()->toDateString(),
        'statut' => CommandeInterne::STATUT_TRANSMISE_APPRO,
    ]);
    $c->lignes()->create(['produit_id' => $this->produit->id, 'designation' => 'X', 'quantite_demandee' => 1]);
    $this->post(route('appro.commandes-internes.assigner-n1', $c), [
        'superieur_id' => $this->n1->id,
    ]);

    $notif = $this->demandeur->notifications()->latest()->first();
    expect($notif)->not->toBeNull();
    expect($notif->type)->toBe(CommandeInterneAssigneeN1::class);
    expect($notif->data['numero'])->toBe('CI-NOTIF-1');
    expect($notif->data['message'])->toContain('a été transmise à');
    expect($notif->data['message'])->toContain('ValideurN1');
});
