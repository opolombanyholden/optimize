<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Détail des rubriques appliquées sur un bulletin
        Schema::create('gpaie_rubrique', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paie_id')->constrained('gpaies')->cascadeOnDelete();
            $table->foreignId('rubrique_id')->constrained('rubriques')->cascadeOnDelete();
            $table->decimal('base', 12, 2)->default(0); // base sur laquelle calculer
            $table->decimal('taux', 8, 4)->default(0); // pourcentage appliqué
            $table->decimal('montant', 12, 2); // résultat
            $table->boolean('imposable')->default(false);
            $table->boolean('cotisable')->default(false);
            $table->jsonb('extra_attributes')->nullable();
            $table->timestamps();

            $table->index(['paie_id', 'rubrique_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gpaie_rubrique');
    }
};
