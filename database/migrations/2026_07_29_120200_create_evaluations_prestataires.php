<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('themes_evaluation', function (Blueprint $t) {
            $t->id();
            $t->string('libelle');
            $t->text('description')->nullable();
            $t->integer('ordre')->default(0);
            $t->boolean('actif')->default(true);
            $t->timestamps();
        });

        Schema::create('criteres_evaluation', function (Blueprint $t) {
            $t->id();
            $t->foreignId('theme_id')->nullable()->constrained('themes_evaluation')->nullOnDelete();
            $t->string('libelle');
            $t->text('description')->nullable();
            $t->integer('ordre')->default(0);
            $t->smallInteger('echelle_min')->default(1);
            $t->smallInteger('echelle_max')->default(5);
            $t->decimal('poids', 5, 2)->default(1.00);
            $t->boolean('actif')->default(true);
            $t->timestamps();
        });

        Schema::create('campagnes_evaluation', function (Blueprint $t) {
            $t->id();
            $t->string('libelle');
            $t->text('description')->nullable();
            $t->date('date_debut');
            $t->date('date_fin')->nullable();
            $t->tinyInteger('statut')->default(0)->comment('0=brouillon 1=en_cours 2=cloturee');
            $t->foreignId('created_by')->nullable()->constrained('users');
            $t->timestamps();
            $t->softDeletes();
        });

        // Prestataires ciblés par une campagne
        Schema::create('campagne_prestataires', function (Blueprint $t) {
            $t->id();
            $t->foreignId('campagne_id')->constrained('campagnes_evaluation')->cascadeOnDelete();
            $t->foreignId('prestataire_id')->constrained('intranet_contact_organisations')->cascadeOnDelete();
            $t->boolean('evaluation_faite')->default(false);
            $t->timestamps();
            $t->unique(['campagne_id', 'prestataire_id'], 'camp_prest_unique');
        });

        Schema::create('evaluations_prestataires', function (Blueprint $t) {
            $t->id();
            $t->foreignId('prestataire_id')->constrained('intranet_contact_organisations')->cascadeOnDelete();
            $t->foreignId('evaluateur_id')->constrained('users');
            $t->string('source', 20)->comment('livraison | campagne | ad_hoc');
            $t->foreignId('commande_fournisseur_id')->nullable()->constrained('commande_fournisseurs')->nullOnDelete();
            $t->foreignId('campagne_id')->nullable()->constrained('campagnes_evaluation')->nullOnDelete();
            $t->date('date_evaluation');
            $t->text('commentaire')->nullable();
            $t->decimal('note_globale', 5, 2)->nullable()->comment('Moyenne pondérée sur 5');
            $t->timestamps();
            $t->softDeletes();
            $t->index(['prestataire_id', 'source']);
        });

        Schema::create('evaluation_notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('evaluation_id')->constrained('evaluations_prestataires')->cascadeOnDelete();
            $t->foreignId('critere_id')->constrained('criteres_evaluation')->cascadeOnDelete();
            $t->decimal('note', 5, 2);
            $t->text('commentaire')->nullable();
            $t->timestamps();
            $t->unique(['evaluation_id', 'critere_id'], 'eval_crit_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_notes');
        Schema::dropIfExists('evaluations_prestataires');
        Schema::dropIfExists('campagne_prestataires');
        Schema::dropIfExists('campagnes_evaluation');
        Schema::dropIfExists('criteres_evaluation');
        Schema::dropIfExists('themes_evaluation');
    }
};
