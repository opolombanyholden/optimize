<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intranet_evenement_invites_externes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('intranet_evenements')->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('intranet_contacts')->nullOnDelete();
            $table->string('nom')->nullable();
            $table->string('email');
            $table->enum('statut', ['invite', 'confirme', 'decline', 'peut_etre'])->default('invite');
            $table->string('token', 64)->unique()->nullable(); // pour lien RSVP externe
            $table->timestamp('repondu_le')->nullable();
            $table->timestamps();

            $table->unique(['evenement_id', 'email']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_evenement_invites_externes');
    }
};
