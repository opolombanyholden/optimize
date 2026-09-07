<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->text('contenu');
            $table->string('media_principal')->nullable();
            $table->string('media_principal_type', 20)->nullable(); // image / video
            $table->integer('vues_count')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('created_by');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
