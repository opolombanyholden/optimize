<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Table polymorphe : tous les niveaux administratifs partagent ce schéma.
        // type ∈ { pays, province, departement-admin, prefecture, sous-prefecture,
        //          commune, arrondissement, quartier, canton, regroupement-village, village }
        Schema::create('referentiel_localisations', function (Blueprint $t) {
            $t->id();
            $t->string('type', 50);
            $t->string('code', 80);
            $t->string('libelle', 255);
            $t->text('description')->nullable();
            $t->unsignedBigInteger('parent_id')->nullable(); // pour évolution cascading
            $t->integer('ordre')->default(0);
            $t->smallInteger('statut')->default(1);
            $t->jsonb('extra_attributes')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->unique(['type', 'code']);
            $t->index('type');
            $t->index('parent_id');
            $t->index('statut');
            $t->foreign('parent_id')->references('id')->on('referentiel_localisations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referentiel_localisations');
    }
};
