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
        Schema::create('profils', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('introduction')->nullable();
            $table->text('description')->nullable();
            $table->text('nombres')->nullable()->comment('nombre de postes');
            $table->foreignId('recrutement_id')->constrained('recrutements')->cascadeOnDelete();
            $table->text('fichiersjoin')->nullable();
            $table->integer('statut')->default(1);
            $table->json('extra_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('postulants', function (Blueprint $table) {
            $table->id();
            $table->string('noms');
            $table->string('prenoms');
            $table->string('age');
            $table->string('date_naissance');
            $table->string('email');
            $table->string('contact')->nullable();
            $table->foreignId('profil_id')->constrained('profils');
            $table->foreignId('recrutement_id')->nullable()->constrained('recrutements')->nullOnDelete();
            $table->integer('statut')->default(0)->comment('0=nouveau, 1=entretenu, 2=retenu, 3=rejete');
            $table->text('fichiersjoin')->nullable()->comment('CV, lettres');
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
        Schema::dropIfExists('postulants');
        Schema::dropIfExists('profils');
    }
};
