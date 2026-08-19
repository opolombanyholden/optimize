<?php

use App\Models\CommandeInterne;
use App\Models\User;

beforeEach(function () {
    seedRoles();
    // Créer plusieurs commandes internes de différents demandeurs et différents N+1
    $this->demA = User::factory()->create(['name' => 'DemA', 'statut' => 1]);
    $this->demB = User::factory()->create(['name' => 'DemB', 'statut' => 1]);
    $this->n1X  = User::factory()->create(['name' => 'N1X', 'statut' => 1]);
    $this->n1Y  = User::factory()->create(['name' => 'N1Y', 'statut' => 1]);

    // Commande de A → N1X (brouillon)
    $this->cmdA = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(), 'objet' => 'ObjetCmdA',
        'demandeur_id' => $this->demA->id, 'superieur_id' => $this->n1X->id,
        'date_demande' => now()->toDateString(), 'statut' => CommandeInterne::STATUT_BROUILLON,
    ]);
    // Commande de B → N1Y en attente N+1
    $this->cmdB = CommandeInterne::create([
        'numero' => CommandeInterne::genererNumero(), 'objet' => 'ObjetCmdB',
        'demandeur_id' => $this->demB->id, 'superieur_id' => $this->n1Y->id,
        'date_demande' => now()->toDateString(), 'statut' => CommandeInterne::STATUT_EN_ATTENTE_N1,
    ]);
});

it('un utilisateur normal ne voit que ses demandes sur l\'onglet "mes"', function () {
    $this->actingAs($this->demA);
    $r = $this->get(route('appro.commandes-internes.index', ['onglet' => 'mes']));
    $r->assertStatus(200);
    $r->assertSee('ObjetCmdA');
    $r->assertDontSee('ObjetCmdB');
});

it('le super-admin voit TOUTES les commandes sur l\'onglet "mes"', function () {
    actingAsSuperAdmin(); // Utilisateur ayant le rôle super-admin
    $r = $this->get(route('appro.commandes-internes.index', ['onglet' => 'mes']));
    $r->assertStatus(200);
    $r->assertSee('ObjetCmdA');
    $r->assertSee('ObjetCmdB');
});

it('le super-admin voit tous les tickets à valider sur l\'onglet "a_valider" (pas seulement les siens)', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('appro.commandes-internes.index', ['onglet' => 'a_valider']));
    $r->assertStatus(200);
    // cmdB est en attente N+1 mais son N+1 est N1Y, pas le super-admin — normalement invisible
    $r->assertSee('ObjetCmdB');
});

it('un N+1 non-superadmin ne voit sur "a_valider" que les demandes qu\'il doit valider', function () {
    $this->actingAs($this->n1Y);
    $r = $this->get(route('appro.commandes-internes.index', ['onglet' => 'a_valider']));
    $r->assertStatus(200);
    $r->assertSee('ObjetCmdB'); // Il est N+1 de cmdB
    $r->assertDontSee('ObjetCmdA'); // cmdA a un autre N+1 (et statut brouillon)
});

it('super-admin : onglet par défaut = "toutes"', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('appro.commandes-internes.index'));
    $r->assertStatus(200);
    // Sans paramètre onglet, super-admin devrait voir tout
    $r->assertSee('ObjetCmdA');
    $r->assertSee('ObjetCmdB');
});
