<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_projet_phases', function (Blueprint $table) {
            $table->foreignId('statut_id')->nullable()->after('avancement')
                  ->constrained('intranet_statuts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('intranet_projet_phases', function (Blueprint $table) {
            $table->dropForeign(['statut_id']);
            $table->dropColumn('statut_id');
        });
    }
};
