<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Correctif : la table `configs` existe déjà (module Intranet).
 * On crée une table dédiée `finance_configs` pour le paramétrage Finance V2.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('finance_configs')) {
            Schema::create('finance_configs', function (Blueprint $t) {
                $t->id();
                $t->string('key')->unique();
                $t->string('value')->nullable();
                $t->json('config_object')->nullable();
                $t->json('extra')->nullable();
                $t->softDeletes();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_configs');
    }
};
