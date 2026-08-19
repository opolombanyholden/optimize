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
        Schema::create('grand_livres', function (Blueprint $table) {
            $table->id();
            $table->date('date_ecriture')->nullable();
            $table->foreignId('id_entite')->constrained('entites');
            $table->timestamp('dateeffect')->useCurrent();
            $table->double('montant_tc')->nullable();
            $table->string('sens')->nullable();
            $table->string('mode_reglement')->nullable();
            $table->string('exercice')->nullable();
            $table->foreignId('id_exercicebudgetaire')->nullable()->constrained('exercices');
            $table->mediumText('description')->nullable();
            $table->foreignId('id_user')->constrained('users');
            $table->string('rib')->nullable();
            $table->string('banque')->nullable();
            $table->string('beneficiaire')->nullable();
            $table->string('type_beneficiaire')->nullable();
            $table->string('beneficiaire_interne')->nullable();
            $table->string('id_beneficiaire')->nullable();
            $table->foreignId('id_beneficiaire_interne')->nullable();
            $table->string('libelle_piece')->nullable();
            $table->string('type_piece')->nullable();
            $table->foreignId('id_ligne')->nullable();
            $table->foreignId('id_titre')->nullable();
            $table->string('imputation')->nullable();
            $table->string('nature')->nullable();
            $table->string('num_piece')->nullable();
            $table->string('ref_piece')->nullable();
            $table->string('nature_piece')->nullable();
            $table->double('montant_signe_tc')->nullable();
            $table->foreignId('compte_id')->constrained('comptes');
            $table->string('compte_general')->nullable();
            $table->string('compte_auxiliaire')->nullable();
            $table->string('role_tiers')->nullable();
            $table->string('journal')->nullable();
            $table->string('libelle')->nullable();
            $table->string('devise', 100)->nullable();
            $table->double('montant_tr')->nullable();
            $table->string('code_lettrage')->nullable();
            $table->string('type_marquage')->nullable();
            $table->string('date_lettrage', 100)->nullable();
            $table->string('date_pointage', 100)->nullable();
            $table->string('lettre_rappro')->nullable();
            $table->date('date_rappro')->nullable();
            $table->string('type_ecriture')->nullable();
            $table->string('num_lot')->nullable();
            $table->string('num_ecriture')->nullable();
            $table->string('code_tiers')->nullable();
            $table->double('montant_signe_tr', 8, 2)->nullable();
            $table->foreignId('id_famillecodeanalytique')->nullable();
            $table->foreignId('id_codeanalytique')->nullable();
            $table->foreignId('id_organisation')->nullable();
            $table->string('attribut1')->nullable();
            $table->string('attribut2')->nullable();
            $table->string('attribut3')->nullable();
            $table->integer('isvalide')->default(0);
            $table->integer('effacer')->default(0);
            $table->string('num_stat')->nullable();
            $table->string('nif')->nullable();
            $table->foreignId('id_facture')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grand_livres');
    }
};
