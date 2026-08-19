<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enrichir catégories
        Schema::table('intranet_template_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('nom');
            $table->string('couleur', 20)->default('#7C3AED')->after('icone');
            $table->text('description')->nullable()->after('couleur');
            $table->unsignedSmallInteger('ordre')->default(0)->after('description');
            $table->softDeletes();
        });

        // Enrichir templates
        Schema::table('intranet_templates', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->enum('format', ['html', 'fichier'])->default('html')->after('contenu');
            $table->string('fichier_modele')->nullable()->after('format'); // docx, xlsx uploadé
            $table->string('nom_original')->nullable()->after('fichier_modele');
            $table->string('mime_type', 120)->nullable()->after('nom_original');
            $table->unsignedBigInteger('taille')->default(0)->after('mime_type');
            $table->json('variables')->nullable()->after('taille'); // ["nom","date","entreprise"]
            $table->json('tags')->nullable()->after('variables');
            $table->unsignedInteger('vues_count')->default(0)->after('utilisations');

            $table->index('categorie_id');
            $table->index('format');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_templates', function (Blueprint $table) {
            $table->dropIndex(['categorie_id']);
            $table->dropIndex(['format']);
            $table->dropColumn([
                'slug', 'format', 'fichier_modele', 'nom_original',
                'mime_type', 'taille', 'variables', 'tags', 'vues_count',
            ]);
        });

        Schema::table('intranet_template_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['slug', 'couleur', 'description', 'ordre']);
        });
    }
};
