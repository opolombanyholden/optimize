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
        Schema::create('pays', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->nullable();
            $table->string('zipcode', 45)->nullable();
            $table->string('alpha2', 2);
            $table->string('alpha3', 3)->nullable();
            $table->string('nom_en_gb');
            $table->string('nom_fr_fr', 225);
            $table->integer('isvalide')->default(1);
            $table->integer('statut')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pays');
    }
};
