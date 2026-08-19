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
        Schema::create('localites', function (Blueprint $table) {
            $table->id();
            $table->datetime('dateeffet')->useCurrent();
            $table->string('code')->nullable();
            $table->string('libelle');
            $table->integer('statut')->default(1);
            $table->string('attribut1')->nullable();
            $table->string('attribut2')->nullable();
            $table->string('attribut3')->nullable();
            $table->integer('isvalide')->default(1);
            $table->integer('id_user')->default(1);
            $table->integer('effacer')->default(0);
            $table->foreignId('id_typelocalite')->constrained('type_localites');
            $table->foreignId('id_region')->constrained('regions');
            $table->foreignId('id_pays')->constrained('pays');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('localites');
    }
};
