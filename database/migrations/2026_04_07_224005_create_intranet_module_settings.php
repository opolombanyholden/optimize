<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════
        // PARAMÈTRES PAR MODULE
        // Contrôle les fonctionnalités sociales et de publication
        // pour chaque module intranet (activable/désactivable
        // par l'administrateur)
        // ══════════════════════════════════════════════════════

        Schema::create('intranet_module_parametres', function (Blueprint $table) {
            $table->id();
            $table->string('module')->unique(); // slug : annonces, news, evenements, projets…

            // Fonctionnalités sociales
            $table->boolean('likes_actifs')->default(true);
            $table->boolean('commentaires_actifs')->default(true);
            $table->boolean('partage_actif')->default(false);

            // Publication & visibilité
            $table->boolean('ciblage_users')->default(true);     // cibler des utilisateurs
            $table->boolean('ciblage_groupes')->default(true);   // cibler des groupes
            $table->boolean('ciblage_entites')->default(true);   // cibler des entités
            $table->boolean('peut_etre_public')->default(true);  // peut être visible par tous

            // Notifications
            $table->boolean('notif_publication')->default(true); // notifier à la publication
            $table->boolean('notif_commentaire')->default(true); // notifier sur nouveau commentaire
            $table->boolean('notif_like')->default(false);       // notifier sur like

            // Divers
            $table->boolean('moderation_commentaires')->default(false); // modération avant publication
            $table->boolean('actif')->default(true);             // module activé

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_module_parametres');
    }
};
