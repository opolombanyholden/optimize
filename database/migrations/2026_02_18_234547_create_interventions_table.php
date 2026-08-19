<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('description')->nullable();
            $table->foreignId('dysfonctionnement_id')->nullable()->constrained('dysfonctionnements');
            $table->foreignId('immobilisation_id')->nullable()->constrained('immobilisations');
            $table->foreignId('technicien_id')->nullable()->constrained('users');
            $table->string('type_intervention')->nullable()->comment('preventive, corrective, ameliorative');
            $table->datetime('date_planifiee')->nullable();
            $table->datetime('date_debut')->nullable();
            $table->datetime('date_fin')->nullable();
            $table->double('cout', 10, 2)->nullable();
            $table->text('rapport')->nullable();
            $table->text('fichiersjoin')->nullable();
            $table->integer('statut')->default(0)->comment('0=planifie, 1=en_cours, 2=termine, 3=annule');
            $table->json('extra_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
