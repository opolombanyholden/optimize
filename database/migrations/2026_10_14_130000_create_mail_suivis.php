<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Classement des mails professionnels : priorité, catégorie, échéance, statut, note interne,
 * et liens vers n'importe quelle entité de la solution (projet, tâche, courrier, opportunité,
 * dépense…). Les mails restant sur le serveur IMAP, un mail est identifié par
 * (compte, Message-ID), stable quel que soit le dossier.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('mail_suivis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compte_id')->constrained('intranet_mail_comptes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('message_id', 500);
            // Résumé du mail (affiché sur les fiches liées sans accès à la boîte)
            $table->string('sujet', 255);
            $table->string('correspondant', 255)->nullable(); // expéditeur (reçu) ou destinataires (envoyé)
            $table->timestamp('date_mail')->nullable();
            $table->string('sens', 10);                        // recu | envoye
            $table->unsignedBigInteger('uid')->nullable();
            $table->string('dossier')->nullable();
            // Classement
            $table->string('priorite', 10)->default('normale');
            $table->string('categorie', 50)->nullable();
            $table->string('statut', 20)->nullable();          // a_traiter | en_attente | traite
            $table->date('echeance')->nullable();
            $table->text('note')->nullable();                  // note interne, jamais envoyée
            $table->boolean('confidentiel')->default(false);   // masqué aux autres sur les fiches liées
            $table->timestamps();
            $table->unique(['compte_id', 'message_id']);
            $table->index(['user_id', 'echeance']);
        });
        DB::statement("ALTER TABLE mail_suivis ADD CONSTRAINT mail_suivis_priorite_check CHECK (priorite IN ('basse','normale','haute','urgente'))");
        DB::statement("ALTER TABLE mail_suivis ADD CONSTRAINT mail_suivis_statut_check CHECK (statut IS NULL OR statut IN ('a_traiter','en_attente','traite'))");
        DB::statement("ALTER TABLE mail_suivis ADD CONSTRAINT mail_suivis_sens_check CHECK (sens IN ('recu','envoye'))");

        Schema::create('mail_suivi_liens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suivi_id')->constrained('mail_suivis')->cascadeOnDelete();
            $table->morphs('liable');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['suivi_id', 'liable_type', 'liable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_suivi_liens');
        Schema::dropIfExists('mail_suivis');
    }
};
