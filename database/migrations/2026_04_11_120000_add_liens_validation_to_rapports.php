<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_rapports', function (Blueprint $table) {
            // Liens vers phase, tâche, activité du projet
            $table->foreignId('phase_id')->nullable()->after('projet_id')
                  ->constrained('intranet_projet_phases')->nullOnDelete();
            $table->foreignId('tache_id')->nullable()->after('phase_id')
                  ->constrained('intranet_taches')->nullOnDelete();
            $table->foreignId('activite_id')->nullable()->after('tache_id')
                  ->constrained('intranet_activites')->nullOnDelete();

            // Workflow de validation N+1
            $table->foreignId('valideur_id')->nullable()->after('created_by')
                  ->constrained('users')->nullOnDelete();
            $table->enum('statut_validation', ['en_attente', 'approuve', 'rejete', 'revisions_demandees'])
                  ->nullable()->after('valideur_id');
            $table->text('commentaire_validation')->nullable()->after('statut_validation');
            $table->timestamp('valide_le')->nullable()->after('commentaire_validation');
            $table->timestamp('soumis_le')->nullable()->after('valide_le');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_rapports', function (Blueprint $table) {
            $table->dropForeign(['phase_id']);
            $table->dropForeign(['tache_id']);
            $table->dropForeign(['activite_id']);
            $table->dropForeign(['valideur_id']);
            $table->dropColumn([
                'phase_id', 'tache_id', 'activite_id',
                'valideur_id', 'statut_validation',
                'commentaire_validation', 'valide_le', 'soumis_le',
            ]);
        });
    }
};
