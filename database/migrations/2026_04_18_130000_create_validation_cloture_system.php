<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════
        // Table polymorphique des valideurs de clôture
        // Utilisable par : Projet, ProjetPhase, Jalon, Tache
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_valideurs', function (Blueprint $table) {
            $table->id();
            $table->string('validable_type');               // App\Models\Intranet\Projet, ProjetPhase, Jalon, Tache
            $table->unsignedBigInteger('validable_id');     // ID de l'entité
            $table->enum('valideur_type', ['user', 'groupe']);
            $table->unsignedBigInteger('valideur_id');      // user_id ou groupe_id
            $table->timestamps();

            $table->index(['validable_type', 'validable_id'], 'valideurs_validable_idx');
            $table->index(['valideur_type', 'valideur_id'], 'valideurs_valideur_idx');
            $table->unique(
                ['validable_type', 'validable_id', 'valideur_type', 'valideur_id'],
                'valideur_unique'
            );
        });

        // ══════════════════════════════════════════════════════
        // Historique des actions de validation (polymorphique)
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_historique_validations', function (Blueprint $table) {
            $table->id();
            $table->string('validable_type');
            $table->unsignedBigInteger('validable_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', [
                'cree', 'modifie',
                'soumis', 'resoumis',
                'approuve', 'rejete', 'revisions_demandees',
                'cloture', 'reouvert',
            ]);
            $table->text('justification')->nullable();     // Motif / justification de clôture
            $table->text('commentaire')->nullable();        // Commentaire du valideur
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['validable_type', 'validable_id'], 'hist_valid_validable_idx');
            $table->index('action');
        });

        // ── Champs validation sur intranet_projet_phases ─────
        Schema::table('intranet_projet_phases', function (Blueprint $table) {
            $table->enum('statut_cloture', ['ouvert', 'soumis', 'approuve', 'rejete', 'revisions'])
                  ->default('ouvert')->after('statut_id');
            $table->text('justification_cloture')->nullable()->after('statut_cloture');
            $table->timestamp('soumis_le')->nullable()->after('justification_cloture');
            $table->timestamp('valide_le')->nullable()->after('soumis_le');
            $table->text('motif_rejet')->nullable()->after('valide_le');
        });

        // ── Champs validation sur intranet_jalons ────────────
        Schema::table('intranet_jalons', function (Blueprint $table) {
            $table->enum('statut_cloture', ['ouvert', 'soumis', 'approuve', 'rejete', 'revisions'])
                  ->default('ouvert')->after('statut');
            $table->text('justification_cloture')->nullable()->after('statut_cloture');
            $table->timestamp('soumis_le')->nullable()->after('justification_cloture');
            $table->timestamp('valide_le')->nullable()->after('soumis_le');
            $table->text('motif_rejet')->nullable()->after('valide_le');
        });

        // ── Champs validation sur intranet_projets ───────────
        Schema::table('intranet_projets', function (Blueprint $table) {
            $table->enum('statut_cloture', ['ouvert', 'soumis', 'approuve', 'rejete', 'revisions'])
                  ->default('ouvert')->after('statut_id');
            $table->text('justification_cloture')->nullable()->after('statut_cloture');
            $table->timestamp('soumis_le')->nullable()->after('justification_cloture');
            $table->timestamp('valide_le')->nullable()->after('soumis_le');
            $table->text('motif_rejet')->nullable()->after('valide_le');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_projets', function (Blueprint $table) {
            $table->dropColumn(['statut_cloture', 'justification_cloture', 'soumis_le', 'valide_le', 'motif_rejet']);
        });
        Schema::table('intranet_jalons', function (Blueprint $table) {
            $table->dropColumn(['statut_cloture', 'justification_cloture', 'soumis_le', 'valide_le', 'motif_rejet']);
        });
        Schema::table('intranet_projet_phases', function (Blueprint $table) {
            $table->dropColumn(['statut_cloture', 'justification_cloture', 'soumis_le', 'valide_le', 'motif_rejet']);
        });
        Schema::dropIfExists('intranet_historique_validations');
        Schema::dropIfExists('intranet_valideurs');
    }
};
