<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Emplacements hiérarchiques (magasin → zone → étagère → case)
        Schema::create('emplacements', function (Blueprint $t) {
            $t->id();
            $t->string('code', 30)->unique();
            $t->string('libelle');
            $t->foreignId('parent_id')->nullable()->constrained('emplacements')->nullOnDelete();
            $t->string('type', 30)->nullable()->comment('magasin | zone | etagere | case | autre');
            $t->text('adresse')->nullable();
            $t->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('description')->nullable();
            $t->boolean('actif')->default(true);
            $t->integer('ordre')->default(0);
            $t->timestamps();
            $t->softDeletes();
            $t->index(['parent_id', 'ordre']);
        });

        // Pivot produit × emplacement : quantité en stock par emplacement
        Schema::create('produit_emplacements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $t->foreignId('emplacement_id')->constrained('emplacements')->cascadeOnDelete();
            $t->decimal('quantite', 12, 3)->default(0);
            $t->timestamps();
            $t->unique(['produit_id', 'emplacement_id'], 'produit_emp_unique');
            $t->index('emplacement_id');
        });

        // Enrichir produit_mouvements : emplacement destination (entrée/ajust) + source (sortie/transfert)
        Schema::table('produit_mouvements', function (Blueprint $t) {
            if (!Schema::hasColumn('produit_mouvements', 'emplacement_id')) {
                $t->foreignId('emplacement_id')->nullable()->after('quantite')->constrained('emplacements')->nullOnDelete();
            }
            if (!Schema::hasColumn('produit_mouvements', 'emplacement_source_id')) {
                $t->foreignId('emplacement_source_id')->nullable()->after('emplacement_id')->constrained('emplacements')->nullOnDelete();
            }
        });

        // commande_lignes (fournisseur) : emplacement de destination pour la réception
        Schema::table('commande_lignes', function (Blueprint $t) {
            if (!Schema::hasColumn('commande_lignes', 'emplacement_id')) {
                $t->foreignId('emplacement_id')->nullable()->after('produit_id')->constrained('emplacements')->nullOnDelete();
            }
        });

        // commande_interne_lignes : emplacement source pour la sortie
        Schema::table('commande_interne_lignes', function (Blueprint $t) {
            if (!Schema::hasColumn('commande_interne_lignes', 'emplacement_source_id')) {
                $t->foreignId('emplacement_source_id')->nullable()->after('produit_id')->constrained('emplacements')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('commande_interne_lignes', fn (Blueprint $t) => Schema::hasColumn('commande_interne_lignes', 'emplacement_source_id') ? $t->dropConstrainedForeignId('emplacement_source_id') : null);
        Schema::table('commande_lignes', fn (Blueprint $t) => Schema::hasColumn('commande_lignes', 'emplacement_id') ? $t->dropConstrainedForeignId('emplacement_id') : null);
        Schema::table('produit_mouvements', function (Blueprint $t) {
            foreach (['emplacement_id', 'emplacement_source_id'] as $c) {
                if (Schema::hasColumn('produit_mouvements', $c)) $t->dropConstrainedForeignId($c);
            }
        });
        Schema::dropIfExists('produit_emplacements');
        Schema::dropIfExists('emplacements');
    }
};
