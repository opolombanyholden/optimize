<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enrichir les dossiers d'archives
        Schema::table('intranet_archive_dossiers', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('nom');
            $table->string('icone')->nullable()->after('couleur');
            $table->string('couverture')->nullable()->after('icone');
            $table->softDeletes();
            $table->index('parent_id');
        });

        // Enrichir les archives
        Schema::table('intranet_archives', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->string('nature', 80)->nullable()->after('reference'); // contrat, facture, PV, rapport…
            $table->json('tags')->nullable()->after('nature');
            $table->string('lieu_physique')->nullable()->after('tags'); // carton, étagère…
            $table->string('code_barre', 50)->nullable()->after('lieu_physique');
            $table->unsignedSmallInteger('duree_conservation_mois')->nullable()->after('code_barre');
            $table->date('date_destruction_prevue')->nullable()->after('duree_conservation_mois');
            $table->date('date_archivage')->nullable()->after('date_destruction_prevue');
            $table->enum('statut', ['actif', 'semi_actif', 'inactif', 'a_detruire'])->default('actif')->after('date_archivage');
            $table->unsignedInteger('telechargements_count')->default(0)->after('statut');
            $table->unsignedInteger('vues_count')->default(0)->after('telechargements_count');

            $table->index('statut');
            $table->index('nature');
            $table->index('date_destruction_prevue');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_archives', function (Blueprint $table) {
            $table->dropIndex(['statut']);
            $table->dropIndex(['nature']);
            $table->dropIndex(['date_destruction_prevue']);
            $table->dropColumn([
                'slug', 'nature', 'tags', 'lieu_physique', 'code_barre',
                'duree_conservation_mois', 'date_destruction_prevue',
                'date_archivage', 'statut', 'telechargements_count', 'vues_count',
            ]);
        });

        Schema::table('intranet_archive_dossiers', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            $table->dropSoftDeletes();
            $table->dropColumn(['slug', 'icone', 'couverture']);
        });
    }
};
