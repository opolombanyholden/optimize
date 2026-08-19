<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiel des documents à fournir par une organisation.
 *
 * Trois tables :
 *  - `intranet_types_documents`         : catalogue des types de docs (NIF, RCCM, RIB, KYC, statuts…)
 *  - `intranet_types_documents_exigences`: matrice type_document × type_organisation → obligatoire/facultatif
 *  - `intranet_organisation_documents`  : instances de documents effectivement fournis (fichier + méta)
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('intranet_types_documents', function (Blueprint $t) {
            $t->id();
            $t->string('code', 60)->unique()->comment('Slug technique : nif, rccm, rib, kyc…');
            $t->string('libelle');
            $t->text('description')->nullable();
            $t->string('icone', 60)->default('fa-file-lines')->comment('Icône FontAwesome');
            $t->integer('ordre')->default(0);
            $t->boolean('avec_expiration')->default(false)->comment('Le document a-t-il une date d\'expiration ?');
            $t->boolean('actif')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('intranet_types_documents_exigences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('type_document_id')->constrained('intranet_types_documents')->cascadeOnDelete();
            $t->string('type_organisation', 30)->comment('client, fournisseur, investisseur, administration, partenaire, autre');
            $t->boolean('obligatoire')->default(false);
            $t->timestamps();

            $t->unique(['type_document_id', 'type_organisation'], 'uniq_typedoc_typeorg');
            $t->index('type_organisation');
        });

        Schema::create('intranet_organisation_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organisation_id')->constrained('intranet_contact_organisations')->cascadeOnDelete();
            $t->foreignId('type_document_id')->constrained('intranet_types_documents')->cascadeOnDelete();
            $t->string('fichier')->comment('Chemin storage/public');
            $t->string('nom_original')->nullable();
            $t->unsignedBigInteger('taille')->nullable();
            $t->string('mime', 100)->nullable();
            $t->date('date_expiration')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['organisation_id', 'type_document_id'], 'idx_orgdoc_org_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_organisation_documents');
        Schema::dropIfExists('intranet_types_documents_exigences');
        Schema::dropIfExists('intranet_types_documents');
    }
};
