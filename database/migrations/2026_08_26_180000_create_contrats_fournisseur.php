<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats_fournisseur', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 60)->unique();
            $table->foreignId('fournisseur_id')->constrained('intranet_contact_organisations')->cascadeOnDelete();
            $table->string('objet');
            $table->text('description')->nullable();
            $table->string('type', 30)->default('prestation'); // achat|prestation|cadre|maintenance|licence|autre
            $table->date('date_signature')->nullable();
            $table->date('date_debut');
            $table->date('date_fin')->nullable(); // null = indéterminée
            $table->decimal('montant_ht', 14, 2)->nullable();
            $table->decimal('montant_ttc', 14, 2)->nullable();
            $table->string('devise', 3)->default('XAF');
            $table->string('statut', 20)->default('brouillon'); // brouillon|actif|expire|resilie|renouvele
            $table->boolean('renouvellement_auto')->default(false);
            $table->integer('preavis_resiliation_jours')->nullable();
            $table->text('conditions')->nullable();
            $table->foreignId('parent_contrat_id')->nullable()->constrained('contrats_fournisseur')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['statut', 'date_fin']);
            $table->index('fournisseur_id');
        });

        Schema::create('contrats_fournisseur_avenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained('contrats_fournisseur')->cascadeOnDelete();
            $table->integer('numero'); // n° d'ordre
            $table->date('date_avenant');
            $table->string('objet');
            $table->text('impact')->nullable(); // description du changement
            $table->decimal('delta_montant_ht', 14, 2)->nullable(); // + ou -
            $table->date('nouvelle_date_fin')->nullable(); // si prorogation
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contrat_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats_fournisseur_avenants');
        Schema::dropIfExists('contrats_fournisseur');
    }
};
