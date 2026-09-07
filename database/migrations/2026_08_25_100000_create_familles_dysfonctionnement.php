<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('familles_dysfonctionnement', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('description', 1000)->nullable();
            $table->string('couleur', 20)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('typesdysfonctionnements', function (Blueprint $table) {
            $table->foreignId('famille_id')->nullable()->after('id')
                ->constrained('familles_dysfonctionnement')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('typesdysfonctionnements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('famille_id');
        });
        Schema::dropIfExists('familles_dysfonctionnement');
    }
};
