<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conges_soldes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->smallInteger('annee'); // ex: 2026
            $table->string('type_conge', 50)->default('annuel'); // annuel, rtt, anciennete, exceptionnel
            // Droits annuels - Au Gabon : 2j/mois soit 24j/an + 1j par tranche de 5 ans d'ancienneté
            $table->decimal('droit_annuel', 8, 2)->default(24); // en jours
            $table->decimal('report_n_moins_1', 8, 2)->default(0);
            $table->decimal('acquis_periode', 8, 2)->default(0);
            $table->decimal('pris_periode', 8, 2)->default(0);
            $table->decimal('en_attente', 8, 2)->default(0); // absences soumises non validées
            $table->decimal('solde_disponible', 8, 2)->storedAs('(droit_annuel + report_n_moins_1 + acquis_periode - pris_periode)');
            $table->date('date_debut_acquisition')->nullable();
            $table->date('date_fin_acquisition')->nullable();
            $table->jsonb('extra_attributes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'annee', 'type_conge']);
            $table->index(['employee_id', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conges_soldes');
    }
};
