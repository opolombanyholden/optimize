<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_media', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->string('album')->nullable()->after('dossier');
            $table->json('tags')->nullable()->after('album');
            $table->unsignedSmallInteger('largeur')->nullable()->after('tags');
            $table->unsignedSmallInteger('hauteur')->nullable()->after('largeur');
            $table->unsignedInteger('duree_secondes')->nullable()->after('hauteur');
            $table->unsignedInteger('telechargements_count')->default(0)->after('duree_secondes');
            $table->unsignedInteger('vues_count')->default(0)->after('telechargements_count');

            $table->index('type');
            $table->index('album');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_media', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['album']);
            $table->dropColumn([
                'slug', 'album', 'tags',
                'largeur', 'hauteur', 'duree_secondes',
                'telechargements_count', 'vues_count',
            ]);
        });
    }
};
