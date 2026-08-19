<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('declarations_sociales', function (Blueprint $t) {
            $t->id();
            $t->string('code', 60)->unique();
            // cnss | cnamgs | fnh | cfp
            $t->string('type_organisme', 20);
            $t->smallInteger('annee');
            // Mois (1-12) ou n° trimestre (1-4) selon périodicité
            $t->smallInteger('mois')->nullable();
            $t->smallInteger('trimestre')->nullable();
            // mensuelle | trimestrielle
            $t->string('periodicite', 20)->default('mensuelle');
            $t->date('date_debut');
            $t->date('date_fin');
            // Workflow :
            //  0 = brouillon (calculé, modifiable)
            //  1 = validée (verrouillée)
            //  2 = déposée (envoyée à l'organisme)
            $t->smallInteger('statut')->default(0);
            $t->integer('nombre_employes')->default(0);
            $t->decimal('total_brut', 16, 2)->default(0);
            $t->decimal('total_brut_plafonne', 16, 2)->default(0);
            $t->decimal('total_cot_salariale', 16, 2)->default(0);
            $t->decimal('total_cot_patronale', 16, 2)->default(0);
            // Métadonnées employeur snapshot (matricule organisme, raison sociale)
            $t->jsonb('snapshot_employeur')->nullable();
            $t->text('commentaire')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('validee_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('validee_at')->nullable();
            $t->foreignId('deposee_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('deposee_at')->nullable();
            $t->string('reference_depot', 100)->nullable();
            $t->jsonb('extra_attributes')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->index(['type_organisme', 'annee', 'mois']);
            $t->index(['type_organisme', 'annee', 'trimestre']);
        });

        Schema::create('declaration_sociale_lignes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('declaration_sociale_id')->constrained('declarations_sociales')->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            // Snapshot identité (figé au moment de la déclaration)
            $t->string('matricule_employeur', 50)->nullable();
            $t->string('matricule_organisme', 50)->nullable();
            $t->string('nip', 30)->nullable();
            $t->string('noms', 120);
            $t->string('prenoms', 120)->nullable();
            $t->date('date_naissance')->nullable();
            $t->string('sexe', 1)->nullable();
            // Données paie agrégées sur la période
            $t->smallInteger('nb_jours_travailles')->default(30);
            $t->decimal('brut', 14, 2)->default(0);
            $t->decimal('brut_plafonne', 14, 2)->default(0);
            $t->decimal('cot_salariale', 14, 2)->default(0);
            $t->decimal('cot_patronale', 14, 2)->default(0);
            // Liste des bulletins agrégés (paie_ids[])
            $t->jsonb('bulletins_inclus')->nullable();
            $t->timestamps();

            $t->unique(['declaration_sociale_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declaration_sociale_lignes');
        Schema::dropIfExists('declarations_sociales');
    }
};
