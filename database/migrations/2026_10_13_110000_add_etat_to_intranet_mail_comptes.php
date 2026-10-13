<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comptes mail professionnels connectés au profil : nom d'expéditeur + état de la
 * dernière connexion (affiché dans le profil et la boîte mail).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('intranet_mail_comptes', function (Blueprint $table) {
            $table->string('nom_expediteur', 150)->nullable();
            $table->timestamp('derniere_connexion_at')->nullable();
            $table->text('derniere_erreur')->nullable();
            $table->unique(['user_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('intranet_mail_comptes', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'email']);
            $table->dropColumn(['nom_expediteur', 'derniere_connexion_at', 'derniere_erreur']);
        });
    }
};
