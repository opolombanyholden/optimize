<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_wiki_articles', function (Blueprint $table) {
            $table->string('extrait', 500)->nullable()->after('titre');
            $table->json('tags')->nullable()->after('is_epingle');
            $table->string('media_principal')->nullable()->after('tags');
            $table->enum('media_principal_type', ['image', 'video', 'document'])->nullable()->after('media_principal');
            $table->unsignedSmallInteger('temps_lecture')->default(0)->after('media_principal_type');
            $table->unsignedSmallInteger('version')->default(1)->after('temps_lecture');
            $table->foreignId('parent_id')->nullable()->after('version')
                  ->constrained('intranet_wiki_articles')->nullOnDelete();
            $table->unsignedSmallInteger('ordre')->default(0)->after('parent_id');

            $table->index('categorie_id');
            $table->index('parent_id');
        });

        Schema::table('intranet_wiki_categories', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('intranet_wiki_articles', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['categorie_id']);
            $table->dropIndex(['parent_id']);
            $table->dropColumn([
                'extrait', 'tags', 'media_principal', 'media_principal_type',
                'temps_lecture', 'version', 'parent_id', 'ordre',
            ]);
        });
        Schema::table('intranet_wiki_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
