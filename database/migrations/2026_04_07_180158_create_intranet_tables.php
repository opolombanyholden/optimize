<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Statuts (Non démarré, En cours, En pause, Terminé, Annulé)
        Schema::create('intranet_statuts', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('couleur', 20)->default('#64748b');
            $table->timestamps();
        });

        // ─── Priorités (Basse, Normal, Haute, Urgente)
        Schema::create('intranet_priorites', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('couleur', 20)->default('#64748b');
            $table->timestamps();
        });

        // ─── Types d'événements
        Schema::create('intranet_type_evenements', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code', 50)->nullable();
            $table->string('couleur', 20)->default('#4F46E5');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ─── Services / Départements internes
        Schema::create('intranet_services', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->foreignId('chef_du_service_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ─── Annonces internes
        Schema::create('intranet_annonces', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        // ─── Actualités / News
        Schema::create('intranet_news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        // ─── Événements / Agenda
        Schema::create('intranet_evenements', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->string('lieu')->nullable();
            $table->string('statut')->default('prevu');
            $table->boolean('is_public')->default(false);
            $table->text('description')->nullable();
            $table->foreignId('type_evenement_id')->constrained('intranet_type_evenements')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ─── Projets
        Schema::create('intranet_projets', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->foreignId('priorite_id')->nullable()->constrained('intranet_priorites')->nullOnDelete();
            $table->foreignId('statut_id')->nullable()->constrained('intranet_statuts')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ─── Tâches
        Schema::create('intranet_taches', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->foreignId('projet_id')->nullable()->constrained('intranet_projets')->cascadeOnDelete();
            $table->foreignId('priorite_id')->nullable()->constrained('intranet_priorites')->nullOnDelete();
            $table->foreignId('statut_id')->nullable()->constrained('intranet_statuts')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->timestamp('deadline_alerted_at')->nullable();
            $table->timestamps();
        });

        // ─── Courriers (entrant / sortant / interne)
        Schema::create('intranet_courriers', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['entrant', 'sortant', 'interne']);
            $table->string('objet');
            $table->string('reference')->unique();
            $table->text('contenu')->nullable();
            $table->string('expediteur')->nullable();
            $table->date('date_reception')->nullable();
            $table->date('date_expedition')->nullable();
            $table->enum('priorite', ['normale', 'urgente'])->default('normale');
            $table->string('statut')->default('recu');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ─── Ressources documentaires
        Schema::create('intranet_ressources', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('intranet_ressources')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ─── Pivot : membres de projets
        Schema::create('intranet_projet_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        });

        // ─── Pivot : assignation tâches
        Schema::create('intranet_tache_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('intranet_taches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_tache_user');
        Schema::dropIfExists('intranet_projet_user');
        Schema::dropIfExists('intranet_ressources');
        Schema::dropIfExists('intranet_courriers');
        Schema::dropIfExists('intranet_taches');
        Schema::dropIfExists('intranet_projets');
        Schema::dropIfExists('intranet_evenements');
        Schema::dropIfExists('intranet_news');
        Schema::dropIfExists('intranet_annonces');
        Schema::dropIfExists('intranet_services');
        Schema::dropIfExists('intranet_type_evenements');
        Schema::dropIfExists('intranet_priorites');
        Schema::dropIfExists('intranet_statuts');
    }
};
