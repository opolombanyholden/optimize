<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Les colonnes salaire_base et nombre_enfants étaient NOT NULL avec default 0.
        // Le formulaire RH les expose comme optionnelles : on les rend nullable
        // pour cohérence (sinon validation passe mais INSERT échoue avec null).
        DB::statement('ALTER TABLE employees ALTER COLUMN salaire_base DROP NOT NULL');
        DB::statement('ALTER TABLE employees ALTER COLUMN nombre_enfants DROP NOT NULL');
    }

    public function down(): void
    {
        // On remet NOT NULL, en s'assurant qu'aucune valeur null ne reste
        DB::statement("UPDATE employees SET salaire_base = 0 WHERE salaire_base IS NULL");
        DB::statement("UPDATE employees SET nombre_enfants = 0 WHERE nombre_enfants IS NULL");
        DB::statement('ALTER TABLE employees ALTER COLUMN salaire_base SET NOT NULL');
        DB::statement('ALTER TABLE employees ALTER COLUMN nombre_enfants SET NOT NULL');
    }
};
