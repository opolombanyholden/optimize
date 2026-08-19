<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bloc Finance §3 — Gestion des dépenses et recettes.
 *
 * Trois tables :
 *   - clients (parallèle à fournisseurs, pour les recettes)
 *   - factures (polymorphique : fournisseur OU client) avec workflow
 *   - operations_financieres (ordres de dépense / recette) avec workflow
 */
return new class extends Migration {
    public function up(): void
    {
        // ─── CLIENTS ───────────────────────────────────────
        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->string('code', 50)->unique();
            $t->string('raison_sociale', 255);
            $t->string('forme_juridique', 50)->nullable(); // SARL, SA, EI, ONG, etc.
            $t->string('nif', 50)->nullable();
            $t->string('rccm', 50)->nullable();
            $t->string('adresse', 500)->nullable();
            $t->string('ville', 100)->nullable();
            $t->string('pays', 100)->nullable();
            $t->string('telephone', 50)->nullable();
            $t->string('email', 150)->nullable();
            $t->string('site_web', 255)->nullable();
            $t->string('contact_nom', 150)->nullable();
            $t->string('contact_telephone', 50)->nullable();
            $t->string('contact_email', 150)->nullable();
            $t->string('rib', 50)->nullable();
            $t->string('banque', 100)->nullable();
            $t->text('notes')->nullable();
            $t->smallInteger('statut')->default(1); // 1=actif, 0=inactif
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->jsonb('extra_attributes')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->index(['statut']);
        });

        // ─── FACTURES ──────────────────────────────────────
        Schema::create('factures', function (Blueprint $t) {
            $t->id();
            $t->string('numero', 60)->unique();
            // sens : 'depense' (facture fournisseur reçue) ou 'recette' (facture client émise)
            $t->string('sens', 20);
            // Tiers polymorphique : Fournisseur ou Client
            $t->string('tiers_type', 50);    // 'fournisseur' ou 'client'
            $t->unsignedBigInteger('tiers_id');
            // Lien exercice (optionnel mais recommandé)
            $t->foreignId('exercice_id')->nullable()->constrained('exercices')->nullOnDelete();

            $t->date('date_emission');
            $t->date('date_echeance')->nullable();
            $t->string('reference_externe', 100)->nullable(); // num facture côté tiers
            $t->string('objet', 500);

            // Montants
            $t->decimal('montant_ht', 14, 2)->default(0);
            $t->decimal('taux_tva', 5, 2)->default(0);
            $t->decimal('montant_tva', 14, 2)->default(0);
            $t->decimal('montant_ttc', 14, 2)->default(0);
            $t->decimal('montant_regle', 14, 2)->default(0);

            // Workflow : 0=brouillon, 1=validée, 2=partiellement réglée, 3=réglée, 4=annulée
            $t->smallInteger('statut')->default(0);
            $t->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('valide_at')->nullable();
            $t->foreignId('annule_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('annule_at')->nullable();
            $t->text('motif_annulation')->nullable();

            $t->text('commentaire')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->jsonb('extra_attributes')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->index(['tiers_type', 'tiers_id']);
            $t->index(['sens', 'statut']);
            $t->index(['date_emission']);
        });

        // ─── OPÉRATIONS FINANCIÈRES (ordres de dépense / recette) ──
        Schema::create('operations_financieres', function (Blueprint $t) {
            $t->id();
            $t->string('numero', 60)->unique();
            $t->string('type_operation', 20); // 'depense' ou 'recette'

            // Affectation budgétaire (obligatoire pour les dépenses)
            $t->foreignId('exercice_id')->nullable()->constrained('exercices')->nullOnDelete();
            $t->foreignId('budget_ligne_id')->nullable()->constrained('budget_lignes')->nullOnDelete();

            // Tiers polymorphique (optionnel : une opération peut être hors tiers)
            $t->string('tiers_type', 50)->nullable();
            $t->unsignedBigInteger('tiers_id')->nullable();

            // Facture rattachée (optionnel : ordre peut précéder ou suivre la facture)
            $t->foreignId('facture_id')->nullable()->constrained('factures')->nullOnDelete();

            $t->date('date_operation');
            $t->string('objet', 500);
            $t->decimal('montant', 14, 2);
            $t->string('mode_reglement', 30)->nullable(); // virement, cheque, especes, mobile_money
            $t->string('reference_reglement', 100)->nullable();

            // Workflow : 0=brouillon, 1=soumise, 2=approuvée, 3=exécutée, 4=rejetée, 5=annulée
            $t->smallInteger('statut')->default(0);
            $t->foreignId('soumis_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('soumis_at')->nullable();
            $t->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approuve_at')->nullable();
            $t->foreignId('execute_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('execute_at')->nullable();
            $t->text('motif_rejet')->nullable();

            $t->text('commentaire')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->jsonb('extra_attributes')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->index(['type_operation', 'statut']);
            $t->index(['date_operation']);
            $t->index(['tiers_type', 'tiers_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations_financieres');
        Schema::dropIfExists('factures');
        Schema::dropIfExists('clients');
    }
};
