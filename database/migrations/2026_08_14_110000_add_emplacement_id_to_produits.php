<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $t) {
            if (!Schema::hasColumn('produits', 'emplacement_id')) {
                $t->foreignId('emplacement_id')->nullable()->after('emplacement')->constrained('emplacements')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $t) {
            if (Schema::hasColumn('produits', 'emplacement_id')) $t->dropConstrainedForeignId('emplacement_id');
        });
    }
};
