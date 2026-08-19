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
        Schema::create('type_localites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->datetime('dateeffet')->nullable();
            $table->string('libelle');
            $table->string('attribut1')->nullable();
            $table->string('attribut2')->nullable();
            $table->string('attribut3')->nullable();
            $table->integer('isvalide')->default(1);
            $table->integer('id_user')->default(1);
            $table->integer('effacer')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_localites');
    }
};
