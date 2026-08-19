<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajustements grand_livres :
 *   1. On retire la FK campagne_paie_id (le lien se fait via ref_piece = code campagne).
 *   2. On rend id_entite nullable : les écritures système (paie) n'ont pas d'entité métier.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('grand_livres', function (Blueprint $t) {
            if (Schema::hasColumn('grand_livres', 'campagne_paie_id')) {
                $t->dropConstrainedForeignId('campagne_paie_id');
            }
        });

        // PostgreSQL : suppression de la contrainte NOT NULL sur id_entite
        Schema::table('grand_livres', function (Blueprint $t) {
            $t->unsignedBigInteger('id_entite')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('grand_livres', function (Blueprint $t) {
            $t->foreignId('campagne_paie_id')->nullable()->after('id')
                ->constrained('campagnes_paie')->nullOnDelete();
        });
        // Restaurer NOT NULL est risqué si des lignes sont déjà nulles : on laisse nullable.
    }
};
