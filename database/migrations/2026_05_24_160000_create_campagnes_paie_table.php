<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campagnes_paie', function (Blueprint $t) {
            $t->id();
            $t->string('code', 50)->unique(); // ex: CP-2026-05 (campagne paie mai 2026)
            $t->string('libelle', 255);
            $t->smallInteger('annee');
            $t->smallInteger('mois');
            $t->date('date_debut');
            $t->date('date_fin');
            $t->date('date_paiement_prevue')->nullable();
            // Périodicité (mensuelle par défaut, mais peut être quinzaine, hebdo, prime exceptionnelle…)
            $t->string('periodicite', 30)->default('mensuelle');
            // Mode : simulation (test, n'affecte pas la masse salariale) ou reelle
            $t->boolean('simulation')->default(false);
            // Workflow statut :
            //  0 = brouillon (pas encore générée)
            //  1 = générée (bulletins créés en brouillon)
            //  2 = validée (bulletins validés, prêts au paiement)
            //  3 = payée (paiements enregistrés)
            //  4 = clôturée (verrouillée, plus de modification)
            $t->smallInteger('statut')->default(0);
            $t->integer('nombre_bulletins')->default(0);
            $t->decimal('masse_brute', 14, 2)->default(0);
            $t->decimal('masse_nette', 14, 2)->default(0);
            $t->decimal('total_cotisations_sal', 14, 2)->default(0);
            $t->decimal('total_cotisations_pat', 14, 2)->default(0);
            $t->text('commentaire')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('validee_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('validee_at')->nullable();
            $t->foreignId('cloturee_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('cloturee_at')->nullable();
            $t->jsonb('extra_attributes')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->unique(['annee', 'mois', 'periodicite', 'simulation']);
            $t->index(['statut', 'annee', 'mois']);
        });

        // Pivot : employés sélectionnés pour cette campagne (échantillon)
        Schema::create('campagne_paie_employe', function (Blueprint $t) {
            $t->id();
            $t->foreignId('campagne_paie_id')->constrained('campagnes_paie')->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            // Override possibles à appliquer pour ce bulletin spécifique (primes/avances/heures sup ponctuelles)
            $t->decimal('primes_override', 12, 2)->nullable();
            $t->decimal('indemnites_override', 12, 2)->nullable();
            $t->decimal('heures_sup_override', 12, 2)->nullable();
            $t->decimal('avances_override', 12, 2)->nullable();
            $t->decimal('retenues_override', 12, 2)->nullable();
            $t->text('notes')->nullable();
            // Statut individuel de l'employé dans la campagne :
            //  0 = sélectionné mais non généré
            //  1 = bulletin généré
            //  2 = exclu manuellement
            $t->smallInteger('statut')->default(0);
            $t->timestamps();

            $t->unique(['campagne_paie_id', 'employee_id']);
        });

        // Ajout campagne_id à gpaies
        Schema::table('gpaies', function (Blueprint $t) {
            $t->foreignId('campagne_paie_id')->nullable()->constrained('campagnes_paie')->nullOnDelete();
            $t->index('campagne_paie_id');
        });
    }

    public function down(): void
    {
        Schema::table('gpaies', function (Blueprint $t) {
            $t->dropConstrainedForeignId('campagne_paie_id');
        });
        Schema::dropIfExists('campagne_paie_employe');
        Schema::dropIfExists('campagnes_paie');
    }
};
