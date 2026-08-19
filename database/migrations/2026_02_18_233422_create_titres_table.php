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
        Schema::create('titres', function (Blueprint $table) {
            $table->id();
            $table->string('imputation');
            $table->string('libelle');
            $table->double('seuil')->nullable();
            $table->string('type_ligne')->comment('depense, recette');
            $table->mediumText('description')->nullable();
            $table->tinyInteger('effacer')->default(0);
            $table->integer('id_user')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titres');
    }
};
