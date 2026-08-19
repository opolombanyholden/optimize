<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type_depart', 50); // demission, licenciement, retraite, fin_contrat, deces, rupture_conventionnelle
            $table->string('motif', 255)->nullable();
            $table->text('description')->nullable();
            $table->date('date_notification');
            $table->date('date_effet');
            $table->date('date_solde_tout_compte')->nullable();
            $table->boolean('preavis_effectue')->default(true);
            $table->decimal('indemnite_depart', 12, 2)->default(0);
            $table->decimal('solde_conges_paye', 12, 2)->default(0);
            $table->text('certificat_travail_url')->nullable();
            $table->text('attestation_pole_emploi_url')->nullable();
            $table->boolean('entretien_sortie_effectue')->default(false);
            $table->text('notes_entretien_sortie')->nullable();
            $table->foreignId('traite_par')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('fichiersjoin')->nullable();
            $table->jsonb('extra_attributes')->nullable();
            $table->smallInteger('statut')->default(0); // 0=annonce, 1=en_cours, 2=finalise
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'type_depart']);
            $table->index('date_effet');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departs');
    }
};
