<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('natures_intervention', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('libelle');
            $table->string('description', 1000)->nullable();
            $table->string('couleur', 20)->nullable();
            $table->boolean('actif')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('interventions', function (Blueprint $table) {
            $table->foreignId('nature_id')->nullable()->after('type_intervention')
                ->constrained('natures_intervention')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('nature_id');
        });
        Schema::dropIfExists('natures_intervention');
    }
};
