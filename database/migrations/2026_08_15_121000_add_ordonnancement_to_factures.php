<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $t) {
            if (!Schema::hasColumn('factures', 'ordonnancee_at')) {
                $t->timestamp('ordonnancee_at')->nullable()->after('sens');
            }
            if (!Schema::hasColumn('factures', 'ordonnee_par')) {
                $t->foreignId('ordonnee_par')->nullable()->after('ordonnancee_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('factures', 'motivation_ordonnancement')) {
                $t->text('motivation_ordonnancement')->nullable()->after('ordonnee_par');
            }
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $t) {
            foreach (['motivation_ordonnancement', 'ordonnee_par', 'ordonnancee_at'] as $c) {
                if (Schema::hasColumn('factures', $c)) $t->dropColumn($c);
            }
        });
    }
};
