<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->text('preuve')->nullable()->after('resultat');
        });

        Schema::table('dysfonctionnements', function (Blueprint $table) {
            // Étape 1 : le technicien propose la résolution (via intervention.terminer)
            $table->timestamp('resolution_proposee_at')->nullable();
            $table->foreignId('resolution_proposee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->text('commentaire_resolution_proposee')->nullable();
            // Étape 2 : l'émetteur (déclarant) valide la résolution → passage à RESOLU
            $table->timestamp('validation_resolution_at')->nullable();
            $table->foreignId('validation_resolution_par')->nullable()->constrained('users')->nullOnDelete();
            $table->text('commentaire_validation_resolution')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dysfonctionnements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('validation_resolution_par');
            $table->dropConstrainedForeignId('resolution_proposee_par');
            $table->dropColumn(['resolution_proposee_at', 'commentaire_resolution_proposee',
                                'validation_resolution_at', 'commentaire_validation_resolution']);
        });
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropColumn('preuve');
        });
    }
};
