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
        Schema::create('organisations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('label');
            $table->text('introduction')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('typesorganisation_id')->constrained('typesorganisations');
            $table->foreignId('chefs')->nullable()->constrained('users')->comment('user_id');
            $table->foreignId('peres')->nullable()->references('id')->on('organisations')->nullOnDelete()->comment('organisation_id parent');
            $table->integer('statut')->default(1);
            $table->json('extra_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
