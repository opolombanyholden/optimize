<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rend `factures.tiers_id` nullable pour supporter les tiers externes
 * (fournisseurs/clients non enregistrés dont les infos sont dans tiers_infos_json).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $t) {
            $t->unsignedBigInteger('tiers_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Best-effort : à défaut de garantie que toutes les lignes ont un tiers_id
        Schema::table('factures', function (Blueprint $t) {
            $t->unsignedBigInteger('tiers_id')->nullable(false)->change();
        });
    }
};
