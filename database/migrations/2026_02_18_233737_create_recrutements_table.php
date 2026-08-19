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
        Schema::create('recrutements', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('introduction')->nullable();
            $table->text('description')->nullable();
            $table->datetime('debut')->nullable();
            $table->datetime('fin')->nullable();
            $table->integer('statut')->default(0)->comment('0=ouvert, 1=ferme, 2=pourvu');
            $table->text('fichiersjoin')->nullable();
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
        Schema::dropIfExists('recrutements');
    }
};
