<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enrichit le workflow des exercices budgétaires :
 *
 *  Statut principal (déjà existant) :
 *    1 = Planifié (planification budgétaire en cours)
 *    2 = En exécution (budget validé, locked)
 *    3 = Clôturé (définitif)
 *
 *  Nouveau sous-statut « validation_statut » (workflow de validation top-mgmt) :
 *    0 = En cours de planification (modifications budgétaires libres)
 *    1 = Soumis pour validation (lock, en attente top-mgmt)
 *    2 = Validé (déclenche transition statut 1→2)
 *    3 = Rejeté avec motif (retour à 0 + correction)
 *
 *  Traçabilité complète : qui/quand pour chaque transition.
 *
 *  Le flag « en attente de clôture » est calculé : statut=2 ET date_fin < now().
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('exercices', function (Blueprint $t) {
            // Workflow validation de la planification
            $t->smallInteger('validation_statut')->default(0)->after('statut');
            $t->foreignId('soumis_par')->nullable()->after('validation_statut')
                ->constrained('users')->nullOnDelete();
            $t->timestamp('soumis_at')->nullable()->after('soumis_par');
            $t->foreignId('valide_par')->nullable()->after('soumis_at')
                ->constrained('users')->nullOnDelete();
            $t->timestamp('valide_at')->nullable()->after('valide_par');
            $t->foreignId('cloture_par')->nullable()->after('valide_at')
                ->constrained('users')->nullOnDelete();
            $t->timestamp('cloture_at')->nullable()->after('cloture_par');
            $t->text('motif_rejet')->nullable()->after('cloture_at');

            $t->index(['statut', 'validation_statut']);
        });
    }

    public function down(): void
    {
        Schema::table('exercices', function (Blueprint $t) {
            $t->dropConstrainedForeignId('soumis_par');
            $t->dropConstrainedForeignId('valide_par');
            $t->dropConstrainedForeignId('cloture_par');
            $t->dropColumn(['validation_statut', 'soumis_at', 'valide_at', 'cloture_at', 'motif_rejet']);
        });
    }
};
