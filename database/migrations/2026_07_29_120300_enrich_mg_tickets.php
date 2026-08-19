<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mg_thematiques', function (Blueprint $t) {
            $t->id();
            $t->string('libelle');
            $t->text('description')->nullable();
            $t->string('couleur', 20)->default('#6c757d');
            $t->integer('ordre')->default(0);
            $t->boolean('actif')->default(true);
            $t->timestamps();
        });

        // Dysfonctionnement devient un ticket ; enrichi avec thématique + numéro ticket
        Schema::table('dysfonctionnements', function (Blueprint $t) {
            if (!Schema::hasColumn('dysfonctionnements', 'ticket_ref')) $t->string('ticket_ref', 30)->nullable()->unique()->after('id');
            if (!Schema::hasColumn('dysfonctionnements', 'thematique_id')) $t->foreignId('thematique_id')->nullable()->after('type_id')->constrained('mg_thematiques')->nullOnDelete();
            if (!Schema::hasColumn('dysfonctionnements', 'priorite_admin')) $t->string('priorite_admin', 20)->nullable()->comment('priorisation par admin');
            if (!Schema::hasColumn('dysfonctionnements', 'moment_intervention')) $t->timestamp('moment_intervention')->nullable();
            if (!Schema::hasColumn('dysfonctionnements', 'priorise_par')) $t->foreignId('priorise_par')->nullable()->constrained('users')->nullOnDelete();
            if (!Schema::hasColumn('dysfonctionnements', 'priorise_at')) $t->timestamp('priorise_at')->nullable();
        });

        // Interventions enrichies avec le détail d'analyse + résultat
        Schema::table('interventions', function (Blueprint $t) {
            if (!Schema::hasColumn('interventions', 'thematique_id')) $t->foreignId('thematique_id')->nullable()->after('dysfonctionnement_id')->constrained('mg_thematiques')->nullOnDelete();
            if (!Schema::hasColumn('interventions', 'nature_probleme')) $t->text('nature_probleme')->nullable();
            if (!Schema::hasColumn('interventions', 'pistes_solution')) $t->text('pistes_solution')->nullable();
            if (!Schema::hasColumn('interventions', 'solution_appliquee')) $t->text('solution_appliquee')->nullable();
            if (!Schema::hasColumn('interventions', 'resultat')) $t->text('resultat')->nullable();
            if (!Schema::hasColumn('interventions', 'statut_resolution')) $t->string('statut_resolution', 20)->nullable()->comment('resolu | partiel | non_resolu');
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $t) {
            foreach (['thematique_id','nature_probleme','pistes_solution','solution_appliquee','resultat','statut_resolution'] as $c) {
                if (Schema::hasColumn('interventions', $c)) $t->dropColumn($c);
            }
        });
        Schema::table('dysfonctionnements', function (Blueprint $t) {
            foreach (['ticket_ref','thematique_id','priorite_admin','moment_intervention','priorise_par','priorise_at'] as $c) {
                if (Schema::hasColumn('dysfonctionnements', $c)) $t->dropColumn($c);
            }
        });
        Schema::dropIfExists('mg_thematiques');
    }
};
