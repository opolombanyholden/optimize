<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('commande_fournisseurs', function (Blueprint $t) {
            if (!Schema::hasColumn('commande_fournisseurs', 'objet')) {
                $t->string('objet', 255)->nullable()->after('numero_commande');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commande_fournisseurs', function (Blueprint $t) {
            if (Schema::hasColumn('commande_fournisseurs', 'objet')) $t->dropColumn('objet');
        });
    }
};
