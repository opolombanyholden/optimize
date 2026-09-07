<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Configuration avancement au niveau du grade
        Schema::table('grades', function (Blueprint $table) {
            $table->boolean('avancement_automatique')->default(false);
            $table->unsignedInteger('duree_max_mois')->nullable();          // ex : 36 mois
            $table->foreignId('grade_suivant_id')->nullable()->constrained('grades')->nullOnDelete();
        });

        // Workflow de validation des avancements (surtout automatiques)
        Schema::table('avancements', function (Blueprint $table) {
            // 'valide' par défaut : compatibilité avec l'existant (les manuels sont directement validés)
            $table->string('statut', 20)->default('valide');
            // 'manuel' | 'automatique'
            $table->string('origine', 20)->default('manuel');
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('valide_at')->nullable();
            $table->text('motif_refus')->nullable();

            $table->index(['statut', 'origine']);
        });
    }

    public function down(): void
    {
        Schema::table('avancements', function (Blueprint $table) {
            $table->dropForeign(['valide_par']);
            $table->dropIndex(['statut', 'origine']);
            $table->dropColumn(['statut', 'origine', 'valide_par', 'valide_at', 'motif_refus']);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['grade_suivant_id']);
            $table->dropColumn(['avancement_automatique', 'duree_max_mois', 'grade_suivant_id']);
        });
    }
};
