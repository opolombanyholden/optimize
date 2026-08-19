<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = ['type_contrats', 'postes', 'departements'];
        foreach ($tables as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->string('code', 50)->unique();
                $t->string('libelle', 255);
                $t->text('description')->nullable();
                $t->integer('ordre')->default(0);
                $t->smallInteger('statut')->default(1); // 0=inactif, 1=actif
                $t->jsonb('extra_attributes')->nullable();
                $t->softDeletes();
                $t->timestamps();

                $t->index('statut');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('type_contrats');
        Schema::dropIfExists('postes');
        Schema::dropIfExists('departements');
    }
};
