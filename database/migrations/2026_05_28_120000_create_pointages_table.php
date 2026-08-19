<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pointages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $t->date('date');
            // Décompose les heures en 4 catégories (utilisables pour majoration)
            $t->decimal('h_normales', 5, 2)->default(0);
            $t->decimal('h_sup', 5, 2)->default(0);
            $t->decimal('h_nuit', 5, 2)->default(0);
            $t->decimal('h_dimanche', 5, 2)->default(0);
            // Statut :
            //  0 = brouillon (modifiable)
            //  1 = validé (verrouillé, pris en compte par la paie)
            //  2 = utilisé dans bulletin (verrou ferme)
            $t->smallInteger('statut')->default(0);
            $t->string('motif', 100)->nullable(); // ex: absence justifiée, formation, mission
            $t->text('notes')->nullable();
            $t->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('valide_at')->nullable();
            $t->jsonb('extra_attributes')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->unique(['employee_id', 'date']);
            $t->index(['date', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointages');
    }
};
