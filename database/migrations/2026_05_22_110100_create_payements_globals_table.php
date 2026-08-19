<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payements_globals', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('annee');
            $table->smallInteger('mois');
            $table->foreignId('exercice_id')->nullable()->constrained('exercices')->nullOnDelete();
            $table->integer('nombre_bulletins')->default(0);
            $table->decimal('masse_salariale_brute', 14, 2)->default(0);
            $table->decimal('masse_salariale_nette', 14, 2)->default(0);
            $table->decimal('total_cotisations_salariales', 14, 2)->default(0);
            $table->decimal('total_cotisations_patronales', 14, 2)->default(0);
            $table->decimal('total_irpp', 14, 2)->default(0);
            $table->decimal('total_avances', 14, 2)->default(0);
            $table->decimal('total_retenues', 14, 2)->default(0);
            $table->decimal('total_paye', 14, 2)->default(0);
            $table->jsonb('agregats_par_departement')->nullable();
            $table->jsonb('extra_attributes')->nullable();
            $table->smallInteger('statut')->default(0); // 0=brouillon, 1=cloture
            $table->foreignId('cloturee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cloturee_at')->nullable();
            $table->timestamps();

            $table->unique(['annee', 'mois']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payements_globals');
    }
};
