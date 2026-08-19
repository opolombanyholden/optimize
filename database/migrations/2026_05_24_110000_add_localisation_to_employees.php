<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Hiérarchie administrative (Gabon et pays comparables)
            $table->string('pays', 100)->nullable()->after('adresse');
            $table->string('province', 100)->nullable()->after('pays');
            $table->string('departement_geo', 100)->nullable()->after('province');
            $table->string('prefecture', 100)->nullable()->after('departement_geo');
            $table->string('sous_prefecture', 100)->nullable()->after('prefecture');
            // Zone : urbaine ou rurale (détermine quels champs sont remplis)
            $table->string('zone_type', 20)->nullable()->after('sous_prefecture'); // 'urbaine' ou 'rurale'
            // Subdivisions urbaines
            $table->string('commune', 100)->nullable()->after('zone_type');
            $table->string('arrondissement', 100)->nullable()->after('commune');
            $table->string('quartier_loc', 100)->nullable()->after('arrondissement');
            // Subdivisions rurales
            $table->string('canton', 100)->nullable()->after('quartier_loc');
            $table->string('regroupement_village', 100)->nullable()->after('canton');
            $table->string('village', 100)->nullable()->after('regroupement_village');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'pays', 'province', 'departement_geo', 'prefecture', 'sous_prefecture',
                'zone_type', 'commune', 'arrondissement', 'quartier_loc',
                'canton', 'regroupement_village', 'village',
            ]);
        });
    }
};
