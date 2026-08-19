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
        Schema::create('commande_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('numero_commande')->nullable()->unique();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs');
            $table->foreignId('approvisionnement_id')->nullable()->constrained('approvisionnements');
            $table->date('date_commande')->nullable();
            $table->date('date_livraison_prevue')->nullable();
            $table->date('date_livraison_effective')->nullable();
            $table->double('montant_ht', 12, 2)->default(0);
            $table->double('montant_tva', 12, 2)->default(0);
            $table->double('montant_ttc', 12, 2)->default(0);
            $table->string('mode_reglement')->nullable();
            $table->text('conditions')->nullable();
            $table->text('commentaire')->nullable();
            $table->foreignId('valideur_id')->nullable()->constrained('users');
            $table->text('fichiersjoin')->nullable();
            $table->integer('statut')->default(0)->comment('0=brouillon, 1=soumis, 2=valide, 3=envoye, 4=livre_partiellement, 5=livre, 6=annule');
            $table->json('extra_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_fournisseurs');
    }
};
