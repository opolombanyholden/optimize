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
        Schema::create('immobilisations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->string('categorie')->nullable();
            $table->string('localisation')->nullable();
            $table->date('date_acquisition')->nullable();
            $table->double('valeur_acquisition', 12, 2)->default(0);
            $table->double('valeur_nette_comptable', 12, 2)->default(0);
            $table->integer('duree_amortissement')->nullable()->comment('en mois');
            $table->string('methode_amortissement')->nullable()->comment('lineaire, degressif');
            $table->string('etat')->nullable()->comment('neuf, bon, usage, hors_service');
            $table->foreignId('affecte_a')->nullable()->constrained('employees');
            $table->foreignId('entite_id')->nullable()->constrained('entites');
            $table->text('fichiersjoin')->nullable();
            $table->integer('statut')->default(1);
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
        Schema::dropIfExists('immobilisations');
    }
};
