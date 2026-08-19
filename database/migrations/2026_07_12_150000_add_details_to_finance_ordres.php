<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Détails ventilés d'un ordre de dépense / recette.
 *
 * Un ordre s'impute désormais sur une LIGNE BUDGÉTAIRE précise, et peut
 * comporter plusieurs lignes de détail — chacune référençant une RUBRIQUE
 * de la table `rubriques_operations` (déjà existante et paramétrable par
 * les admins via /finance/rubriques).
 *
 * Le paramétrage des désignations disponibles pour une ligne budgétaire
 * se fait donc dans le référentiel existant : chaque RubriqueOperation
 * est liée à une `ligne_id` + un `sens` (depense/recette/mixte).
 */
return new class extends Migration {
    public function up(): void
    {
        // 1) L'ordre s'impute sur une ligne budgétaire (pour filtrer les rubriques dispos)
        Schema::table('finance_ordres', function (Blueprint $t) {
            if (!Schema::hasColumn('finance_ordres', 'budget_ligne_id')) {
                $t->foreignId('budget_ligne_id')->nullable()->after('exercice_id')
                    ->constrained('budget_lignes')->nullOnDelete();
                $t->index('budget_ligne_id');
            }
        });

        // 2) Table des détails (lignes de la facture / ventilation)
        Schema::create('finance_ordre_details', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ordre_id')->constrained('finance_ordres')->cascadeOnDelete();
            $t->foreignId('rubrique_id')->nullable()->constrained('rubriques_operations')->nullOnDelete()
                ->comment('Désignation choisie parmi le référentiel — filtré par ligne budgétaire + sens');
            $t->string('libelle')->comment('Libellé effectif (recopie de la rubrique ou saisie libre)');
            $t->decimal('quantite',      12, 2)->default(1);
            $t->decimal('prix_unitaire', 20, 2)->default(0);
            $t->decimal('montant',       20, 2)->default(0)->comment('quantite × prix_unitaire');
            $t->text('observation')->nullable();
            $t->integer('ordre')->default(0);
            $t->timestamps();

            $t->index(['ordre_id', 'ordre'], 'idx_ordredet_ordre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_ordre_details');
        Schema::table('finance_ordres', function (Blueprint $t) {
            if (Schema::hasColumn('finance_ordres', 'budget_ligne_id')) {
                $t->dropForeign(['budget_ligne_id']);
                $t->dropColumn('budget_ligne_id');
            }
        });
    }
};
