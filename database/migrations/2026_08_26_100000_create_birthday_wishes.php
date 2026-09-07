<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_wishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('expediteur_id')->constrained('users')->cascadeOnDelete();
            $table->date('date_anniversaire'); // anniversaire ciblé (année en cours)
            $table->string('message', 500)->nullable();
            $table->timestamps();

            // Un utilisateur ne peut souhaiter qu'une fois par anniversaire (même date)
            $table->unique(['employee_id', 'expediteur_id', 'date_anniversaire'], 'uniq_wish_per_day');
            $table->index(['employee_id', 'date_anniversaire']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_wishes');
    }
};
