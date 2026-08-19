<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exercices', function (Blueprint $table) {
            $table->id();
            $table->string('exercice')->nullable();
            $table->string('libelle')->nullable();
            $table->foreignId('id_regroupement')->nullable();
            $table->timestamp('dateeffect')->useCurrent();
            $table->datetime('datedebut')->nullable();
            $table->datetime('datefin')->nullable();
            $table->datetime('entite')->nullable();
            $table->double('budgetglobalinitial')->nullable();
            $table->mediumText('commentaire')->nullable();
            $table->mediumText('attribut1')->nullable();
            $table->mediumText('attribut2')->nullable();
            $table->mediumText('attribut3')->nullable();
            $table->integer('isvalide')->default(0);
            $table->foreignId('id_user')->nullable()->constrained('users');
            $table->integer('effacer')->default(0);
            $table->integer('version_exercice')->default(1);
            $table->integer('statut')->default(3)->comment('1=planifie, 2=en cours, 3=cloture');
            $table->double('dotationglobale', 8, 2)->default(0.00);
            $table->double('fondpropreglobal', 8, 2)->default(0.00);
            $table->double('reportbudgetare', 8, 2)->default(0.00);
            $table->double('reporttresorerieglobal', 8, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercices');
    }
};
