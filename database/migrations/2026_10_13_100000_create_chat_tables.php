<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Messagerie instantanée 1-à-1 : conversations entre deux collaborateurs + messages,
 * et présence en ligne (users.last_seen_at).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            // Paire ordonnée (user_un_id < user_deux_id) → une seule conversation par paire
            $table->foreignId('user_un_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_deux_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->unique(['user_un_id', 'user_deux_id']);
            $table->index('user_deux_id');
        });
        DB::statement('ALTER TABLE chat_conversations ADD CONSTRAINT chat_conversations_paire_ordonnee CHECK (user_un_id < user_deux_id)');

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('contenu');
            $table->timestamp('lu_at')->nullable();
            $table->timestamps();
            $table->index(['conversation_id', 'id']);
            $table->index(['conversation_id', 'sender_id', 'lu_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn(Blueprint $table) => $table->dropColumn('last_seen_at'));
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
