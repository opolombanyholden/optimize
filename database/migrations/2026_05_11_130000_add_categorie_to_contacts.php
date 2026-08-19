<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_contacts', function (Blueprint $t) {
            // Catégorisation : client, fournisseur, partenaire, sponsor, prospect, autre
            $t->string('categorie', 30)->nullable()->after('etiquette');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_contacts', function (Blueprint $t) {
            $t->dropColumn('categorie');
        });
    }
};
