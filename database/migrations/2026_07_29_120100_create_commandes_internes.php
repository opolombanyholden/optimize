<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commandes_internes', function (Blueprint $t) {
            $t->id();
            $t->string('numero', 30)->unique();
            $t->string('objet');
            $t->text('justification')->nullable();
            $t->foreignId('demandeur_id')->constrained('users');
            $t->foreignId('superieur_id')->nullable()->constrained('users')->comment('N+1 valideur');
            $t->foreignId('valide_par_appro_id')->nullable()->constrained('users');
            $t->foreignId('commande_fournisseur_id')->nullable()->constrained('commande_fournisseurs')->nullOnDelete()
              ->comment('Commande externe générée pour honorer la demande');

            $t->tinyInteger('statut')->default(0)
              ->comment('0=brouillon 1=en_attente_n1 2=validee_n1 3=refusee_n1 4=transmise_appro 5=livree_partielle 6=livree 7=annulee');

            $t->date('date_demande');
            $t->date('date_besoin')->nullable();
            $t->timestamp('soumise_n1_at')->nullable();
            $t->timestamp('validee_n1_at')->nullable();
            $t->timestamp('transmise_appro_at')->nullable();
            $t->timestamp('livree_at')->nullable();
            $t->timestamp('annulee_at')->nullable();

            $t->text('motif_refus_n1')->nullable();
            $t->text('motif_annulation')->nullable();
            $t->text('commentaire_appro')->nullable();

            $t->decimal('montant_estime', 20, 2)->default(0);

            $t->timestamps();
            $t->softDeletes();

            $t->index(['statut', 'demandeur_id']);
        });

        Schema::create('commande_interne_lignes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('commande_interne_id')->constrained('commandes_internes')->cascadeOnDelete();
            $t->foreignId('produit_id')->nullable()->constrained('produits')->nullOnDelete();
            $t->string('designation');
            $t->decimal('quantite_demandee', 12, 3);
            $t->decimal('quantite_livree', 12, 3)->default(0);
            $t->string('unite', 30)->nullable();
            $t->decimal('prix_unitaire_estime', 20, 2)->nullable();
            $t->text('commentaire')->nullable();
            $t->timestamps();
        });

        // Livraisons internes (historique des remises au demandeur)
        Schema::create('livraisons_internes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('commande_interne_id')->constrained('commandes_internes')->cascadeOnDelete();
            $t->date('date_livraison');
            $t->foreignId('livre_par')->nullable()->constrained('users');
            $t->foreignId('recu_par')->nullable()->constrained('users');
            $t->string('type', 20)->default('partielle')->comment('partielle | complete');
            $t->text('commentaire')->nullable();
            $t->timestamps();
        });

        Schema::create('livraison_interne_lignes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('livraison_id')->constrained('livraisons_internes')->cascadeOnDelete();
            $t->foreignId('commande_ligne_id')->constrained('commande_interne_lignes')->cascadeOnDelete();
            $t->decimal('quantite', 12, 3);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livraison_interne_lignes');
        Schema::dropIfExists('livraisons_internes');
        Schema::dropIfExists('commande_interne_lignes');
        Schema::dropIfExists('commandes_internes');
    }
};
