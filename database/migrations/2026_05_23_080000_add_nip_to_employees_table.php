<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // NIP — Numéro d'Identification Personnel (Gabon)
            // Format : XX-XXXX-AAAAMMJJ (16 caractères avec tirets)
            $table->string('nip', 20)->nullable()->after('numero_secu');
            $table->index('nip');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['nip']);
            $table->dropColumn('nip');
        });
    }
};
