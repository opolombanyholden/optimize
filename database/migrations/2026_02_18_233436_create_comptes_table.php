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
        Schema::create('comptes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('nom')->nullable();
            $table->string('filename')->nullable();
            $table->tinyInteger('type')->nullable()->comment('1=banque, 2=caisse, 3=tiers');
            $table->integer('entite_id')->nullable();
            $table->double('solde', 8, 2)->default(0.00);
            $table->string('rib')->nullable();
            $table->string('responsable')->nullable();
            $table->string('gestionnaire')->nullable();
            $table->string('contact_gestionnaire')->nullable();
            $table->string('domiciliation')->nullable();
            $table->text('attribut1')->nullable();
            $table->text('attribut2')->nullable();
            $table->text('attribut3')->nullable();
            $table->tinyInteger('effacer')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
