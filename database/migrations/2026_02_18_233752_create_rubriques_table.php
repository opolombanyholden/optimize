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
        Schema::create('rubriques', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->string('type')->comment('gain, retenue, cotisation');
            $table->string('base_calcul')->nullable()->comment('fixe, pourcentage, formule');
            $table->double('taux', 8, 4)->nullable();
            $table->double('montant_fixe', 10, 2)->nullable();
            $table->text('formule')->nullable();
            $table->boolean('imposable')->default(true);
            $table->boolean('cotisable')->default(true);
            $table->integer('ordre_affichage')->default(0);
            $table->integer('statut')->default(1);
            $table->json('extra_attributes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubriques');
    }
};
