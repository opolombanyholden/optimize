<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intranet_plans_action', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->foreignId('objectif_id')->constrained('intranet_objectifs')->cascadeOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_debut')->nullable();
            $table->date('date_echeance')->nullable();
            $table->date('date_realisation')->nullable();
            $table->enum('statut', ['planifie', 'en_cours', 'realise', 'reporte', 'annule'])
                  ->default('planifie');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])
                  ->default('normale');
            $table->unsignedTinyInteger('avancement')->default(0); // 0-100
            $table->text('resultats_attendus')->nullable();
            $table->text('moyens_requis')->nullable();
            $table->decimal('budget_estime', 15, 2)->nullable();
            $table->decimal('budget_reel', 15, 2)->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('objectif_id');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_plans_action');
    }
};
