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
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('raison_sociale');
            $table->string('sigle')->nullable();
            $table->string('nif')->nullable();
            $table->string('rccm')->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('pays')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();
            $table->string('contact_nom')->nullable();
            $table->string('contact_telephone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('rib')->nullable();
            $table->string('banque')->nullable();
            $table->string('domiciliation')->nullable();
            $table->string('categorie')->nullable();
            $table->double('note_evaluation', 3, 1)->nullable();
            $table->text('commentaire')->nullable();
            $table->text('fichiersjoin')->nullable();
            $table->integer('statut')->default(1)->comment('1=actif, 0=inactif');
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
        Schema::dropIfExists('fournisseurs');
    }
};
