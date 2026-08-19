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
        Schema::create('transaction_comptes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compte_id')->constrained('comptes');
            $table->tinyInteger('sens')->comment('1=debit, 0=credit');
            $table->double('montant', 8, 2);
            $table->tinyInteger('effacer')->default(0);
            $table->datetime('dateeffet')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('modification_budgetaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_compte_emission')->nullable()->constrained('comptes');
            $table->string('codecompte_emission')->nullable();
            $table->foreignId('id_compte_reception')->nullable()->constrained('comptes');
            $table->string('codecompte_reception')->nullable();
            $table->string('objetmodification')->nullable();
            $table->double('montant_modification', 8, 2)->nullable();
            $table->tinyInteger('isbudgetligne')->nullable();
            $table->mediumText('commentaire')->nullable();
            $table->foreignId('id_user')->constrained('users');
            $table->integer('statut')->default(0)->comment('0=brouillon, 1=soumis, 2=valide, 3=rejete');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modification_budgetaires');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('transaction_comptes');
    }
};
