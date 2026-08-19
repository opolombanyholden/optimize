<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rendre `description` nullable pour 5 entités PMP
        // (le contrôleur valide déjà comme nullable — le bloquant venait de la DB)
        foreach ([
            'intranet_projet_risques',
            'intranet_projet_problemes',
            'intranet_projet_changements',
            'intranet_projet_lecons',
        ] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->text('description')->nullable()->change();
            });
        }

        // recommandation des leçons : nullable aussi
        Schema::table('intranet_projet_lecons', function (Blueprint $t) {
            $t->text('recommandation')->nullable()->change();
        });

        // score des risques : colonne calculée Postgres (auto-générée), pas besoin de toucher

        // problemes : date_identification nullable (auto = now)
        Schema::table('intranet_projet_problemes', function (Blueprint $t) {
            $t->date('date_identification')->nullable()->change();
        });

        // changements : demandeur_id et date_soumission nullable (auto)
        Schema::table('intranet_projet_changements', function (Blueprint $t) {
            $t->foreignId('demandeur_id')->nullable()->change();
            $t->date('date_soumission')->nullable()->change();
        });

        // parties prenantes : rendre les enums obligatoires nullable
        // (l'utilisateur ne devrait pas être bloqué pour ces champs)
        Schema::table('intranet_projet_parties_prenantes', function (Blueprint $t) {
            $t->string('categorie')->nullable()->change();
            $t->smallInteger('interet')->nullable()->change();
            $t->smallInteger('influence')->nullable()->change();
            $t->string('engagement_actuel')->nullable()->change();
            $t->string('engagement_desire')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Pas de revert (rendre nullable est non destructif)
    }
};
