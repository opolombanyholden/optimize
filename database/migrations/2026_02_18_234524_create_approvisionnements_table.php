<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('approvisionnements', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable();
            $table->string('label');
            $table->text('description')->nullable();
            $table->foreignId('fournisseur_id')->nullable()->constrained('fournisseurs');
            $table->foreignId('demandeur_id')->nullable()->constrained('users');
            $table->date('date_demande')->nullable();
            $table->date('date_livraison_souhaitee')->nullable();
            $table->double('montant_total', 12, 2)->default(0);
            $table->string('priorite')->nullable()->comment('basse, normale, haute, urgente');
            $table->integer('statut')->default(0)->comment('0=brouillon, 1=soumis, 2=valide, 3=commande, 4=livre, 5=annule');
            $table->text('fichiersjoin')->nullable();
            $table->json('extra_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('approvisionnementsproduits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approvisionnement_id')->constrained('approvisionnements')->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained('produits');
            $table->integer('quantite_demandee')->default(1);
            $table->integer('quantite_recue')->default(0);
            $table->double('prix_unitaire', 10, 2)->default(0);
            $table->double('montant_ht', 10, 2)->default(0);
            $table->double('montant_tva', 10, 2)->default(0);
            $table->double('montant_ttc', 10, 2)->default(0);
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvisionnementsproduits');
        Schema::dropIfExists('approvisionnements');
    }
};
