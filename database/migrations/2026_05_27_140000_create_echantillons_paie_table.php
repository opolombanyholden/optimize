<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('echantillons_paie', function (Blueprint $t) {
            $t->id();
            $t->string('code', 60)->unique();   // ex: ECH-FORCE-VENTE
            $t->string('libelle', 255);
            $t->text('description')->nullable();
            $t->string('couleur', 9)->nullable();  // ex: #16A34A (identification visuelle)
            $t->string('icone', 40)->nullable();   // ex: fa-users (Font Awesome)
            $t->boolean('statut')->default(true); // true = actif, false = archivé
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->jsonb('extra_attributes')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->index('statut');
        });

        // Pivot : employés rattachés à un échantillon
        Schema::create('echantillon_paie_employe', function (Blueprint $t) {
            $t->id();
            $t->foreignId('echantillon_paie_id')->constrained('echantillons_paie')->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->unique(['echantillon_paie_id', 'employee_id']);
            $t->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('echantillon_paie_employe');
        Schema::dropIfExists('echantillons_paie');
    }
};
