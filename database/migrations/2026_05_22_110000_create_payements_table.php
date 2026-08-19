<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paie_id')->constrained('gpaies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date_payement');
            $table->decimal('montant', 12, 2);
            $table->string('mode_payement', 30); // virement, cheque, especes, mobile_money
            $table->string('reference_payement', 100)->nullable(); // numero cheque, ref virement
            $table->string('banque', 100)->nullable();
            $table->string('iban', 50)->nullable();
            $table->text('justificatif_url')->nullable();
            $table->foreignId('execute_par')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('extra_attributes')->nullable();
            $table->smallInteger('statut')->default(1); // 0=annule, 1=execute, 2=rejete_banque
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'date_payement']);
            $table->index('paie_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payements');
    }
};
