<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trésorerie & lien Ordre → Compte.
 *
 * 1) Enrichit `comptes` pour supporter la gestion complète de trésorerie :
 *    - devise (XAF par défaut)
 *    - solde_initial (à l'ouverture du compte, référence historique)
 *    - actif (soft désactivation sans supprimer)
 *
 * 2) Ajoute `compte_id` sur `finance_ordres` : l'agent doit désormais choisir
 *    explicitement le compte (bancaire, caisse, électronique) à débiter (dépense)
 *    ou créditer (recette). Le compte est optionnel en brouillon mais requis
 *    au moment de l'exécution au Grand Livre.
 *
 * Le type `4 = électronique` (Mobile Money, e-wallet, PayPal) est une convention
 * ajoutée côté modèle — pas de contrainte DB pour rester compatible avec les
 * données existantes (types 1=banque, 2=caisse, 3=tiers).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('comptes', function (Blueprint $t) {
            if (!Schema::hasColumn('comptes', 'devise')) {
                $t->string('devise', 10)->default('XAF');
            }
            if (!Schema::hasColumn('comptes', 'solde_initial')) {
                $t->double('solde_initial')->default(0);
            }
            if (!Schema::hasColumn('comptes', 'actif')) {
                $t->boolean('actif')->default(true);
            }
        });

        Schema::table('finance_ordres', function (Blueprint $t) {
            if (!Schema::hasColumn('finance_ordres', 'compte_id')) {
                $t->foreignId('compte_id')->nullable()->after('budget_ligne_id')
                    ->constrained('comptes')->nullOnDelete();
                $t->index('compte_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_ordres', function (Blueprint $t) {
            if (Schema::hasColumn('finance_ordres', 'compte_id')) {
                $t->dropForeign(['compte_id']);
                $t->dropColumn('compte_id');
            }
        });
        Schema::table('comptes', function (Blueprint $t) {
            $t->dropColumn(['devise', 'solde_initial', 'actif']);
        });
    }
};
