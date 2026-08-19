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
        Schema::create('mode_reglements', function (Blueprint $table) {
            $table->id();
            $table->datetime('dateeffet')->useCurrent();
            $table->string('code')->nullable();
            $table->string('libelle')->nullable();
            $table->string('description')->nullable();
            $table->integer('statut')->default(1);
            $table->string('attribut1')->nullable();
            $table->string('attribut2')->nullable();
            $table->string('attribut3')->nullable();
            $table->integer('isvalide')->default(0);
            $table->integer('id_user')->default(1);
            $table->tinyInteger('effacer')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mode_reglements');
    }
};
