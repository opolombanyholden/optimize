<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_projet_phases', function (Blueprint $table) {
            $table->decimal('ponderation', 5, 2)->nullable()->after('avancement');
            $table->date('date_debut_reelle')->nullable()->after('date_fin');
            $table->date('date_fin_reelle')->nullable()->after('date_debut_reelle');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_projet_phases', function (Blueprint $table) {
            $table->dropColumn(['ponderation', 'date_debut_reelle', 'date_fin_reelle']);
        });
    }
};
