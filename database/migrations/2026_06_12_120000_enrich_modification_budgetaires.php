<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enrichit la table modification_budgetaires pour clarifier la sémantique :
 *   - Le cahier des charges parle de modifications budgétaires (transferts entre lignes ou ajouts).
 *   - Les anciennes colonnes id_compte_* sont conservées en legacy ; on ajoute des FK explicites
 *     vers budget_lignes (source/destination) et exercice (de rattachement).
 *   - On ajoute le type ('transfert' ou 'ajout') et l'exercice_id pour cohérence.
 *   - Workflow : statut 0=brouillon, 1=soumise, 2=approuvée, 3=appliquée, 4=rejetée.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('modification_budgetaires', function (Blueprint $t) {
            // FK explicites
            $t->foreignId('exercice_id')->nullable()->after('id')
                ->constrained('exercices')->nullOnDelete();
            $t->foreignId('budget_ligne_source_id')->nullable()->after('exercice_id')
                ->constrained('budget_lignes')->nullOnDelete();
            $t->foreignId('budget_ligne_destination_id')->nullable()->after('budget_ligne_source_id')
                ->constrained('budget_lignes')->nullOnDelete();

            // Type : 'transfert' (entre 2 lignes) ou 'ajout' (apport sur 1 ligne)
            $t->string('type_modification', 20)->default('transfert')->after('budget_ligne_destination_id');

            // Traçabilité workflow
            $t->foreignId('soumis_par')->nullable()->after('statut')
                ->constrained('users')->nullOnDelete();
            $t->timestamp('soumis_at')->nullable()->after('soumis_par');
            $t->foreignId('approuve_par')->nullable()->after('soumis_at')
                ->constrained('users')->nullOnDelete();
            $t->timestamp('approuve_at')->nullable()->after('approuve_par');
            $t->foreignId('applique_par')->nullable()->after('approuve_at')
                ->constrained('users')->nullOnDelete();
            $t->timestamp('applique_at')->nullable()->after('applique_par');
            $t->text('motif_rejet')->nullable()->after('applique_at');

            $t->softDeletes();

            $t->index(['statut', 'exercice_id']);
        });
    }

    public function down(): void
    {
        Schema::table('modification_budgetaires', function (Blueprint $t) {
            $t->dropConstrainedForeignId('exercice_id');
            $t->dropConstrainedForeignId('budget_ligne_source_id');
            $t->dropConstrainedForeignId('budget_ligne_destination_id');
            $t->dropConstrainedForeignId('soumis_par');
            $t->dropConstrainedForeignId('approuve_par');
            $t->dropConstrainedForeignId('applique_par');
            $t->dropColumn(['type_modification', 'soumis_at', 'approuve_at', 'applique_at', 'motif_rejet']);
            $t->dropSoftDeletes();
        });
    }
};
