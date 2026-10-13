<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lien interaction CRM ↔ mail : les mails journalisés depuis le webmail gardent leur
 * Message-ID (anti-doublon), le compte et l'UID IMAP (lien « Ouvrir le mail »).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('intranet_crm_interactions', function (Blueprint $table) {
            $table->string('mail_message_id', 500)->nullable();
            $table->foreignId('mail_compte_id')->nullable()->constrained('intranet_mail_comptes')->nullOnDelete();
            $table->unsignedBigInteger('mail_uid')->nullable();
            $table->string('mail_dossier')->nullable();
            $table->string('mail_sens', 10)->nullable(); // recu | envoye
            $table->index('mail_message_id');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_crm_interactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mail_compte_id');
            $table->dropIndex(['mail_message_id']);
            $table->dropColumn(['mail_message_id', 'mail_uid', 'mail_dossier', 'mail_sens']);
        });
    }
};
