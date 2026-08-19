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
        Schema::create('changespasswords', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('users')->nullable()->constrained()->cascadeOnDelete();
            $table->string('password')->nullable();
            $table->timestamp('changed_at')->nullable()->useCurrent();
            $table->json('extra_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('changespasswords');
    }
};
