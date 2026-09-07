<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_group_messages', function (Blueprint $table) {
            $table->string('shared_type')->nullable()->after('contenu');
            $table->unsignedBigInteger('shared_id')->nullable()->after('shared_type');
            $table->string('contenu', 2000)->nullable()->change(); // permet un message purement partage/PJ
            $table->index(['shared_type', 'shared_id']);
        });
    }

    public function down(): void
    {
        Schema::table('social_group_messages', function (Blueprint $table) {
            $table->dropIndex(['shared_type', 'shared_id']);
            $table->dropColumn(['shared_type', 'shared_id']);
        });
    }
};
