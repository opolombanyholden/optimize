<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════
        // CRM — CONTACTS & ORGANISATIONS
        // ══════════════════════════════════════════════════

        Schema::create('intranet_contact_organisations', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('secteur')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();
            $table->string('site_web')->nullable();
            $table->string('logo')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('intranet_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenoms')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('poste')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('organisation_id')->nullable()->constrained('intranet_contact_organisations')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════
        // CRM — PIPELINE & OPPORTUNITÉS
        // ══════════════════════════════════════════════════

        Schema::create('intranet_crm_etapes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->integer('ordre')->default(0);
            $table->string('couleur', 20)->default('#4F46E5');
            $table->boolean('est_gagnee')->default(false);
            $table->boolean('est_perdue')->default(false);
            $table->timestamps();
        });

        Schema::create('intranet_opportunites', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained('intranet_contacts')->nullOnDelete();
            $table->foreignId('organisation_id')->nullable()->constrained('intranet_contact_organisations')->nullOnDelete();
            $table->foreignId('etape_id')->nullable()->constrained('intranet_crm_etapes')->nullOnDelete();
            $table->decimal('valeur', 15, 2)->nullable();
            $table->unsignedTinyInteger('probabilite')->default(0); // 0-100%
            $table->date('date_echeance')->nullable();
            $table->string('statut')->default('ouvert'); // ouvert, gagnee, perdue, abandonnee
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════
        // MÉDIATHÈQUE
        // ══════════════════════════════════════════════════

        Schema::create('intranet_media', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('type', ['image', 'video', 'audio', 'document', 'autre'])->default('document');
            $table->string('fichier');          // chemin stockage
            $table->string('nom_original');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('taille')->nullable(); // bytes
            $table->string('dossier')->nullable();
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════
        // OBJECTIFS, KPI & ÉVALUATIONS
        // ══════════════════════════════════════════════════

        Schema::create('intranet_objectifs', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('portee', ['organisation', 'service', 'equipe', 'individuel'])->default('organisation');
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut')->default('actif'); // actif, atteint, non_atteint, abandonne
            $table->foreignId('parent_id')->nullable()->constrained('intranet_objectifs')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('intranet_kpi', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->foreignId('objectif_id')->nullable()->constrained('intranet_objectifs')->nullOnDelete();
            $table->decimal('valeur_cible', 15, 4)->nullable();
            $table->decimal('valeur_actuelle', 15, 4)->default(0);
            $table->string('unite')->nullable(); // %, F, nb, jours…
            $table->enum('tendance', ['hausse', 'baisse', 'stable'])->default('stable');
            $table->string('periodicite')->default('mensuel'); // quotidien, hebdo, mensuel, annuel
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('intranet_kpi_valeurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_id')->constrained('intranet_kpi')->cascadeOnDelete();
            $table->decimal('valeur', 15, 4);
            $table->date('date_mesure');
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('intranet_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();        // évalué
            $table->foreignId('evaluateur_id')->constrained('users')->cascadeOnDelete();  // évaluateur
            $table->foreignId('objectif_id')->nullable()->constrained('intranet_objectifs')->nullOnDelete();
            $table->unsignedTinyInteger('score')->nullable(); // 0-100
            $table->text('commentaire')->nullable();
            $table->text('points_forts')->nullable();
            $table->text('axes_amelioration')->nullable();
            $table->date('date_evaluation');
            $table->string('statut')->default('brouillon'); // brouillon, finalise, valide
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════
        // WIKI — BASE DE CONNAISSANCES
        // ══════════════════════════════════════════════════

        Schema::create('intranet_wiki_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icone')->nullable();
            $table->string('couleur', 20)->default('#4F46E5');
            $table->foreignId('parent_id')->nullable()->constrained('intranet_wiki_categories')->nullOnDelete();
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });

        Schema::create('intranet_wiki_articles', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->longText('contenu');
            $table->foreignId('categorie_id')->nullable()->constrained('intranet_wiki_categories')->nullOnDelete();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_epingle')->default(false);
            $table->integer('vues')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════
        // RAPPORTS & COMPTES RENDUS
        // ══════════════════════════════════════════════════

        Schema::create('intranet_rapports', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->enum('type', ['rapport', 'compte_rendu', 'note_interne', 'synthese'])->default('rapport');
            $table->longText('contenu')->nullable();
            $table->string('statut')->default('brouillon'); // brouillon, finalise, diffuse
            $table->foreignId('evenement_id')->nullable()->constrained('intranet_evenements')->nullOnDelete();
            $table->date('date_document')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════
        // ARCHIVAGE DOCUMENTAIRE
        // ══════════════════════════════════════════════════

        Schema::create('intranet_archive_dossiers', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('intranet_archive_dossiers')->nullOnDelete();
            $table->string('couleur', 20)->default('#4F46E5');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('intranet_archives', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('fichier');
            $table->string('nom_original');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->string('reference')->nullable();
            $table->date('date_document')->nullable();
            $table->foreignId('dossier_id')->nullable()->constrained('intranet_archive_dossiers')->nullOnDelete();
            $table->boolean('is_confidentiel')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════
        // TEMPLATES DE DOCUMENTS
        // ══════════════════════════════════════════════════

        Schema::create('intranet_template_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('icone')->nullable();
            $table->timestamps();
        });

        Schema::create('intranet_templates', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->longText('contenu'); // HTML/Markdown du template
            $table->foreignId('categorie_id')->nullable()->constrained('intranet_template_categories')->nullOnDelete();
            $table->boolean('is_public')->default(true);
            $table->integer('utilisations')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════
        // MESSAGERIE PRO — COMPTES MAIL
        // ══════════════════════════════════════════════════

        Schema::create('intranet_mail_comptes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');                          // nom affiché
            $table->string('email');                        // adresse mail
            $table->string('protocole')->default('imap');   // imap / pop3
            $table->string('serveur_entrant');
            $table->unsignedSmallInteger('port_entrant')->default(993);
            $table->boolean('ssl_entrant')->default(true);
            $table->string('serveur_sortant');              // SMTP
            $table->unsignedSmallInteger('port_sortant')->default(587);
            $table->boolean('ssl_sortant')->default(true);
            $table->string('identifiant');
            $table->text('mot_de_passe');                   // chiffré
            $table->boolean('is_default')->default(false);
            $table->boolean('is_shared')->default(false);   // boîte partagée
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('intranet_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compte_id')->constrained('intranet_mail_comptes')->cascadeOnDelete();
            $table->string('uid')->nullable();              // UID IMAP
            $table->string('sujet');
            $table->string('expediteur');
            $table->json('destinataires');
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->longText('corps_html')->nullable();
            $table->longText('corps_texte')->nullable();
            $table->enum('dossier', ['reception', 'envoyes', 'brouillons', 'archives', 'corbeille'])->default('reception');
            $table->boolean('lu')->default(false);
            $table->boolean('important')->default(false);
            $table->timestamp('date_envoi')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_mails');
        Schema::dropIfExists('intranet_mail_comptes');
        Schema::dropIfExists('intranet_templates');
        Schema::dropIfExists('intranet_template_categories');
        Schema::dropIfExists('intranet_archives');
        Schema::dropIfExists('intranet_archive_dossiers');
        Schema::dropIfExists('intranet_rapports');
        Schema::dropIfExists('intranet_wiki_articles');
        Schema::dropIfExists('intranet_wiki_categories');
        Schema::dropIfExists('intranet_evaluations');
        Schema::dropIfExists('intranet_kpi_valeurs');
        Schema::dropIfExists('intranet_kpi');
        Schema::dropIfExists('intranet_objectifs');
        Schema::dropIfExists('intranet_media');
        Schema::dropIfExists('intranet_opportunites');
        Schema::dropIfExists('intranet_crm_etapes');
        Schema::dropIfExists('intranet_contacts');
        Schema::dropIfExists('intranet_contact_organisations');
    }
};
