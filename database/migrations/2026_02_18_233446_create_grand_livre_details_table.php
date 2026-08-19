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
        Schema::create('grand_livre_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_facture')->nullable();
            $table->foreignId('id_produit')->nullable();
            $table->double('prix')->nullable();
            $table->double('quantite')->nullable();
            $table->double('total_ht')->nullable();
            $table->double('total_taxe')->nullable();
            $table->double('total_reduction')->nullable();
            $table->double('total_ttc')->nullable();
            $table->string('attribut1')->nullable();
            $table->string('attribut2')->nullable();
            $table->string('attribut3')->nullable();
            $table->tinyInteger('effacer')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grand_livre_details');
    }
};
