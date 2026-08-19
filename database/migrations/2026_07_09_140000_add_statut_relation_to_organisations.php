<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute `statut_relation` à intranet_contact_organisations.
 *
 * Ce champ porte le cycle de vie relationnel spécifique au TYPE :
 * - client       → prospect / en_negociation / actif / inactif / perdu
 * - fournisseur  → evaluation / reference / actif / suspendu / radie
 * - investisseur → prospect / en_discussion / actif / sortie
 * - administration / autre → actif / inactif
 * - partenaire   → prospect / actif / ancien
 *
 * Contrairement à `statut` (int, actif/inactif global), `statut_relation`
 * exprime l'étape du pipeline commercial ou de la relation d'affaires.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('intranet_contact_organisations', function (Blueprint $t) {
            if (!Schema::hasColumn('intranet_contact_organisations', 'statut_relation')) {
                $t->string('statut_relation', 30)->nullable()
                    ->comment('Étape du cycle relationnel — libellé varie selon `type`');
                $t->index(['type', 'statut_relation'], 'idx_org_type_statutrel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('intranet_contact_organisations', function (Blueprint $t) {
            $t->dropIndex('idx_org_type_statutrel');
            $t->dropColumn('statut_relation');
        });
    }
};
