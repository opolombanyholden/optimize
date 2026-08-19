<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiel des rubriques d'opérations financières + lignes de détail.
 *
 * - rubriques_operations : référentiel attaché à une Ligne (code analytique).
 *   Si Ligne X est rattachée à une BudgetLigne d'un exercice, alors les rubriques
 *   de la Ligne X sont disponibles lors d'une opération sur cette BudgetLigne.
 *
 * - operation_financiere_details : détail d'une opération (rubrique + montant + quantité).
 *   Σ montants détails == opération.montant (validation côté service).
 */
return new class extends Migration {
    public function up(): void
    {
        // RÉFÉRENTIEL — Rubriques liées à une Ligne (code analytique)
        Schema::create('rubriques_operations', function (Blueprint $t) {
            $t->id();
            $t->string('code', 50)->unique();
            $t->string('libelle', 255);
            $t->text('description')->nullable();
            // ligne_id pointe vers le code analytique référentiel (table `lignes`)
            $t->foreignId('ligne_id')->nullable()->constrained('lignes')->nullOnDelete();
            // Sens autorisé : depense | recette | mixte
            $t->string('sens', 20)->default('mixte');
            // Type de rubrique (ex: matériel, services, autre)
            $t->string('categorie', 50)->nullable();
            // Ordre d'affichage
            $t->integer('ordre_affichage')->default(0);
            $t->smallInteger('statut')->default(1); // 1=actif, 0=archivé
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->softDeletes();
            $t->timestamps();

            $t->index(['ligne_id', 'statut']);
            $t->index(['sens', 'statut']);
        });

        // DÉTAILS d'une opération financière (lignes de détail comme une facture)
        Schema::create('operation_financiere_details', function (Blueprint $t) {
            $t->id();
            $t->foreignId('operation_financiere_id')
                ->constrained('operations_financieres')
                ->cascadeOnDelete();
            $t->foreignId('rubrique_id')->nullable()
                ->constrained('rubriques_operations')
                ->nullOnDelete();
            // Libellé libre (peut surcharger celui de la rubrique)
            $t->string('libelle', 500);
            $t->decimal('quantite', 12, 2)->default(1);
            $t->decimal('prix_unitaire', 14, 2)->default(0);
            $t->decimal('montant', 14, 2); // = quantite × prix_unitaire (calculé)
            $t->integer('ordre')->default(0);
            $t->text('observation')->nullable();
            $t->timestamps();

            $t->index('operation_financiere_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_financiere_details');
        Schema::dropIfExists('rubriques_operations');
    }
};
