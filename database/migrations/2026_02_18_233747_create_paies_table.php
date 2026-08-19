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
        Schema::create('gpaies', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('introduction')->nullable();
            $table->text('description')->nullable();
            $table->datetime('debut')->nullable();
            $table->datetime('fin')->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->double('salaire_base', 10, 2)->default(0);
            $table->double('primes', 10, 2)->default(0);
            $table->double('indemnites', 10, 2)->default(0);
            $table->double('heures_sup', 10, 2)->default(0);
            $table->double('brut', 10, 2)->default(0);
            $table->double('cotisations_salariales', 10, 2)->default(0);
            $table->double('cotisations_patronales', 10, 2)->default(0);
            $table->double('irpp', 10, 2)->default(0);
            $table->double('net_imposable', 10, 2)->default(0);
            $table->double('net_a_payer', 10, 2)->default(0);
            $table->double('avances', 10, 2)->default(0);
            $table->double('retenues', 10, 2)->default(0);
            $table->text('fichiersjoin')->nullable();
            $table->integer('statut')->default(0)->comment('0=brouillon, 1=valide, 2=paye');
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
        Schema::dropIfExists('gpaies');
    }
};
