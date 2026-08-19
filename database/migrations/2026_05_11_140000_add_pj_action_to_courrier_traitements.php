<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter 'piece_jointe_ajoutee' aux valeurs autorisées du check constraint
        DB::statement('ALTER TABLE intranet_courrier_traitements DROP CONSTRAINT IF EXISTS intranet_courrier_traitements_action_check');
        DB::statement("ALTER TABLE intranet_courrier_traitements ADD CONSTRAINT intranet_courrier_traitements_action_check
            CHECK (action IN ('cree','assigne','reassigne','commente','accuse_reception','traite','archive','rouvert','piece_jointe_ajoutee'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE intranet_courrier_traitements DROP CONSTRAINT IF EXISTS intranet_courrier_traitements_action_check');
        DB::statement("ALTER TABLE intranet_courrier_traitements ADD CONSTRAINT intranet_courrier_traitements_action_check
            CHECK (action IN ('cree','assigne','reassigne','commente','accuse_reception','traite','archive','rouvert'))");
    }
};
