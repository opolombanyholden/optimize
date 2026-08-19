<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Identifiants Sécurité sociale Gabon (visibles sur bulletin)
            $table->string('matricule_cnss', 30)->nullable()->after('numero_secu');
            $table->string('matricule_cnamgs', 30)->nullable()->after('matricule_cnss');
            // Parts fiscales pour calcul IRPP (Gabon : 2.0 marié + enfants × 0.5)
            $table->decimal('parts_fiscales', 4, 2)->default(1)->after('matricule_cnamgs');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['matricule_cnss', 'matricule_cnamgs', 'parts_fiscales']);
        });
    }
};
