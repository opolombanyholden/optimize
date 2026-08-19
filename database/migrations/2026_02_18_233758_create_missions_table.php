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
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('introduction')->nullable();
            $table->text('description')->nullable();
            $table->string('lieu')->nullable();
            $table->datetime('debut')->nullable();
            $table->datetime('fin')->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->double('budget', 10, 2)->nullable();
            $table->double('frais_reels', 10, 2)->nullable();
            $table->text('fichiersjoin')->nullable();
            $table->integer('statut')->default(0)->comment('0=planifie, 1=en cours, 2=termine');
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
        Schema::dropIfExists('missions');
    }
};
