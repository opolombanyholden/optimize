<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════
        // RÉFÉRENTIELS — Pays, Secteurs d'activité
        // ══════════════════════════════════════════════════════

        Schema::create('intranet_pays', function (Blueprint $table) {
            $table->id();
            $table->string('code_iso2', 2)->unique();
            $table->string('code_iso3', 3)->nullable();
            $table->string('nom');
            $table->string('nom_en')->nullable();
            $table->string('indicatif_telephone', 10)->nullable();
            $table->string('drapeau_emoji', 10)->nullable();
            $table->string('continent', 30)->nullable();
            $table->unsignedSmallInteger('ordre')->default(100);
            $table->timestamps();
        });

        Schema::create('intranet_secteurs_activite', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code', 50)->unique();
            $table->string('couleur', 20)->default('#7C3AED');
            $table->string('icone')->nullable();
            $table->unsignedSmallInteger('ordre')->default(100);
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // ENRICHISSEMENT intranet_contact_organisations
        // ══════════════════════════════════════════════════════
        Schema::table('intranet_contact_organisations', function (Blueprint $table) {
            $table->dropColumn('secteur');
        });

        Schema::table('intranet_contact_organisations', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('nom');
            $table->text('description')->nullable()->after('slug');
            $table->foreignId('secteur_id')->nullable()->after('description')
                  ->constrained('intranet_secteurs_activite')->nullOnDelete();
            $table->foreignId('pays_id')->nullable()->after('adresse')
                  ->constrained('intranet_pays')->nullOnDelete();
            $table->string('ville')->nullable()->after('pays_id');
            $table->string('code_postal', 20)->nullable()->after('ville');
            $table->enum('taille', ['TPE', 'PME', 'ETI', 'GE'])->nullable()->after('code_postal');
            $table->decimal('chiffre_affaires', 18, 2)->nullable()->after('taille');
            $table->unsignedInteger('effectif')->nullable()->after('chiffre_affaires');
            $table->string('siret', 30)->nullable()->after('effectif');
            $table->string('numero_tva', 30)->nullable()->after('siret');
            $table->string('linkedin')->nullable()->after('numero_tva');
            $table->json('tags')->nullable()->after('linkedin');
            $table->string('etiquette', 20)->nullable()->after('tags'); // hot, warm, cold
            $table->boolean('est_client')->default(false)->after('etiquette');
            $table->boolean('est_prospect')->default(true)->after('est_client');
            $table->unsignedInteger('vues_count')->default(0)->after('est_prospect');
        });

        // ══════════════════════════════════════════════════════
        // ENRICHISSEMENT intranet_contacts
        // ══════════════════════════════════════════════════════
        Schema::table('intranet_contacts', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->string('civilite', 10)->nullable()->after('slug');
            $table->string('linkedin')->nullable()->after('photo');
            $table->string('twitter')->nullable()->after('linkedin');
            $table->string('site_web')->nullable()->after('twitter');
            $table->text('adresse')->nullable()->after('site_web');
            $table->foreignId('pays_id')->nullable()->after('adresse')
                  ->constrained('intranet_pays')->nullOnDelete();
            $table->string('ville')->nullable()->after('pays_id');
            $table->string('langue', 10)->default('fr')->after('ville');
            $table->string('source')->nullable()->after('langue');
            $table->json('tags')->nullable()->after('source');
            $table->string('etiquette', 20)->nullable()->after('tags');
            $table->date('date_naissance')->nullable()->after('etiquette');
            $table->timestamp('derniere_interaction_le')->nullable()->after('date_naissance');
            $table->boolean('est_favori')->default(false)->after('derniere_interaction_le');
            $table->unsignedInteger('vues_count')->default(0)->after('est_favori');
        });

        // ══════════════════════════════════════════════════════
        // ENRICHISSEMENT intranet_crm_etapes
        // ══════════════════════════════════════════════════════
        Schema::table('intranet_crm_etapes', function (Blueprint $table) {
            $table->unsignedTinyInteger('probabilite_defaut')->default(0)->after('couleur');
            $table->boolean('est_finale')->default(false)->after('est_perdue');
        });

        // ══════════════════════════════════════════════════════
        // ENRICHISSEMENT intranet_opportunites
        // ══════════════════════════════════════════════════════
        Schema::table('intranet_opportunites', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('titre');
            $table->string('reference', 50)->nullable()->after('slug');
            $table->foreignId('responsable_id')->nullable()->after('created_by')
                  ->constrained('users')->nullOnDelete();
            $table->string('devise', 10)->default('XAF')->after('valeur');
            $table->date('date_creation_opp')->nullable()->after('date_echeance');
            $table->date('date_cloture_reelle')->nullable()->after('date_creation_opp');
            $table->text('raison_perte')->nullable()->after('date_cloture_reelle');
            $table->string('source')->nullable()->after('raison_perte');
            $table->json('tags')->nullable()->after('source');
            $table->unsignedInteger('vues_count')->default(0)->after('tags');
            $table->unsignedInteger('ordre_kanban')->default(0)->after('vues_count');
        });

        // ══════════════════════════════════════════════════════
        // INTERACTIONS (polymorphique : Contact ou Opportunite)
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_crm_interactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('interactable'); // interactable_type + interactable_id
            $table->enum('type', ['appel', 'reunion', 'email', 'note', 'tache', 'autre'])->default('note');
            $table->string('objet');
            $table->text('description')->nullable();
            $table->dateTime('date_interaction');
            $table->unsignedSmallInteger('duree_minutes')->nullable();
            $table->boolean('est_terminee')->default(true);
            $table->foreignId('realisee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_crm_interactions');

        Schema::table('intranet_opportunites', function (Blueprint $table) {
            $table->dropForeign(['responsable_id']);
            $table->dropColumn([
                'slug', 'reference', 'responsable_id', 'devise',
                'date_creation_opp', 'date_cloture_reelle', 'raison_perte',
                'source', 'tags', 'vues_count', 'ordre_kanban',
            ]);
        });

        Schema::table('intranet_crm_etapes', function (Blueprint $table) {
            $table->dropColumn(['probabilite_defaut', 'est_finale']);
        });

        Schema::table('intranet_contacts', function (Blueprint $table) {
            $table->dropForeign(['pays_id']);
            $table->dropColumn([
                'slug', 'civilite', 'linkedin', 'twitter', 'site_web',
                'adresse', 'pays_id', 'ville', 'langue', 'source',
                'tags', 'etiquette', 'date_naissance',
                'derniere_interaction_le', 'est_favori', 'vues_count',
            ]);
        });

        Schema::table('intranet_contact_organisations', function (Blueprint $table) {
            $table->dropForeign(['secteur_id']);
            $table->dropForeign(['pays_id']);
            $table->dropColumn([
                'slug', 'description', 'secteur_id', 'pays_id', 'ville', 'code_postal',
                'taille', 'chiffre_affaires', 'effectif', 'siret', 'numero_tva',
                'linkedin', 'tags', 'etiquette', 'est_client', 'est_prospect', 'vues_count',
            ]);
            $table->string('secteur')->nullable();
        });

        Schema::dropIfExists('intranet_secteurs_activite');
        Schema::dropIfExists('intranet_pays');
    }
};
