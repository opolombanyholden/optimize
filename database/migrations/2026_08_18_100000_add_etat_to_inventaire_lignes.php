<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventaire_lignes', function (Blueprint $t) {
            if (!Schema::hasColumn('inventaire_lignes', 'quantite_bon_etat')) {
                $t->decimal('quantite_bon_etat', 12, 3)->nullable()->after('quantite_reelle')
                    ->comment('Quantité comptée en bon état (utilisée pour livraisons)');
            }
            if (!Schema::hasColumn('inventaire_lignes', 'quantite_mauvais_etat')) {
                $t->decimal('quantite_mauvais_etat', 12, 3)->nullable()->after('quantite_bon_etat')
                    ->comment('Quantité en mauvais état (traçabilité uniquement, hors stock livrable)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventaire_lignes', function (Blueprint $t) {
            foreach (['quantite_bon_etat', 'quantite_mauvais_etat'] as $c) {
                if (Schema::hasColumn('inventaire_lignes', $c)) $t->dropColumn($c);
            }
        });
    }
};
