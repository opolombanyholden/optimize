<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_projets', function (Blueprint $table) {
            $table->foreignId('objectif_id')->nullable()->after('statut_id')
                  ->constrained('intranet_objectifs')->nullOnDelete();
        });

        Schema::table('intranet_taches', function (Blueprint $table) {
            $table->foreignId('objectif_id')->nullable()->after('projet_id')
                  ->constrained('intranet_objectifs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('intranet_taches', function (Blueprint $table) {
            $table->dropForeign(['objectif_id']);
            $table->dropColumn('objectif_id');
        });
        Schema::table('intranet_projets', function (Blueprint $table) {
            $table->dropForeign(['objectif_id']);
            $table->dropColumn('objectif_id');
        });
    }
};
