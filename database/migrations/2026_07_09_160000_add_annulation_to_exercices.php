<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute l'annulation à un exercice budgétaire (statut = 4).
 *
 * Règle métier : un exercice en cours d'exécution ne peut être ni supprimé
 * ni modifié — seulement clôturé (fin normale) ou annulé (fin anticipée avec motif).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('exercices', function (Blueprint $t) {
            if (!Schema::hasColumn('exercices', 'annule_par')) {
                $t->foreignId('annule_par')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('exercices', 'annule_at')) {
                $t->timestamp('annule_at')->nullable();
            }
            if (!Schema::hasColumn('exercices', 'motif_annulation')) {
                $t->text('motif_annulation')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exercices', function (Blueprint $t) {
            if (Schema::hasColumn('exercices', 'annule_par')) {
                $t->dropForeign(['annule_par']);
                $t->dropColumn('annule_par');
            }
            $t->dropColumn(['annule_at', 'motif_annulation']);
        });
    }
};
