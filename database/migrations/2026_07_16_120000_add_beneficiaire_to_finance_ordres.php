<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bloc bénéficiaire (dépense) / client (recette) sur les ordres.
 *
 * Trois sources possibles :
 *  - `user`          → utilisateur interne du système (personnel)
 *  - `contact`       → contact individuel du CRM (intranet_contacts)
 *  - `organisation`  → organisation du CRM (intranet_contact_organisations)
 *  - `externe`       → identité saisie manuellement, non référencée
 *
 * `beneficiaire_ref_id` pointe vers la PK correspondante quand une source
 * interne est choisie. `beneficiaire_infos_json` stocke un snapshot des infos
 * (utile pour le rendu du PDF et pour conserver la trace même si l'entité est
 * modifiée ou supprimée plus tard).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('finance_ordres', function (Blueprint $t) {
            $t->string('beneficiaire_source', 20)->nullable()
                ->comment('user | contact | organisation | externe');
            $t->unsignedBigInteger('beneficiaire_ref_id')->nullable();
            $t->json('beneficiaire_infos_json')->nullable()
                ->comment('Snapshot des infos : nom, entite, telephone, email, adresse, nif…');
            $t->index(['beneficiaire_source', 'beneficiaire_ref_id'], 'idx_ordres_benef');
        });
    }

    public function down(): void
    {
        Schema::table('finance_ordres', function (Blueprint $t) {
            $t->dropIndex('idx_ordres_benef');
            $t->dropColumn(['beneficiaire_source', 'beneficiaire_ref_id', 'beneficiaire_infos_json']);
        });
    }
};
