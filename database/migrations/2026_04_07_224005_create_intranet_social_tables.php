<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════
        // GROUPES D'UTILISATEURS
        // Permettent de cibler un ensemble de collaborateurs
        // ══════════════════════════════════════════════════════

        Schema::create('intranet_groupes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('couleur', 20)->default('#4F46E5');
            $table->string('icone')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('intranet_groupe_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('groupe_id')->constrained('intranet_groupes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('membre'); // membre, animateur
            $table->timestamps();
            $table->unique(['groupe_id', 'user_id']);
        });

        // ══════════════════════════════════════════════════════
        // PUBLICATIONS — système de ciblage universel
        // Polymorphique : s'applique à TOUT contenu intranet.
        // Chaque contenu peut avoir une entrée publication
        // décrivant sa visibilité et ses options sociales.
        // ══════════════════════════════════════════════════════

        Schema::create('intranet_publications', function (Blueprint $table) {
            $table->id();

            // Contenu source (polymorphique)
            $table->morphs('publishable'); // publishable_type + publishable_id + index

            // Visibilité globale
            $table->enum('visibilite', [
                'public',     // tous les utilisateurs connectés
                'prive',      // uniquement les cibles définies
                'brouillon',  // non publié
            ])->default('prive');

            // Options sociales
            $table->boolean('likes_actifs')->default(false);
            $table->boolean('commentaires_actifs')->default(false);
            $table->boolean('partage_actif')->default(false);

            // Planification
            $table->timestamp('publie_le')->nullable();
            $table->timestamp('expire_le')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════
        // CIBLES DE PUBLICATION
        // Définit à qui est destinée une publication (mode prive)
        // Cible possible : user | groupe | entite (organisation ERP)
        // ══════════════════════════════════════════════════════

        Schema::create('intranet_publication_cibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')
                  ->constrained('intranet_publications')
                  ->cascadeOnDelete();

            // Type de cible : 'user' | 'groupe' | 'entite'
            $table->string('cible_type');
            $table->unsignedBigInteger('cible_id');

            $table->index(['cible_type', 'cible_id']);
            $table->unique(['publication_id', 'cible_type', 'cible_id'], 'pub_cible_unique');
        });

        // ══════════════════════════════════════════════════════
        // LIKES — polymorphique
        // Un user ne peut liker qu'une fois le même contenu
        // ══════════════════════════════════════════════════════

        Schema::create('intranet_likes', function (Blueprint $table) {
            $table->id();
            $table->morphs('likeable'); // likeable_type + likeable_id + index
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            // Contrainte unicité : 1 like par user par contenu
            $table->unique(['likeable_type', 'likeable_id', 'user_id'], 'like_unique');
        });

        // ══════════════════════════════════════════════════════
        // COMMENTAIRES — polymorphique + fil de discussion
        // Supporte les réponses imbriquées (parent_id)
        // ══════════════════════════════════════════════════════

        Schema::create('intranet_commentaires', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable'); // commentable_type + commentable_id + index
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('contenu');
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('intranet_commentaires')
                  ->cascadeOnDelete();
            $table->boolean('est_epingle')->default(false);
            $table->timestamp('modifie_le')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════════
        // VUES / LECTURES — polymorphique
        // Traçabilité des contenus consultés par utilisateur
        // ══════════════════════════════════════════════════════

        Schema::create('intranet_vues', function (Blueprint $table) {
            $table->id();
            $table->morphs('viewable'); // viewable_type + viewable_id
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('vu_le')->useCurrent();

            $table->unique(['viewable_type', 'viewable_id', 'user_id'], 'vue_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_vues');
        Schema::dropIfExists('intranet_commentaires');
        Schema::dropIfExists('intranet_likes');
        Schema::dropIfExists('intranet_publication_cibles');
        Schema::dropIfExists('intranet_publications');
        Schema::dropIfExists('intranet_groupe_user');
        Schema::dropIfExists('intranet_groupes');
    }
};
