<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrats_fournisseur', function (Blueprint $table) {
            $table->string('frequence_paiement', 20)->default('ponctuel')->after('devise'); // ponctuel|mensuel|bimestriel|trimestriel|semestriel|annuel
            $table->decimal('montant_par_paiement', 14, 2)->nullable()->after('frequence_paiement');
            $table->smallInteger('jour_paiement')->nullable()->after('montant_par_paiement'); // 1-31 : jour du mois pour récurrents
            $table->smallInteger('delai_paiement_jours')->nullable()->after('jour_paiement'); // ex: 30j après facturation
        });
    }

    public function down(): void
    {
        Schema::table('contrats_fournisseur', function (Blueprint $table) {
            $table->dropColumn(['frequence_paiement', 'montant_par_paiement', 'jour_paiement', 'delai_paiement_jours']);
        });
    }
};
