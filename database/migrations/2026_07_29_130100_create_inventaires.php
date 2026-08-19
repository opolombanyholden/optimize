<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventaires', function (Blueprint $t) {
            $t->id();
            $t->string('numero', 30)->unique();
            $t->string('libelle');
            $t->date('date_prevue');
            $t->date('date_realisation')->nullable();
            $t->foreignId('emplacement_id')->nullable()->constrained('emplacements')->nullOnDelete()
              ->comment('Périmètre — null = tous emplacements');
            $t->foreignId('responsable_id')->nullable()->constrained('users');
            $t->tinyInteger('statut')->default(0)->comment('0=brouillon 1=en_cours 2=cloture 3=annule');
            $t->text('commentaire')->nullable();
            $t->timestamp('cloture_at')->nullable();
            $t->foreignId('cloture_par')->nullable()->constrained('users');
            $t->timestamps();
            $t->softDeletes();
            $t->index('statut');
        });

        Schema::create('inventaire_lignes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('inventaire_id')->constrained('inventaires')->cascadeOnDelete();
            $t->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $t->foreignId('emplacement_id')->nullable()->constrained('emplacements')->nullOnDelete();
            $t->decimal('quantite_theorique', 12, 3)->default(0);
            $t->decimal('quantite_reelle', 12, 3)->nullable();
            $t->decimal('ecart', 12, 3)->nullable()->comment('reelle - theorique');
            $t->text('commentaire')->nullable();
            $t->foreignId('compte_par')->nullable()->constrained('users');
            $t->timestamp('compte_at')->nullable();
            $t->timestamps();
            $t->index(['inventaire_id', 'produit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaire_lignes');
        Schema::dropIfExists('inventaires');
    }
};
