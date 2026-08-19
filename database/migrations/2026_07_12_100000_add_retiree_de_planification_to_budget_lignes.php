<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute `retiree_de_planification` à budget_lignes.
 *
 * Une ligne du référentiel peut être retirée de la planification d'un exercice
 * précis sans être supprimée (pour préserver l'historique + permettre la remise).
 *
 * Fait aussi le cleanup des BudgetLignes créées par erreur avec
 * id_codeanalytique = Ligne::id_codeanalytique (au lieu de Ligne::id).
 * Cf. Exercice::initialiserPlanification() version 2026-07-09.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('budget_lignes', function (Blueprint $t) {
            if (!Schema::hasColumn('budget_lignes', 'retiree_de_planification')) {
                $t->boolean('retiree_de_planification')->default(false)
                    ->comment('Retirée manuellement de la planification par un agent');
                $t->index(['id_exercicebudgetaire', 'retiree_de_planification'], 'idx_bl_ex_retiree');
            }
        });

        // Réparation des BudgetLignes incohérentes (id_codeanalytique = code métier au lieu de PK)
        // Les BudgetLignes créées par initialiserPlanification stockaient à tort le code
        // analytique métier (ex: 1001) au lieu de la PK de Ligne (ex: 1). On rétablit.
        $bl = DB::table('budget_lignes');
        $rows = $bl->whereNotNull('id_codeanalytique')->get(['id', 'id_codeanalytique']);

        foreach ($rows as $row) {
            // Si id_codeanalytique pointe déjà vers une PK de Ligne valide, on ne touche pas.
            $existePk = DB::table('lignes')->where('id', $row->id_codeanalytique)->exists();
            if ($existePk) continue;

            // Sinon, essayer de retrouver la Ligne via son code analytique métier
            $ligne = DB::table('lignes')->where('id_codeanalytique', $row->id_codeanalytique)->first();
            if ($ligne) {
                DB::table('budget_lignes')->where('id', $row->id)->update([
                    'id_codeanalytique' => $ligne->id,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('budget_lignes', function (Blueprint $t) {
            $t->dropIndex('idx_bl_ex_retiree');
            $t->dropColumn('retiree_de_planification');
        });
    }
};
