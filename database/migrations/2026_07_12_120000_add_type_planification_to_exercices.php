<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Périmètre de la planification budgétaire d'un exercice.
 *
 * - `depense` : dépenses uniquement (fonctionnement + investissement) — les titres
 *   du référentiel dont `type_ligne = depense` seront matérialisés/affichés.
 * - `recette` : recettes uniquement.
 * - `mixte`   : les deux (défaut).
 *
 * Choisi à la création de l'exercice, il conditionne la matérialisation initiale
 * des lignes ET le filtrage de la grille de planification.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('exercices', function (Blueprint $t) {
            if (!Schema::hasColumn('exercices', 'type_planification')) {
                $t->string('type_planification', 20)->default('mixte')
                    ->comment('depense | recette | mixte');
                $t->index('type_planification');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exercices', function (Blueprint $t) {
            $t->dropIndex(['type_planification']);
            $t->dropColumn('type_planification');
        });
    }
};
