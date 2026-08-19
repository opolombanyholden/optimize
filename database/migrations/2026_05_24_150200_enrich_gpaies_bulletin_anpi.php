<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gpaies', function (Blueprint $table) {
            // Identification et règlement (modèle ANPI)
            $table->string('numero_bulletin', 30)->nullable()->unique();
            $table->string('mode_reglement', 30)->nullable(); // virement, cheque, especes, mobile_money
            $table->string('compte_bancaire', 50)->nullable();
            $table->decimal('part_impots', 4, 2)->default(1); // parts fiscales au moment du calcul (snapshot)

            // Cumuls annuels (snapshot au moment du calcul du bulletin)
            $table->decimal('cumul_annuel_brut', 14, 2)->default(0);
            $table->decimal('cumul_annuel_net_imposable', 14, 2)->default(0);
            $table->decimal('cumul_annuel_cotisations_sal', 14, 2)->default(0);
            $table->decimal('cumul_annuel_cotisations_pat', 14, 2)->default(0);
            $table->decimal('cumul_annuel_irpp', 14, 2)->default(0);
            $table->decimal('cumul_annuel_net_a_payer', 14, 2)->default(0);

            // Gestion congés intégrée au bulletin
            $table->decimal('brut_conges_mois', 12, 2)->default(0);
            $table->decimal('brut_conges_cumul', 14, 2)->default(0);
            $table->decimal('nb_jrs_acquis_mois', 5, 2)->default(0);
            $table->decimal('nb_jrs_acquis_annee', 5, 2)->default(0);
            $table->decimal('reste_a_prendre', 5, 2)->default(0);
            $table->decimal('acquis_n_moins_1', 5, 2)->default(0);

            // Snapshot identité (utile pour réimprimer un bulletin ancien sans risque de divergence)
            $table->jsonb('snapshot_employe')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gpaies', function (Blueprint $table) {
            $table->dropColumn([
                'numero_bulletin', 'mode_reglement', 'compte_bancaire', 'part_impots',
                'cumul_annuel_brut', 'cumul_annuel_net_imposable',
                'cumul_annuel_cotisations_sal', 'cumul_annuel_cotisations_pat',
                'cumul_annuel_irpp', 'cumul_annuel_net_a_payer',
                'brut_conges_mois', 'brut_conges_cumul',
                'nb_jrs_acquis_mois', 'nb_jrs_acquis_annee',
                'reste_a_prendre', 'acquis_n_moins_1',
                'snapshot_employe',
            ]);
        });
    }
};
