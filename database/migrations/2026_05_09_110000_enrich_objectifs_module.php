<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_objectifs', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->unique()->after('id');
            $table->string('couleur', 20)->nullable()->after('description');
            $table->string('icone', 50)->nullable()->after('couleur');
            $table->unsignedTinyInteger('ponderation')->nullable()->after('icone'); // 0-100 pour pondération dans parent
            $table->foreignId('responsable_id')->nullable()->after('parent_id')
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->after('responsable_id')
                  ->constrained('intranet_services')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('intranet_objectifs', function (Blueprint $table) {
            $table->dropForeign(['responsable_id']);
            $table->dropForeign(['service_id']);
            $table->dropColumn(['code', 'couleur', 'icone', 'ponderation', 'responsable_id', 'service_id']);
        });
    }
};
