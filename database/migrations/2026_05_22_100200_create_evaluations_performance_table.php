<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluations_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('evaluateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('periode', 30); // ex: "2026-S1", "2026-Annuel"
            $table->date('date_evaluation');
            $table->date('date_entretien')->nullable();
            $table->smallInteger('note_globale')->nullable(); // 1..5
            $table->jsonb('competences_evaluees')->nullable(); // [{competence_id, note, commentaire}]
            $table->text('points_forts')->nullable();
            $table->text('axes_amelioration')->nullable();
            $table->text('objectifs_periode_suivante')->nullable();
            $table->text('commentaire_employe')->nullable();
            $table->text('commentaire_manager')->nullable();
            $table->text('plan_developpement_individuel')->nullable();
            $table->boolean('signature_employe')->default(false);
            $table->boolean('signature_manager')->default(false);
            $table->jsonb('fichiersjoin')->nullable();
            $table->jsonb('extra_attributes')->nullable();
            $table->smallInteger('statut')->default(0); // 0=brouillon, 1=soumis_employe, 2=valide_employe, 3=cloture
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'periode']);
            $table->index('date_evaluation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations_performance');
    }
};
