<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 — Achats & Approvisionnement :
 *  - Workflow commande : brouillon → soumise → approuvée → livrée / annulée
 *  - Lignes de commande (produits + quantité + prix unitaire)
 *  - Réception : mouvements de stock automatiques à la livraison
 *  - Lien Commande ↔ Facture (une commande peut générer une facture dépense)
 */
return new class extends Migration {
    public function up(): void
    {
        // 1. Workflow sur commande_fournisseurs
        Schema::table('commande_fournisseurs', function (Blueprint $t) {
            foreach ([
                'soumis_par' => 'unsignedBigInteger',
                'soumis_at' => 'timestamp',
                'approuve_par' => 'unsignedBigInteger',
                'approuve_at' => 'timestamp',
                'livre_par' => 'unsignedBigInteger',
                'livre_at' => 'timestamp',
                'annule_par' => 'unsignedBigInteger',
                'annule_at' => 'timestamp',
                'facture_id' => 'unsignedBigInteger',
            ] as $col => $type) {
                if (!Schema::hasColumn('commande_fournisseurs', $col)) {
                    $t->{$type}($col)->nullable();
                }
            }
            if (!Schema::hasColumn('commande_fournisseurs', 'motif_annulation')) {
                $t->text('motif_annulation')->nullable();
            }
            if (!Schema::hasColumn('commande_fournisseurs', 'created_by')) {
                $t->unsignedBigInteger('created_by')->nullable();
            }
        });

        // 2. Lignes de commande (détail articles)
        if (!Schema::hasTable('commande_lignes')) {
            Schema::create('commande_lignes', function (Blueprint $t) {
                $t->id();
                $t->foreignId('commande_id')->constrained('commande_fournisseurs')->cascadeOnDelete();
                $t->foreignId('produit_id')->nullable()->constrained('produits')->nullOnDelete();
                $t->string('designation'); // recopié du produit ou saisie libre
                $t->decimal('quantite_commandee', 12, 3)->default(1);
                $t->decimal('quantite_livree', 12, 3)->default(0);
                $t->decimal('prix_unitaire', 20, 2)->default(0);
                $t->decimal('montant', 20, 2)->default(0)->comment('quantite × prix_unitaire');
                $t->integer('ordre')->default(0);
                $t->text('observation')->nullable();
                $t->timestamps();

                $t->index(['commande_id', 'ordre']);
            });
        }

        // 3. Mouvements de stock (traçabilité entrées/sorties)
        if (!Schema::hasTable('produit_mouvements')) {
            Schema::create('produit_mouvements', function (Blueprint $t) {
                $t->id();
                $t->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
                $t->string('type', 20)->comment('entree | sortie | ajustement');
                $t->decimal('quantite', 12, 3);
                $t->decimal('stock_apres', 12, 3);
                $t->string('reference', 100)->nullable()->comment('N° commande, N° sortie…');
                $t->string('motif')->nullable();
                $t->morphs('source'); // source_type, source_id : CommandeFournisseur ou autre
                $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();

                $t->index(['produit_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('produit_mouvements');
        Schema::dropIfExists('commande_lignes');
        Schema::table('commande_fournisseurs', function (Blueprint $t) {
            $t->dropColumn(['soumis_par','soumis_at','approuve_par','approuve_at','livre_par','livre_at','annule_par','annule_at','facture_id','motif_annulation','created_by']);
        });
    }
};
