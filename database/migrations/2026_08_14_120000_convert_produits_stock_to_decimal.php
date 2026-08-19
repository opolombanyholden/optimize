<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // PostgreSQL : conversion in-place integer → decimal(12,3)
        DB::statement('ALTER TABLE produits ALTER COLUMN stock_actuel  TYPE numeric(12,3) USING stock_actuel::numeric');
        DB::statement('ALTER TABLE produits ALTER COLUMN stock_minimum TYPE numeric(12,3) USING stock_minimum::numeric');
        DB::statement('ALTER TABLE produits ALTER COLUMN stock_maximum TYPE numeric(12,3) USING stock_maximum::numeric');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE produits ALTER COLUMN stock_actuel  TYPE integer USING stock_actuel::integer');
        DB::statement('ALTER TABLE produits ALTER COLUMN stock_minimum TYPE integer USING stock_minimum::integer');
        DB::statement('ALTER TABLE produits ALTER COLUMN stock_maximum TYPE integer USING stock_maximum::integer');
    }
};
