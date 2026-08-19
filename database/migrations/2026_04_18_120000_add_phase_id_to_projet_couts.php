<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_projet_couts', function (Blueprint $table) {
            $table->foreignId('phase_id')->nullable()->after('projet_id')
                  ->constrained('intranet_projet_phases')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('intranet_projet_couts', function (Blueprint $table) {
            $table->dropForeign(['phase_id']);
            $table->dropColumn('phase_id');
        });
    }
};
