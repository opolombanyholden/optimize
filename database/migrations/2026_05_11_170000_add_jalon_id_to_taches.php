<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_taches', function (Blueprint $t) {
            $t->foreignId('jalon_id')->nullable()->after('phase_id')
              ->constrained('intranet_jalons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('intranet_taches', function (Blueprint $t) {
            $t->dropForeign(['jalon_id']);
            $t->dropColumn('jalon_id');
        });
    }
};
