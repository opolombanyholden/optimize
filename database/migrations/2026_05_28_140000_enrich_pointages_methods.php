<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pointages', function (Blueprint $t) {
            // Mode de pointage : manuel / qr_code / intranet / teletravail
            $t->string('mode_pointage', 20)->default('manuel');
            // Adresse IP de la requête (utile pour intranet/teletravail)
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            // Heures d'entrée et sortie (si pointage par check-in/check-out)
            $t->time('heure_entree')->nullable();
            $t->time('heure_sortie')->nullable();
            // Workflow validation N+1 (pour pointages en télétravail)
            $t->boolean('requires_validation_n1')->default(false);
            $t->foreignId('validation_n1_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('validation_n1_at')->nullable();
            $t->string('validation_n1_decision', 20)->nullable(); // 'approuve' | 'rejete'
            $t->text('validation_n1_commentaire')->nullable();

            $t->index(['mode_pointage', 'requires_validation_n1']);
        });

        Schema::table('employees', function (Blueprint $t) {
            // Token unique pour la génération du QR code de pointage
            // Régénérable depuis la fiche employé. Length 64 = secure random.
            $t->string('pointage_token', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('pointages', function (Blueprint $t) {
            $t->dropConstrainedForeignId('validation_n1_par');
            $t->dropColumn([
                'mode_pointage', 'ip_address', 'user_agent',
                'heure_entree', 'heure_sortie',
                'requires_validation_n1', 'validation_n1_at',
                'validation_n1_decision', 'validation_n1_commentaire',
            ]);
        });
        Schema::table('employees', function (Blueprint $t) {
            $t->dropColumn('pointage_token');
        });
    }
};
