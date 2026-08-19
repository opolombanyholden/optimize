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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->string('categorie')->nullable();
            $table->string('sous_categorie')->nullable();
            $table->string('unite_mesure')->nullable();
            $table->double('prix_unitaire', 10, 2)->default(0);
            $table->double('taux_tva', 5, 2)->default(0);
            $table->integer('stock_actuel')->default(0);
            $table->integer('stock_minimum')->default(0);
            $table->integer('stock_maximum')->nullable();
            $table->string('emplacement')->nullable();
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
        Schema::dropIfExists('produits');
    }
};
