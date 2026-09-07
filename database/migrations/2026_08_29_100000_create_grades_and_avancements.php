<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Grades (référentiel)
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('libelle', 100);
            $table->text('description')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Critères par grade (conditions à remplir)
        Schema::create('grade_criteres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->boolean('obligatoire')->default(true);
            $table->integer('ordre')->default(0);
            $table->timestamps();
            $table->index('grade_id');
        });

        // Historique des avancements (évolutions de grade)
        Schema::create('avancements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained('grades')->restrictOnDelete();
            $table->foreignId('grade_precedent_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->date('date_effet');
            $table->string('motif', 255)->nullable();
            $table->string('reference_document', 100)->nullable();
            $table->foreignId('decide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->text('commentaire')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'date_effet']);
        });

        // Grade actuel dénormalisé sur employees (perf des listings et filtres)
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
            $table->dropColumn('grade_id');
        });
        Schema::dropIfExists('avancements');
        Schema::dropIfExists('grade_criteres');
        Schema::dropIfExists('grades');
    }
};
