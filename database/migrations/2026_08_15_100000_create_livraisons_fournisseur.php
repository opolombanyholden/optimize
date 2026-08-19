<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('livraisons_fournisseur', function (Blueprint $t) {
            $t->id();
            $t->string('numero', 30)->unique();
            $t->foreignId('commande_fournisseur_id')->constrained('commande_fournisseurs')->cascadeOnDelete();
            $t->date('date_livraison');
            $t->foreignId('emplacement_reception_id')->nullable()->constrained('emplacements')->nullOnDelete();
            $t->string('bon_livraison_ref', 100)->nullable()->comment('N° du bon fournisseur');
            $t->string('type', 20)->default('partielle')->comment('partielle | totale');
            $t->text('commentaire')->nullable();
            $t->foreignId('receptionnee_par')->nullable()->constrained('users');
            $t->timestamp('receptionnee_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['commande_fournisseur_id', 'date_livraison']);
        });

        Schema::create('livraison_fournisseur_lignes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('livraison_id')->constrained('livraisons_fournisseur')->cascadeOnDelete();
            $t->foreignId('commande_ligne_id')->constrained('commande_lignes')->cascadeOnDelete();
            $t->foreignId('produit_id')->nullable()->constrained('produits')->nullOnDelete();
            $t->foreignId('emplacement_id')->nullable()->constrained('emplacements')->nullOnDelete()
              ->comment('Override par ligne — sinon emplacement_reception_id de la livraison');
            $t->decimal('quantite', 12, 3);
            $t->text('observation')->nullable();
            $t->timestamps();
            $t->index('livraison_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livraison_fournisseur_lignes');
        Schema::dropIfExists('livraisons_fournisseur');
    }
};
