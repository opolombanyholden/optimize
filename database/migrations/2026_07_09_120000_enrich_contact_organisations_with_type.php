<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fusion Organisation ↔ Client ↔ Fournisseur.
 *
 * L'organisation (au sens carnet d'adresses / CRM) absorbe les entités B2B :
 * client, fournisseur, investisseur, administration, autre. Le champ `type`
 * détermine son rôle relationnel avec l'entreprise. Un contact (individu) reste
 * lié à zéro ou une organisation via `organisation_id`.
 *
 * Idempotent — chaque colonne est vérifiée avant ajout.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('intranet_contact_organisations', function (Blueprint $t) {
            if (!Schema::hasColumn('intranet_contact_organisations', 'type')) {
                $t->string('type', 30)->default('autre')
                    ->comment('client | fournisseur | investisseur | administration | partenaire | autre');
                $t->index('type');
            }

            // Identification B2B
            if (!Schema::hasColumn('intranet_contact_organisations', 'code'))            $t->string('code', 60)->nullable();
            if (!Schema::hasColumn('intranet_contact_organisations', 'raison_sociale')) $t->string('raison_sociale')->nullable();
            if (!Schema::hasColumn('intranet_contact_organisations', 'forme_juridique'))$t->string('forme_juridique', 50)->nullable();
            if (!Schema::hasColumn('intranet_contact_organisations', 'nif'))            $t->string('nif', 50)->nullable();
            if (!Schema::hasColumn('intranet_contact_organisations', 'rccm'))           $t->string('rccm', 50)->nullable();

            // Bancaire
            if (!Schema::hasColumn('intranet_contact_organisations', 'rib'))    $t->string('rib', 60)->nullable();
            if (!Schema::hasColumn('intranet_contact_organisations', 'banque')) $t->string('banque', 100)->nullable();

            // Interlocuteur principal (personne physique de référence côté organisation)
            if (!Schema::hasColumn('intranet_contact_organisations', 'contact_principal_nom'))       $t->string('contact_principal_nom')->nullable();
            if (!Schema::hasColumn('intranet_contact_organisations', 'contact_principal_telephone')) $t->string('contact_principal_telephone', 50)->nullable();
            if (!Schema::hasColumn('intranet_contact_organisations', 'contact_principal_email'))     $t->string('contact_principal_email')->nullable();

            // Statut (actif / inactif)
            if (!Schema::hasColumn('intranet_contact_organisations', 'statut'))            $t->tinyInteger('statut')->default(1);
            if (!Schema::hasColumn('intranet_contact_organisations', 'extra_attributes')) $t->json('extra_attributes')->nullable();
        });

        // Index unique sur code (par type) — permet code identique entre un client et un fournisseur, mais unique dans son propre type
        if (Schema::hasColumn('intranet_contact_organisations', 'code')) {
            Schema::table('intranet_contact_organisations', function (Blueprint $t) {
                // index simple pour recherche rapide
                $t->index(['type', 'code'], 'idx_org_type_code');
            });
        }
    }

    public function down(): void
    {
        Schema::table('intranet_contact_organisations', function (Blueprint $t) {
            $t->dropIndex('idx_org_type_code');
            foreach ([
                'type', 'code', 'raison_sociale', 'forme_juridique', 'nif', 'rccm',
                'rib', 'banque', 'statut',
                'contact_principal_nom', 'contact_principal_telephone', 'contact_principal_email',
                'extra_attributes',
            ] as $col) {
                if (Schema::hasColumn('intranet_contact_organisations', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};
