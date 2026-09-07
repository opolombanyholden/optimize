<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dysfonctionnement_assignations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dysfonctionnement_id')
                ->constrained('dysfonctionnements')->cascadeOnDelete();
            // Polymorphique : User (personne) ou Service (entité) ou Groupe (équipe)
            $table->string('assignable_type');
            $table->unsignedBigInteger('assignable_id');
            $table->foreignId('assigne_par')->nullable()->constrained('users')->nullOnDelete();
            $table->text('commentaire')->nullable();
            $table->timestamp('assigne_at')->useCurrent();
            $table->timestamp('retire_at')->nullable();
            $table->timestamps();

            $table->index(['assignable_type', 'assignable_id']);
            $table->unique(['dysfonctionnement_id', 'assignable_type', 'assignable_id'], 'uniq_dysfonc_assignable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dysfonctionnement_assignations');
    }
};
