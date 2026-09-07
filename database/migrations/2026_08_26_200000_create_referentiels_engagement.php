<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_engagement', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique(); // achat, prestation, cadre, maintenance, licence, autre…
            $table->string('libelle');
            $table->string('description', 500)->nullable();
            $table->smallInteger('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('frequences_paiement', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique(); // ponctuel, mensuel, trimestriel…
            $table->string('libelle');
            $table->string('description', 500)->nullable();
            $table->smallInteger('mois_increment')->nullable(); // null = ponctuel ; sinon 1,2,3,6,12 → base du calcul prochaine échéance
            $table->smallInteger('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frequences_paiement');
        Schema::dropIfExists('types_engagement');
    }
};
