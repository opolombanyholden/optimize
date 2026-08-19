<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_ressources', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->enum('type', ['dossier', 'fichier'])->default('fichier')->after('description');
            $table->string('chemin')->nullable()->after('type');
            $table->string('nom_original')->nullable()->after('chemin');
            $table->string('mime_type', 120)->nullable()->after('nom_original');
            $table->unsignedBigInteger('taille')->default(0)->after('mime_type');
            $table->string('categorie', 80)->nullable()->after('taille'); // RH, Finance, Technique, Juridique…
            $table->json('tags')->nullable()->after('categorie');
            $table->string('icone')->nullable()->after('tags');
            $table->boolean('acces_restreint')->default(false)->after('is_public');
            $table->unsignedSmallInteger('version')->default(1)->after('acces_restreint');
            $table->unsignedInteger('telechargements_count')->default(0)->after('version');
            $table->unsignedInteger('vues_count')->default(0)->after('telechargements_count');
            $table->softDeletes();

            $table->index('parent_id');
            $table->index('type');
            $table->index('categorie');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_ressources', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['categorie']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'slug', 'type', 'chemin', 'nom_original', 'mime_type', 'taille',
                'categorie', 'tags', 'icone', 'acces_restreint',
                'version', 'telechargements_count', 'vues_count',
            ]);
        });
    }
};
