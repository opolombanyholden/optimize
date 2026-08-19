<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_rapports', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->string('extrait', 500)->nullable()->after('titre');
            $table->string('reference', 50)->nullable()->after('type');
            $table->foreignId('projet_id')->nullable()->after('evenement_id')
                  ->constrained('intranet_projets')->nullOnDelete();
            $table->string('media_principal')->nullable()->after('date_document');
            $table->enum('media_principal_type', ['image', 'video', 'document'])->nullable()->after('media_principal');
            $table->json('tags')->nullable()->after('media_principal_type');
            $table->json('participants')->nullable()->after('tags'); // IDs des participants
            $table->unsignedInteger('vues_count')->default(0)->after('participants');

            $table->index('type');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_rapports', function (Blueprint $table) {
            $table->dropForeign(['projet_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['statut']);
            $table->dropColumn([
                'slug', 'extrait', 'reference', 'projet_id',
                'media_principal', 'media_principal_type',
                'tags', 'participants', 'vues_count',
            ]);
        });
    }
};
