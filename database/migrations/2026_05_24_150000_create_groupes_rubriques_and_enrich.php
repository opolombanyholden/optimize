<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Référentiel des groupes de rubriques (modèle ANPI-Gabon)
        Schema::create('groupes_rubriques', function (Blueprint $t) {
            $t->id();
            $t->string('code', 10)->unique();
            $t->string('libelle', 255);
            $t->text('description')->nullable();
            $t->integer('ordre')->default(0);
            $t->smallInteger('statut')->default(1);
            $t->jsonb('extra_attributes')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        // Enrichissement de la table rubriques
        Schema::table('rubriques', function (Blueprint $table) {
            $table->foreignId('groupe_rubrique_id')->nullable()->after('id')->constrained('groupes_rubriques')->nullOnDelete();
            $table->string('libelle_court', 50)->nullable()->after('libelle');
            $table->decimal('valeur2', 12, 4)->default(0)->after('taux'); // plafond, deuxième paramètre
            $table->date('date_effet')->nullable();
            $table->date('date_fin_effet')->nullable();
            $table->text('base_calcul_libelle')->nullable(); // ex: "Brut imposable", "Brut plafonné CNSS"
        });
    }

    public function down(): void
    {
        Schema::table('rubriques', function (Blueprint $table) {
            $table->dropConstrainedForeignId('groupe_rubrique_id');
            $table->dropColumn(['libelle_court', 'valeur2', 'date_effet', 'date_fin_effet', 'base_calcul_libelle']);
        });
        Schema::dropIfExists('groupes_rubriques');
    }
};
