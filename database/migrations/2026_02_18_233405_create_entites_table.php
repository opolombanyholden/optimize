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
        Schema::create('entites', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('code')->nullable();
            $table->foreignId('id_localite')->nullable()->constrained('localites');
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
        Schema::dropIfExists('entites');
    }
};
