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
        Schema::create('lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_titre')->constrained('titres');
            $table->string('nature');
            $table->double('seuil')->nullable();
            $table->string('libelle');
            $table->mediumText('description')->nullable();
            $table->string('attribut1')->default('');
            $table->string('attribut2')->default('');
            $table->string('attribut3')->default('');
            $table->tinyInteger('effacer')->default(0);
            $table->integer('id_codeanalytique')->nullable();
            $table->integer('id_famillecodeanalytique')->nullable();
            $table->integer('id_user')->default(1);
            $table->datetime('dateeffet')->nullable();
            $table->integer('id_typecode')->nullable();
            $table->timestamps();
        });

        Schema::create('budget_lignes', function (Blueprint $table) {
            $table->id();
            $table->string('id_budgetligne');
            $table->timestamp('dateeffect')->nullable();
            $table->foreignId('id_exercicebudgetaire')->constrained('exercices');
            $table->string('exercice')->nullable();
            $table->foreignId('id_famillecodeanalytique')->nullable()->comment('id titre');
            $table->foreignId('id_codeanalytique')->nullable()->comment('id ligne');
            $table->string('codecompte')->nullable();
            $table->double('budgetligne', 8, 2)->default(0.00);
            $table->mediumText('commentaire')->nullable();
            $table->text('attribut1')->nullable();
            $table->text('attribut2')->nullable();
            $table->text('attribut3')->nullable();
            $table->tinyInteger('isvalide')->default(0);
            $table->foreignId('id_user')->constrained('users');
            $table->tinyInteger('effacer')->default(0);
            $table->double('dotation_etat', 8, 2)->nullable();
            $table->double('fonds_propres', 8, 2)->nullable();
            $table->double('reports_budgetaire', 8, 2)->nullable();
            $table->double('reports_tresorerie', 8, 2)->nullable();
            $table->double('transfert', 8, 2)->default(0.00);
            $table->double('engagement', 8, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_lignes');
        Schema::dropIfExists('lignes');
    }
};
