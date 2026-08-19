<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════
        // Demandes de modification / suppression
        // Polymorphique : Projet, ProjetPhase, Jalon, Tache
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_demandes_modification', function (Blueprint $table) {
            $table->id();
            $table->string('modifiable_type');
            $table->unsignedBigInteger('modifiable_id');
            $table->enum('type_demande', ['modification', 'suppression']);
            $table->enum('statut', ['en_attente', 'approuve', 'rejete'])->default('en_attente');
            $table->text('motif');                          // Pourquoi modifier / supprimer
            $table->json('champs_modifies')->nullable();    // {champ: {ancien, nouveau}} pour les modifs
            $table->foreignId('demandeur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('decideur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decide_le')->nullable();
            $table->text('commentaire_decision')->nullable();
            $table->timestamps();

            $table->index(['modifiable_type', 'modifiable_id'], 'dm_modifiable_idx');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_demandes_modification');
    }
};
