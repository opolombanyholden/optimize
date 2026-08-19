<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_services', function (Blueprint $t) {
            // Type d'entité : direction, service, departement, equipe, autre
            $t->string('type_entite', 30)->default('service')->after('nom');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_services', function (Blueprint $t) {
            $t->dropColumn('type_entite');
        });
    }
};
