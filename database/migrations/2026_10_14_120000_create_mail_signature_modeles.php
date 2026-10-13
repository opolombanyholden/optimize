<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Signatures personnalisables en HTML/CSS + modèles d'entreprise applicables à tous.
 * - source_html / source_css : code saisi (réédition) ; contenu : HTML compilé (CSS en ligne) et nettoyé.
 * - Une signature liée à un modèle (modele_id) est calculée à la volée avec les données de l'utilisateur.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('mail_signature_modeles', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100)->unique();
            $table->text('source_html');
            $table->text('source_css')->nullable();
            $table->text('contenu');                 // compilé + nettoyé, variables {{…}} conservées
            $table->boolean('actif')->default(true);  // proposé aux utilisateurs
            $table->boolean('verrouille')->default(false); // les utilisateurs ne peuvent pas le retirer
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('mail_signatures', function (Blueprint $table) {
            $table->text('source_html')->nullable();
            $table->text('source_css')->nullable();
            $table->foreignId('modele_id')->nullable()->constrained('mail_signature_modeles')->cascadeOnDelete();
            $table->unique(['user_id', 'modele_id']);
        });
    }

    public function down(): void
    {
        Schema::table('mail_signatures', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'modele_id']);
            $table->dropConstrainedForeignId('modele_id');
            $table->dropColumn(['source_html', 'source_css']);
        });
        Schema::dropIfExists('mail_signature_modeles');
    }
};
