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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('noms');
            $table->string('prenoms');
            $table->string('matricule')->nullable()->unique();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('nationalite')->nullable();
            $table->char('sexe', 1)->nullable();
            $table->string('situation_matrimoniale')->nullable();
            $table->integer('nombre_enfants')->default(0);
            $table->string('email')->nullable();
            $table->string('contact')->nullable();
            $table->text('adresse')->nullable();
            $table->foreignId('id_quartier')->nullable();
            $table->foreignId('id_ville')->nullable();
            $table->foreignId('id_pays')->nullable();
            $table->date('date_embauche')->nullable();
            $table->string('type_contrat')->nullable();
            $table->date('date_fin_contrat')->nullable();
            $table->string('poste')->nullable();
            $table->string('departement')->nullable();
            $table->foreignId('superieur_hierarchique')->nullable()->constrained('employees')->nullOnDelete();
            $table->double('salaire_base', 8, 2)->default(0.00);
            $table->string('iban')->nullable();
            $table->string('numero_secu')->nullable();
            $table->integer('statut')->default(1)->comment('1=actif, 2=inactif, 3=parti');
            $table->text('fichiersjoin')->nullable()->comment('CV, pieces jointes');
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
        Schema::dropIfExists('employees');
    }
};
