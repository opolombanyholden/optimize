<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_projet_parties_prenantes', function (Blueprint $t) {
            $t->string('categorie')->nullable()->change();
            $t->smallInteger('interet')->nullable()->change();
            $t->smallInteger('influence')->nullable()->change();
            $t->string('engagement_actuel')->nullable()->change();
            $t->string('engagement_desire')->nullable()->change();
        });
    }

    public function down(): void {}
};
