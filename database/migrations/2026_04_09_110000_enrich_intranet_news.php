<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_news', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
            $table->string('extrait', 500)->nullable()->after('slug');
            $table->string('rubrique', 80)->nullable()->after('extrait');
            $table->json('tags')->nullable()->after('rubrique');
            $table->string('media_principal')->nullable()->after('image');
            $table->enum('media_principal_type', ['image', 'video', 'document'])->nullable()->after('media_principal');
            $table->date('date_debut')->nullable()->after('media_principal_type');
            $table->date('date_fin')->nullable()->after('date_debut');
            $table->boolean('a_la_une')->default(false)->after('date_fin');
            $table->string('auteur_signature')->nullable()->after('a_la_une');
            $table->string('source')->nullable()->after('auteur_signature');
            $table->unsignedSmallInteger('temps_lecture')->default(0)->after('source'); // minutes
            $table->string('couleur', 20)->default('#7C3AED')->after('temps_lecture');
            $table->unsignedInteger('vues_count')->default(0)->after('couleur');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('intranet_news', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'slug', 'extrait', 'rubrique', 'tags',
                'media_principal', 'media_principal_type',
                'date_debut', 'date_fin', 'a_la_une',
                'auteur_signature', 'source', 'temps_lecture',
                'couleur', 'vues_count',
            ]);
        });
    }
};
