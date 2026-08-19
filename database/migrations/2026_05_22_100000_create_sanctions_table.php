<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sanctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type', 50); // rappel_ordre, blame, avertissement, mise_a_pied, licenciement
            $table->string('motif', 255);
            $table->text('description')->nullable();
            $table->date('date_fait')->nullable();
            $table->date('date_notification');
            $table->date('date_fin')->nullable(); // pour mise à pied
            $table->foreignId('decidee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reaction_employe')->nullable();
            $table->string('niveau_gravite', 20)->default('mineure'); // mineure, modere, grave, severe
            $table->jsonb('fichiersjoin')->nullable();
            $table->jsonb('extra_attributes')->nullable();
            $table->smallInteger('statut')->default(1); // 0=annulee, 1=active, 2=archivee
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'type']);
            $table->index('date_notification');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanctions');
    }
};
