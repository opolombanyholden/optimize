<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_groups', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('description', 500)->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_public')->default(false); // public = joignable sans invitation
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('social_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('social_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20)->default('member'); // admin | member
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
            $table->unique(['group_id', 'user_id'], 'uniq_group_member');
            $table->index('user_id');
        });

        Schema::create('social_group_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('social_groups')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('contenu');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['group_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_group_messages');
        Schema::dropIfExists('social_group_members');
        Schema::dropIfExists('social_groups');
    }
};
