<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rollback des colonnes B2B ajoutées par erreur à `intranet_contacts`.
 *
 * Le contact reste un INDIVIDU. L'entité B2B (client / fournisseur /
 * investisseur / administration) est portée par `intranet_contact_organisations`.
 * Cette migration nettoie les colonnes qui auraient pu être créées lors d'un run
 * intermédiaire, et est idempotente.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('intranet_contacts', function (Blueprint $t) {
            foreach ([
                'type', 'code', 'raison_sociale', 'forme_juridique', 'nif', 'rccm',
                'rib', 'banque', 'statut',
                'contact_principal_nom', 'contact_principal_telephone', 'contact_principal_email',
                'extra_attributes',
            ] as $col) {
                if (Schema::hasColumn('intranet_contacts', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        // Pas de rollback : ces colonnes n'ont jamais fait partie du modèle métier.
    }
};
