<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gestion de projet — fonctionnalités PMP optionnelles.
 *
 * Hiérarchie : Projet → Phase (WBS) → Tâche → Activité
 *
 * Domaines couverts (PMBOK) :
 *  1. Intégration     → Charte projet, Leçons apprises, Changements
 *  2. Périmètre       → Phases WBS, Livrables
 *  3. Délais          → Jalons, Dépendances, Activités
 *  4. Coûts           → Budget, Lignes de coût, EVM
 *  5. Qualité         → Checklists
 *  6. Ressources      → Affectations, Feuilles de temps
 *  7. Communication   → Rapports (via module Rapports)
 *  8. Risques         → Registre des risques
 *  9. Approvisionnements → (module Appro ERP)
 * 10. Parties prenantes → Registre stakeholders
 */
return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════
        // 1. PHASES / WBS
        //    Décomposition du projet en phases ou lots de travaux
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_projet_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('code_wbs', 30)->nullable();   // ex: 1.2.3
            $table->integer('ordre')->default(0);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->unsignedTinyInteger('avancement')->default(0); // 0-100%
            $table->string('couleur', 20)->default('#4F46E5');
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Ajoute phase_id sur les tâches maintenant que la table existe
        Schema::table('intranet_taches', function (Blueprint $table) {
            $table->foreignId('phase_id')->nullable()->after('projet_id')
                  ->constrained('intranet_projet_phases')->nullOnDelete();
        });

        // ══════════════════════════════════════════════════════
        // 2. ACTIVITÉS
        //    3e niveau de la hiérarchie : Projet > Tâche > Activité
        //    Unité élémentaire de travail, estimable et assignable
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('intranet_taches')->cascadeOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->foreignId('statut_id')->nullable()->constrained('intranet_statuts')->nullOnDelete();
            $table->foreignId('priorite_id')->nullable()->constrained('intranet_priorites')->nullOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('duree_estimee', 6, 2)->nullable(); // heures
            $table->decimal('duree_reelle', 6, 2)->nullable();  // heures
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->unsignedTinyInteger('avancement')->default(0); // 0-100%
            $table->integer('ordre')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════════
        // 3. JALONS
        //    Points de contrôle clés, durée zéro
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_jalons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('intranet_projet_phases')->nullOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->date('date_prevue');
            $table->date('date_reelle')->nullable();
            $table->enum('statut', ['prevu','atteint','manque','reporte'])->default('prevu');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 4. DÉPENDANCES (optionnel)
        //    Relations entre tâches ou activités (FS, SS, FF, SF)
        //    Pour la construction du chemin critique (CPM)
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_dependances', function (Blueprint $table) {
            $table->id();
            // Source : tâche ou activité qui doit se terminer/démarrer
            $table->string('source_type');        // App\Models\Intranet\Tache | Activite
            $table->unsignedBigInteger('source_id');
            // Cible : tâche ou activité dépendante
            $table->string('cible_type');
            $table->unsignedBigInteger('cible_id');
            // Type de dépendance PMP
            $table->enum('type', ['FS','SS','FF','SF'])->default('FS');
            //  FS = Finish-to-Start  (le + courant)
            //  SS = Start-to-Start
            //  FF = Finish-to-Finish
            //  SF = Start-to-Finish
            $table->smallInteger('lag_jours')->default(0); // décalage (négatif = avance)
            $table->index(['source_type','source_id']);
            $table->index(['cible_type','cible_id']);
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 5. RESSOURCES PROJET (optionnel)
        //    Affectation des ressources humaines au projet
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_projet_ressources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role_projet', ['chef','co_chef','membre','expert','observateur','sponsor'])
                  ->default('membre');
            $table->decimal('heures_allouees', 8, 2)->nullable();
            $table->decimal('heures_reelles', 8, 2)->default(0);
            $table->decimal('taux_journalier', 10, 2)->nullable(); // coût/jour
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
            $table->unique(['projet_id','user_id']);
        });

        // ══════════════════════════════════════════════════════
        // 6. FEUILLES DE TEMPS (optionnel)
        //    Time-tracking par projet / tâche / activité
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_feuilles_temps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->foreignId('tache_id')->nullable()->constrained('intranet_taches')->nullOnDelete();
            $table->foreignId('activite_id')->nullable()->constrained('intranet_activites')->nullOnDelete();
            $table->date('date');
            $table->decimal('heures', 5, 2);
            $table->text('description')->nullable();
            $table->enum('statut', ['brouillon','soumis','approuve','rejete'])->default('brouillon');
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approuve_le')->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 7. BUDGET & LIGNES DE COÛT (optionnel)
        //    Budget initial, baseline, coûts réels — base EVM
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_projet_couts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->foreignId('tache_id')->nullable()->constrained('intranet_taches')->nullOnDelete();
            $table->foreignId('activite_id')->nullable()->constrained('intranet_activites')->nullOnDelete();
            $table->string('libelle');
            $table->enum('categorie', ['main_oeuvre','materiel','service','deplacement','formation','autre'])
                  ->default('autre');
            $table->decimal('montant_estime', 15, 2)->default(0);
            $table->decimal('montant_reel', 15, 2)->default(0);
            $table->date('date_cout')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 8. MÉTRIQUES EVM — Earned Value Management (optionnel)
        //    Snapshots périodiques pour mesurer la performance
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_projet_evm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->date('date_mesure');
            $table->decimal('bac', 15, 2)->nullable();   // Budget At Completion
            $table->decimal('pv', 15, 2)->nullable();    // Planned Value
            $table->decimal('ev', 15, 2)->nullable();    // Earned Value
            $table->decimal('ac', 15, 2)->nullable();    // Actual Cost
            // Dérivés (calculables mais stockés pour historique)
            $table->decimal('sv', 15, 2)->nullable();    // Schedule Variance = EV - PV
            $table->decimal('cv', 15, 2)->nullable();    // Cost Variance = EV - AC
            $table->decimal('spi', 8, 4)->nullable();    // Schedule Performance Index = EV/PV
            $table->decimal('cpi', 8, 4)->nullable();    // Cost Performance Index = EV/AC
            $table->decimal('etc', 15, 2)->nullable();   // Estimate To Complete
            $table->decimal('eac', 15, 2)->nullable();   // Estimate At Completion
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 9. REGISTRE DES RISQUES (optionnel)
        //    Identification, analyse et réponse aux risques
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_projet_risques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->string('titre');
            $table->text('description');
            $table->string('categorie')->nullable(); // technique, externe, organisationnel, gestion
            $table->unsignedTinyInteger('probabilite')->default(3); // 1 (rare) → 5 (quasi-certain)
            $table->unsignedTinyInteger('impact')->default(3);      // 1 (insignifiant) → 5 (critique)
            // score = probabilite × impact (1-25)
            $table->unsignedTinyInteger('score')->storedAs('probabilite * impact');
            $table->enum('type_risque', ['menace','opportunite'])->default('menace');
            $table->enum('strategie', ['eviter','transferer','attenuer','accepter','exploiter','partager','ameliorer'])
                  ->nullable();
            $table->text('plan_reponse')->nullable();
            $table->text('plan_contingence')->nullable();
            $table->decimal('cout_contingence', 15, 2)->nullable();
            $table->enum('statut', ['identifie','analyse','traite','surveille','clos'])->default('identifie');
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_identification')->nullable();
            $table->date('date_revue')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 10. JOURNAL DES PROBLÈMES / ISSUES (optionnel)
        //     Suivi des obstacles et incidents en cours de projet
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_projet_problemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->string('titre');
            $table->text('description');
            $table->string('categorie')->nullable();
            $table->foreignId('priorite_id')->nullable()->constrained('intranet_priorites')->nullOnDelete();
            $table->enum('statut', ['ouvert','en_cours','resolu','clos'])->default('ouvert');
            $table->text('impact')->nullable();
            $table->text('resolution')->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('risque_id')->nullable()->constrained('intranet_projet_risques')->nullOnDelete();
            $table->date('date_identification');
            $table->date('date_resolution')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 11. DEMANDES DE CHANGEMENT (optionnel)
        //     Gestion formelle des modifications de périmètre/délai/coût
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_projet_changements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->string('titre');
            $table->text('description');
            $table->enum('type', ['perimetre','delai','cout','qualite','ressource','autre'])->default('autre');
            $table->text('justification')->nullable();
            $table->decimal('impact_cout', 15, 2)->nullable();   // + ou -
            $table->integer('impact_delai_jours')->nullable();    // + ou -
            $table->text('impact_qualite')->nullable();
            $table->text('impact_risques')->nullable();
            $table->enum('statut', ['soumis','en_analyse','approuve','rejete','implemente','annule'])
                  ->default('soumis');
            $table->foreignId('demandeur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_soumission');
            $table->date('date_decision')->nullable();
            $table->text('decision_commentaire')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 12. LIVRABLES (optionnel)
        //     Résultats mesurables produits par le projet
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_projet_livrables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('intranet_projet_phases')->nullOnDelete();
            $table->foreignId('tache_id')->nullable()->constrained('intranet_taches')->nullOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->text('criteres_acceptation')->nullable();
            $table->enum('statut', ['planifie','en_cours','livre','accepte','rejete'])->default('planifie');
            $table->date('date_prevue')->nullable();
            $table->date('date_livraison')->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 13. PARTIES PRENANTES — Stakeholder Register (optionnel)
        //     Identification et stratégie d'engagement
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_projet_parties_prenantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            // Peut être un user interne ou un contact externe
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('intranet_contacts')->nullOnDelete();
            $table->string('nom_externe')->nullable(); // si ni user ni contact
            $table->string('organisation_externe')->nullable();
            $table->string('role_projet')->nullable();        // Sponsor, Client, Fournisseur…
            $table->enum('categorie', ['interne','externe','regulateur','media','autre'])->default('interne');
            $table->unsignedTinyInteger('interet')->default(3);    // 1 (faible) → 5 (élevé)
            $table->unsignedTinyInteger('influence')->default(3);  // 1 (faible) → 5 (élevé)
            $table->enum('engagement_actuel', ['resistant','neutre','conscient','favorable','champion'])
                  ->default('neutre');
            $table->enum('engagement_desire', ['resistant','neutre','conscient','favorable','champion'])
                  ->default('favorable');
            $table->text('strategie_engagement')->nullable();
            $table->text('attentes')->nullable();
            $table->text('preoccupations')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 14. LEÇONS APPRISES (optionnel)
        //     Capitalisation sur l'expérience du projet
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_projet_lecons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('intranet_projets')->cascadeOnDelete();
            $table->string('titre');
            $table->enum('type', ['succes','echec','amelioration'])->default('amelioration');
            $table->string('categorie')->nullable(); // technique, processus, communication, risques…
            $table->text('description');
            $table->text('impact')->nullable();
            $table->text('recommandation');
            $table->enum('statut', ['brouillon','valide','publie'])->default('brouillon');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 15. CHECKLISTS DE TÂCHES (optionnel)
        //     Critères d'acceptation et sous-items à valider
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_tache_checklist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('intranet_taches')->cascadeOnDelete();
            $table->string('titre');
            $table->boolean('complete')->default(false);
            $table->integer('ordre')->default(0);
            $table->foreignId('complete_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('complete_le')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // 16. CHECKLISTS D'ACTIVITÉS (optionnel)
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_activite_checklist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activite_id')->constrained('intranet_activites')->cascadeOnDelete();
            $table->string('titre');
            $table->boolean('complete')->default(false);
            $table->integer('ordre')->default(0);
            $table->foreignId('complete_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('complete_le')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('intranet_taches', function (Blueprint $table) {
            $table->dropForeign(['phase_id']);
            $table->dropColumn('phase_id');
        });
        Schema::dropIfExists('intranet_activite_checklist');
        Schema::dropIfExists('intranet_tache_checklist');
        Schema::dropIfExists('intranet_projet_lecons');
        Schema::dropIfExists('intranet_projet_parties_prenantes');
        Schema::dropIfExists('intranet_projet_livrables');
        Schema::dropIfExists('intranet_projet_changements');
        Schema::dropIfExists('intranet_projet_problemes');
        Schema::dropIfExists('intranet_projet_risques');
        Schema::dropIfExists('intranet_projet_evm');
        Schema::dropIfExists('intranet_projet_couts');
        Schema::dropIfExists('intranet_feuilles_temps');
        Schema::dropIfExists('intranet_projet_ressources');
        Schema::dropIfExists('intranet_dependances');
        Schema::dropIfExists('intranet_jalons');
        Schema::dropIfExists('intranet_activites');
        Schema::dropIfExists('intranet_projet_phases');
    }
};
