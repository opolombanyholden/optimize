<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 1) Facture : tiers externe (non enregistré) via `tiers_source` + infos JSON.
 *    - tiers_source = 'organisation' (défaut) → tiers_id pointe vers ContactOrganisation
 *    - tiers_source = 'externe'                → tiers_infos_json contient les données
 *
 * 2) Ordre : rattachement optionnel à une facture (dépense→facture dépense,
 *    recette→facture recette). Une facture peut avoir plusieurs ordres.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $t) {
            if (!Schema::hasColumn('factures', 'tiers_source')) {
                $t->string('tiers_source', 20)->default('organisation')
                    ->comment('organisation | externe');
                $t->index('tiers_source');
            }
            if (!Schema::hasColumn('factures', 'tiers_infos_json')) {
                $t->json('tiers_infos_json')->nullable()
                    ->comment('Snapshot ou saisie manuelle pour tiers externe');
            }
        });

        Schema::table('finance_ordres', function (Blueprint $t) {
            if (!Schema::hasColumn('finance_ordres', 'facture_id')) {
                $t->foreignId('facture_id')->nullable()->after('budget_ligne_id')
                    ->constrained('factures')->nullOnDelete();
                $t->index('facture_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_ordres', function (Blueprint $t) {
            if (Schema::hasColumn('finance_ordres', 'facture_id')) {
                $t->dropForeign(['facture_id']);
                $t->dropColumn('facture_id');
            }
        });
        Schema::table('factures', function (Blueprint $t) {
            $t->dropIndex(['tiers_source']);
            $t->dropColumn(['tiers_source', 'tiers_infos_json']);
        });
    }
};
