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
        Schema::create('typesdysfonctionnements', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->json('extra_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('dysfonctionnements', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('description')->nullable();
            $table->foreignId('type_id')->nullable()->constrained('typesdysfonctionnements');
            $table->foreignId('declarant_id')->nullable()->constrained('users');
            $table->foreignId('immobilisation_id')->nullable()->constrained('immobilisations');
            $table->string('localisation')->nullable();
            $table->string('priorite')->nullable()->comment('basse, normale, haute, critique');
            $table->datetime('date_signalement')->nullable();
            $table->datetime('date_resolution')->nullable();
            $table->text('fichiersjoin')->nullable();
            $table->integer('statut')->default(0)->comment('0=signale, 1=pris_en_charge, 2=resolu, 3=clos');
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
        Schema::dropIfExists('dysfonctionnements');
        Schema::dropIfExists('typesdysfonctionnements');
    }
};
