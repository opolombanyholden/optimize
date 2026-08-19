<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enrichit les tables existantes intranet_projets et intranet_taches
 * avec les champs PMP nécessaires.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── intranet_projets ──────────────────────────────────────
        Schema::table('intranet_projets', function (Blueprint $table) {
            $table->string('code_projet', 50)->nullable()->after('nom');
            $table->string('categorie')->nullable()->after('code_projet');       // IT, RH, Marketing…
            $table->text('objectifs')->nullable()->after('description');
            $table->text('perimetre_inclus')->nullable()->after('objectifs');
            $table->text('perimetre_exclus')->nullable()->after('perimetre_inclus');
            $table->text('hypotheses')->nullable()->after('perimetre_exclus');
            $table->text('contraintes')->nullable()->after('hypotheses');
            $table->decimal('budget_approuve', 15, 2)->nullable()->after('contraintes');
            $table->foreignId('sponsor_id')->nullable()->after('created_by')
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('chef_projet_id')->nullable()->after('sponsor_id')
                  ->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('avancement')->default(0)->after('chef_projet_id'); // 0-100%
            $table->string('devise', 10)->default('XAF')->after('avancement');
        });

        // ── intranet_taches ───────────────────────────────────────
        Schema::table('intranet_taches', function (Blueprint $table) {
            $table->foreignId('phase_id')->nullable()->after('projet_id')
                  ->constrained('intranet_projets')->nullOnDelete(); // remplacé par intranet_projet_phases après
            $table->unsignedSmallInteger('ordre')->default(0)->after('phase_id');
            $table->decimal('heures_estimees', 8, 2)->nullable()->after('ordre');
            $table->decimal('heures_reelles', 8, 2)->nullable()->after('heures_estimees');
            $table->unsignedTinyInteger('avancement')->default(0)->after('heures_reelles'); // 0-100%
            $table->foreignId('responsable_id')->nullable()->after('avancement')
                  ->constrained('users')->nullOnDelete();
            $table->boolean('est_jalon')->default(false)->after('responsable_id');
        });

        // Correction : phase_id doit pointer vers intranet_projet_phases (créée ensuite)
        // On supprime et recrée après
        Schema::table('intranet_taches', function (Blueprint $table) {
            $table->dropForeign(['phase_id']);
            $table->dropColumn('phase_id');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_taches', function (Blueprint $table) {
            $table->dropColumn(['ordre','heures_estimees','heures_reelles','avancement','responsable_id','est_jalon']);
        });
        Schema::table('intranet_projets', function (Blueprint $table) {
            $table->dropForeign(['sponsor_id']);
            $table->dropForeign(['chef_projet_id']);
            $table->dropColumn(['code_projet','categorie','objectifs','perimetre_inclus','perimetre_exclus','hypotheses','contraintes','budget_approuve','sponsor_id','chef_projet_id','avancement','devise']);
        });
    }
};
