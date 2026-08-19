<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════
        // ENRICHISSEMENT INTRANET_TACHES
        // ══════════════════════════════════════════════════════
        Schema::table('intranet_taches', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->string('resume', 500)->nullable()->after('description');
            $table->text('besoins')->nullable()->after('resume');
            $table->decimal('cout_execution', 15, 2)->nullable()->after('besoins');
            $table->string('devise_cout', 10)->default('XAF')->after('cout_execution');
            $table->decimal('ponderation', 5, 2)->nullable()->after('devise_cout'); // % dans le projet
            $table->date('date_debut_reelle')->nullable()->after('date_fin');
            $table->date('date_fin_reelle')->nullable()->after('date_debut_reelle');
            $table->decimal('temps_reel_heures', 8, 2)->nullable()->after('date_fin_reelle');

            // Validation
            $table->enum('statut_validation', ['non_soumis', 'soumis', 'approuve', 'rejete', 'revisions'])
                  ->default('non_soumis')->after('temps_reel_heures');
            $table->timestamp('soumis_le')->nullable()->after('statut_validation');
            $table->timestamp('valide_le')->nullable()->after('soumis_le');
            $table->text('motif_rejet')->nullable()->after('valide_le');

            // Évaluation à la clôture
            $table->unsignedTinyInteger('note_evaluation')->nullable()->after('motif_rejet'); // 1-5 étoiles
            $table->text('appreciation')->nullable()->after('note_evaluation');
            $table->foreignId('evalue_par')->nullable()->after('appreciation')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('evalue_le')->nullable()->after('evalue_par');

            $table->string('media_principal')->nullable()->after('evalue_le');
            $table->enum('media_principal_type', ['image', 'video', 'document'])->nullable()->after('media_principal');
            $table->unsignedInteger('vues_count')->default(0)->after('media_principal_type');

            $table->softDeletes();
        });

        // Valideurs de tâche (pivot : users individuels ou groupes)
        Schema::create('intranet_tache_valideurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('intranet_taches')->cascadeOnDelete();
            $table->enum('valideur_type', ['user', 'groupe']);
            $table->unsignedBigInteger('valideur_id');
            $table->timestamps();

            $table->unique(['tache_id', 'valideur_type', 'valideur_id'], 'tache_valideur_unique');
            $table->index(['valideur_type', 'valideur_id']);
        });

        // Historique des validations / actions sur la tâche
        Schema::create('intranet_tache_historique', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('intranet_taches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', [
                'cree', 'modifie', 'soumis', 'approuve', 'rejete',
                'revisions_demandees', 'resoumis', 'evalue', 'cloture',
                'piece_jointe_ajoutee', 'commentaire',
            ]);
            $table->text('commentaire')->nullable();
            $table->unsignedTinyInteger('note')->nullable(); // pour action=evalue
            $table->json('meta')->nullable(); // données complémentaires
            $table->timestamps();

            $table->index('tache_id');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_tache_historique');
        Schema::dropIfExists('intranet_tache_valideurs');

        Schema::table('intranet_taches', function (Blueprint $table) {
            $table->dropForeign(['evalue_par']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'slug', 'resume', 'besoins', 'cout_execution', 'devise_cout',
                'ponderation', 'date_debut_reelle', 'date_fin_reelle', 'temps_reel_heures',
                'statut_validation', 'soumis_le', 'valide_le', 'motif_rejet',
                'note_evaluation', 'appreciation', 'evalue_par', 'evalue_le',
                'media_principal', 'media_principal_type', 'vues_count',
            ]);
        });
    }
};
