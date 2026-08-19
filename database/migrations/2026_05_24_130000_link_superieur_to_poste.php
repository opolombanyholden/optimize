<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Nouveau lien : supérieur défini par POSTE (référentiel), pas par PERSONNE.
            // L'identité du supérieur actuel se déduit de l'employé occupant ce poste.
            // L'ancien `superieur_hierarchique` (FK employees) est conservé pour rétrocompatibilité
            // et peut servir d'override manuel ponctuel.
            $table->foreignId('superieur_poste_id')
                ->nullable()
                ->after('superieur_hierarchique')
                ->constrained('postes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('superieur_poste_id');
        });
    }
};
