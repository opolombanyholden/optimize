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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('type_abscence', 50)->nullable()->comment('conge_paye, maladie, mission, etc.');
            $table->text('introduction')->nullable();
            $table->text('description')->nullable();
            $table->datetime('debut')->nullable();
            $table->datetime('fin')->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->text('fichiersjoin')->nullable()->comment('justificatifs');
            $table->integer('statut')->default(0)->comment('0=brouillon, 1=soumis, 2=valide, 3=rejete');
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('date_validation')->nullable();
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
        Schema::dropIfExists('absences');
    }
};
