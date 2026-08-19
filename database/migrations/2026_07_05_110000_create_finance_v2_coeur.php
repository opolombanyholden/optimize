<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refonte Finance V2 — Cœur (Étape 2).
 *
 *   budgets              — 1 budget par (ligne × exercice), avec statut/seuil
 *   budget_sources       — répartition du montant par source de financement
 *   transactions_v2      — dépenses et recettes (remplace OperationFinanciere + Facture)
 *   transaction_details  — lignes de détail d'une transaction (rubriques/qté/montant)
 *   modifs               — transferts entre BudgetSource
 *
 * Convention "v2" pour éviter les collisions avec tables existantes qu'on gardera pour
 * migration de données. Les modèles utilisent le nom court (Budget, Transaction, Modif).
 */
return new class extends Migration {

    public function up(): void
    {
        // ─── BUDGETS ─────────────────────────────────────────
        Schema::create('budgets', function (Blueprint $t) {
            $t->id();
            $t->string('label');
            $t->text('description')->nullable();
            $t->decimal('seuil', 14, 2)->nullable();
            $t->smallInteger('status')->default(0)
                ->comment('0=brouillon, 1=validé, 2=verrouillé');
            $t->foreignId('ligne_id')->constrained('lignes')->cascadeOnDelete();
            $t->foreignId('exercice_id')->constrained('exercices')->cascadeOnDelete();
            $t->json('extra')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->unique(['ligne_id', 'exercice_id']);
            $t->index('status');
        });

        // ─── BUDGET_SOURCES ─────────────────────────────────
        Schema::create('budget_sources', function (Blueprint $t) {
            $t->id();
            $t->string('label')->nullable();
            $t->text('description')->nullable();
            $t->decimal('montant', 14, 2)->default(0);
            $t->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $t->foreignId('ligne_id')->constrained('lignes')->cascadeOnDelete();
            $t->foreignId('exercice_id')->constrained('exercices')->cascadeOnDelete();
            $t->json('extra')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->unique(['source_id', 'ligne_id', 'exercice_id']);
            $t->index(['ligne_id', 'exercice_id']);
        });

        // ─── TRANSACTIONS ─────────────────────────────────────
        Schema::create('transactions_v2', function (Blueprint $t) {
            $t->id();
            $t->smallInteger('type')->default(0)
                ->comment('0=dépense, 1=recette');
            $t->smallInteger('status')->default(0)
                ->comment('0=brouillon, 1=soumise, 2=validée, 3=payée/encaissée, 4=annulée');
            $t->decimal('montant', 14, 2);
            $t->decimal('montant_restant', 14, 2)->default(0);
            $t->string('montant_lettres', 500)->nullable();
            $t->date('date');
            $t->string('devise', 10)->default('XAF');
            $t->string('beneficiaire')->nullable();
            $t->boolean('beneficiaire_externe')->default(true);
            $t->string('code')->nullable();       // n° pièce
            $t->string('label')->nullable();       // objet
            $t->text('description')->nullable();
            $t->boolean('isvalide')->nullable();
            $t->foreignId('entite_id')->nullable()->constrained('entites')->nullOnDelete();
            $t->foreignId('compte_id')->nullable()->constrained('comptes')->nullOnDelete();
            $t->foreignId('ligne_id')->nullable()->constrained('lignes')->nullOnDelete();
            $t->foreignId('exercice_id')->nullable()->constrained('exercices')->nullOnDelete();
            $t->foreignId('mode_reglement_id')->nullable()->constrained('mode_reglements')->nullOnDelete();
            $t->foreignId('id_user')->nullable()->constrained('users')->nullOnDelete();
            $t->string('file')->nullable();
            $t->string('file_comment')->nullable();
            $t->string('filepath')->nullable();
            $t->json('extra')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->index(['type', 'status']);
            $t->index('exercice_id');
            $t->index('date');
        });

        // ─── TRANSACTION_DETAILS ───────────────────────────
        Schema::create('transaction_details', function (Blueprint $t) {
            $t->id();
            $t->decimal('montant', 14, 2);
            $t->string('montant_lettres', 500)->nullable();
            $t->string('label')->nullable();
            $t->integer('quantity')->default(1);
            $t->foreignId('transaction_id')->nullable()->constrained('transactions_v2')->cascadeOnDelete();
            $t->foreignId('ligne_id')->nullable()->constrained('lignes')->nullOnDelete();
            $t->json('extra')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        // ─── MODIFS (transferts entre BudgetSource) ────────
        Schema::create('modifs', function (Blueprint $t) {
            $t->id();
            $t->string('comment')->nullable();
            $t->date('date')->nullable();
            $t->decimal('montant', 14, 2)->default(0);
            $t->foreignId('budget_emission_id')->nullable()->constrained('budget_sources')->nullOnDelete();
            $t->foreignId('budget_reception_id')->nullable()->constrained('budget_sources')->nullOnDelete();
            $t->smallInteger('status')->default(0)
                ->comment('0=brouillon, 1=soumise, 2=approuvée, 3=appliquée, 4=rejetée');
            $t->foreignId('soumis_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('soumis_at')->nullable();
            $t->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approuve_at')->nullable();
            $t->foreignId('applique_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('applique_at')->nullable();
            $t->text('motif_rejet')->nullable();
            $t->json('extra')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modifs');
        Schema::dropIfExists('transaction_details');
        Schema::dropIfExists('transactions_v2');
        Schema::dropIfExists('budget_sources');
        Schema::dropIfExists('budgets');
    }
};
