<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affilies', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->string('liens')->nullable();
            $table->string('noms');
            $table->string('prenoms')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('contact1')->nullable();
            $table->string('contact2')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->text('fichiersjoin')->nullable();
            $table->tinyInteger('statut')->default(1);
            $table->json('extra_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affilies');
    }
};
