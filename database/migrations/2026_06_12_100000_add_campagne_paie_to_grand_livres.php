<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lien optionnel des écritures comptables vers la campagne de paie qui les a générées.
 * Permet de retrouver toutes les écritures issues d'une campagne (filtrage, audit, dé-validation).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('grand_livres', function (Blueprint $t) {
            $t->foreignId('campagne_paie_id')->nullable()
                ->after('id')
                ->constrained('campagnes_paie')
                ->nullOnDelete();
            $t->index('campagne_paie_id');
        });
    }

    public function down(): void
    {
        Schema::table('grand_livres', function (Blueprint $t) {
            $t->dropConstrainedForeignId('campagne_paie_id');
        });
    }
};
