<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $t) {
            // PIN à 4-6 chiffres (hashé bcrypt) — utilisé avec le QR générique
            // pour identifier l'employé sur une borne de pointage partagée.
            $t->string('pointage_pin', 255)->nullable();
            $t->timestamp('pointage_pin_changed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $t) {
            $t->dropColumn(['pointage_pin', 'pointage_pin_changed_at']);
        });
    }
};
