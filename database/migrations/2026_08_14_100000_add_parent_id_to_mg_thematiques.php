<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mg_thematiques', function (Blueprint $t) {
            if (!Schema::hasColumn('mg_thematiques', 'parent_id')) {
                $t->foreignId('parent_id')->nullable()->after('id')->constrained('mg_thematiques')->nullOnDelete();
                $t->index(['parent_id', 'ordre']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('mg_thematiques', function (Blueprint $t) {
            if (Schema::hasColumn('mg_thematiques', 'parent_id')) {
                $t->dropConstrainedForeignId('parent_id');
            }
        });
    }
};
