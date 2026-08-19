<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Familles hiérarchiques (une famille peut appartenir à une autre)
        Schema::create('familles_articles', function (Blueprint $t) {
            $t->id();
            $t->string('libelle');
            $t->string('code', 30)->nullable()->unique();
            $t->foreignId('parent_id')->nullable()->constrained('familles_articles')->nullOnDelete();
            $t->integer('ordre')->default(0);
            $t->text('description')->nullable();
            $t->boolean('actif')->default(true);
            $t->timestamps();
            $t->softDeletes();
            $t->index(['parent_id', 'ordre']);
        });

        // Enrichir produits → catalogue biens/services
        Schema::table('produits', function (Blueprint $t) {
            if (!Schema::hasColumn('produits', 'famille_id')) $t->foreignId('famille_id')->nullable()->after('sous_categorie')->constrained('familles_articles')->nullOnDelete();
            if (!Schema::hasColumn('produits', 'type_article')) $t->string('type_article', 10)->default('bien')->after('famille_id');
            if (!Schema::hasColumn('produits', 'est_stockable')) $t->boolean('est_stockable')->default(true)->after('type_article');
            if (!Schema::hasColumn('produits', 'seuil_alerte')) $t->decimal('seuil_alerte', 12, 3)->nullable()->after('stock_minimum');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $t) {
            foreach (['famille_id', 'type_article', 'est_stockable', 'seuil_alerte'] as $c) {
                if (Schema::hasColumn('produits', $c)) $t->dropColumn($c);
            }
        });
        Schema::dropIfExists('familles_articles');
    }
};
