<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            // Force le changement de mot de passe au prochain login
            // (mis à true après reset admin, false après changement par l'utilisateur)
            $t->boolean('must_change_password')->default(false);
            // Trace du dernier changement réussi (pour politiques d'expiration éventuelles)
            $t->timestamp('password_changed_at')->nullable();
            // Trace du dernier reset par un admin (qui, quand) — colonne nullable pour ne pas casser l'existant
            $t->foreignId('password_reset_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('password_reset_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('password_reset_by');
            $t->dropColumn(['must_change_password', 'password_changed_at', 'password_reset_at']);
        });
    }
};
