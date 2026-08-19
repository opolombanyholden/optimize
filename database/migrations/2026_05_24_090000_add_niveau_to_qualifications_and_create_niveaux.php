<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Colonne niveau sur qualifications
        Schema::table('qualifications', function (Blueprint $table) {
            if (!Schema::hasColumn('qualifications', 'niveau')) {
                $table->string('niveau', 50)->nullable()->after('organisme');
                $table->index('niveau');
            }
        });

        // 2. Référentiel des niveaux de qualification
        Schema::create('niveaux_qualification', function (Blueprint $t) {
            $t->id();
            $t->string('code', 50)->unique();
            $t->string('libelle', 255);
            $t->text('description')->nullable();
            $t->integer('ordre')->default(0);
            $t->smallInteger('statut')->default(1);
            $t->jsonb('extra_attributes')->nullable();
            $t->softDeletes();
            $t->timestamps();

            $t->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('niveaux_qualification');
        Schema::table('qualifications', function (Blueprint $table) {
            $table->dropIndex(['niveau']);
            $table->dropColumn('niveau');
        });
    }
};
