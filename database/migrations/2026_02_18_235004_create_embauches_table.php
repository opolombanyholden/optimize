<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('embauches', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->text('introduction')->nullable();
            $table->text('description')->nullable();
            $table->boolean('valider')->default(false);
            $table->foreignId('postulant_id')->nullable()->constrained('postulants')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('fichiersjoin')->nullable();
            $table->tinyInteger('statut')->default(0);
            $table->json('extra_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embauches');
    }
};
