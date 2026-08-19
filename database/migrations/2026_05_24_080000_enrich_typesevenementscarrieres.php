<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('typesevenementscarrieres', function (Blueprint $table) {
            if (!Schema::hasColumn('typesevenementscarrieres', 'code')) {
                $table->string('code', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('typesevenementscarrieres', 'ordre')) {
                $table->integer('ordre')->default(0);
            }
            if (!Schema::hasColumn('typesevenementscarrieres', 'statut')) {
                $table->smallInteger('statut')->default(1);
            }
        });

        // Populer les codes pour les éventuelles données existantes
        $rows = DB::table('typesevenementscarrieres')->whereNull('code')->get();
        foreach ($rows as $r) {
            DB::table('typesevenementscarrieres')->where('id', $r->id)->update([
                'code' => strtoupper(Str::slug($r->libelle ?? ('TYPE_' . $r->id), '_')),
            ]);
        }

        // Index + unique sur code (après remplissage)
        Schema::table('typesevenementscarrieres', function (Blueprint $table) {
            $table->string('code', 50)->nullable(false)->change();
            $table->unique('code');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::table('typesevenementscarrieres', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropIndex(['statut']);
            $table->dropColumn(['code', 'ordre', 'statut']);
        });
    }
};
