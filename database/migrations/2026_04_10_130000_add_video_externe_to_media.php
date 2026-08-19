<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_media', function (Blueprint $table) {
            $table->string('url_externe')->nullable()->after('fichier');
            $table->string('plateforme', 30)->nullable()->after('url_externe'); // youtube, dailymotion, vimeo, autre
            $table->string('embed_id', 100)->nullable()->after('plateforme');
            $table->string('thumbnail_url')->nullable()->after('embed_id');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_media', function (Blueprint $table) {
            $table->dropColumn(['url_externe', 'plateforme', 'embed_id', 'thumbnail_url']);
        });
    }
};
