<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop the legacy FK to fournisseurs — fournisseur_id now targets intranet_contact_organisations
        // (fusion CRM). Repointing done in application code, not via FK to keep the field polymorphic-friendly.
        $fk = DB::selectOne("
            SELECT conname FROM pg_constraint
            WHERE conrelid = 'commande_fournisseurs'::regclass
              AND contype = 'f'
              AND conname LIKE '%fournisseur_id%'
        ");
        if ($fk) {
            DB::statement("ALTER TABLE commande_fournisseurs DROP CONSTRAINT {$fk->conname}");
        }
    }

    public function down(): void
    {
        Schema::table('commande_fournisseurs', function ($t) {
            $t->foreign('fournisseur_id')->references('id')->on('fournisseurs')->nullOnDelete();
        });
    }
};
