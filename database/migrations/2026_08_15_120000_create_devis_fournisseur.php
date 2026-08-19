<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devis_fournisseurs', function (Blueprint $t) {
            $t->id();
            $t->string('numero', 30)->unique();
            $t->foreignId('commande_fournisseur_id')->constrained('commande_fournisseurs')->cascadeOnDelete();
            $t->foreignId('fournisseur_id')->nullable()->constrained('intranet_contact_organisations')->nullOnDelete();
            $t->date('date_reception');
            $t->date('date_validite')->nullable();
            $t->integer('delai_livraison_jours')->nullable();
            $t->string('mode_reglement', 50)->nullable();
            $t->decimal('montant_ht', 20, 2)->default(0);
            $t->decimal('montant_tva', 20, 2)->default(0);
            $t->decimal('montant_ttc', 20, 2)->default(0);
            $t->text('conditions')->nullable();
            $t->text('commentaire')->nullable();
            $t->tinyInteger('statut')->default(0)->comment('0=recu 1=selectionne 2=rejete');
            $t->text('motivation')->nullable()->comment('Motivation sélection ou rejet');
            $t->foreignId('decide_par')->nullable()->constrained('users');
            $t->timestamp('decide_at')->nullable();
            $t->foreignId('facture_id')->nullable()->constrained('factures')->nullOnDelete()
              ->comment('Facture reçue suite à la sélection du devis');
            $t->timestamps();
            $t->softDeletes();
            $t->index(['commande_fournisseur_id', 'statut']);
        });

        Schema::create('devis_fournisseur_lignes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('devis_id')->constrained('devis_fournisseurs')->cascadeOnDelete();
            $t->string('designation');
            $t->text('description')->nullable();
            $t->decimal('quantite', 12, 3)->default(1);
            $t->string('unite', 30)->nullable();
            $t->decimal('prix_unitaire', 20, 2)->default(0);
            $t->decimal('montant', 20, 2)->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devis_fournisseur_lignes');
        Schema::dropIfExists('devis_fournisseurs');
    }
};
