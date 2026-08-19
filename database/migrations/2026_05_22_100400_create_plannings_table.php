<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date_jour');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->time('heure_debut_pause')->nullable();
            $table->time('heure_fin_pause')->nullable();
            $table->decimal('heures_prevues', 4, 2)->default(8);
            $table->decimal('heures_reelles', 4, 2)->nullable();
            $table->string('type_journee', 30)->default('travail'); // travail, repos, ferie, conge, absence
            $table->text('lieu')->nullable(); // bureau, teletravail, mission
            $table->text('notes')->nullable();
            $table->jsonb('extra_attributes')->nullable();
            $table->smallInteger('statut')->default(1); // 0=annule, 1=planifie, 2=realise
            $table->timestamps();

            $table->unique(['employee_id', 'date_jour']);
            $table->index('date_jour');
            $table->index(['employee_id', 'type_journee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plannings');
    }
};
