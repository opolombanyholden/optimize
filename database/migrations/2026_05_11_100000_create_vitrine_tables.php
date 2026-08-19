<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Slides du hero animé
        Schema::create('vitrine_slides_hero', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable();           // Petit badge au-dessus du titre
            $table->string('eyebrow_icone', 50)->nullable();
            $table->string('eyebrow_couleur', 20)->nullable();
            $table->string('titre');                          // Peut contenir <span class="highlight">...</span>
            $table->text('sous_titre')->nullable();
            $table->string('cta_texte')->default('Demander une démo');
            $table->string('cta_url')->default('#contact');
            $table->json('stats')->nullable();                // [{num: '7', label: 'Modules'}, ...]
            $table->string('image_path')->nullable();         // Image uploadée
            $table->string('mockup_type', 30)->nullable();    // 'dashboard', 'wbs', 'okr', 'validation' (ou null si image)
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
        });

        // Modules présentés
        Schema::create('vitrine_modules', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('icone', 50)->default('fa-cube');     // FontAwesome
            $table->string('couleur', 20)->default('#0D9488');
            $table->json('features')->nullable();                 // ['Feature 1', 'Feature 2', 'Feature 3']
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
        });

        // Captures d'écran
        Schema::create('vitrine_captures', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('tag', 50)->nullable();             // 'PMP / WBS', 'Tâches', etc.
            $table->string('tag_couleur', 20)->nullable();
            $table->string('url_affichee')->nullable();        // ex : '/projet/5/wbs'
            $table->string('image_path')->nullable();
            $table->string('mockup_type', 30)->nullable();     // 'dashboard', 'wbs', 'kanban', 'annuaire', 'okr', 'validation'
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
        });

        // Atouts (section "Pourquoi OptimiZe")
        Schema::create('vitrine_atouts', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('icone', 50)->default('fa-star');
            $table->string('gradient_from', 20)->default('#0D9488');
            $table->string('gradient_to', 20)->default('#0F766E');
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
        });

        // Paramètres généraux de la vitrine (key/value)
        Schema::create('vitrine_settings', function (Blueprint $table) {
            $table->id();
            $table->string('cle', 80)->unique();
            $table->text('valeur')->nullable();
            $table->string('libelle');                          // Pour l'affichage admin
            $table->string('groupe', 50)->default('general');   // general, hero, footer, contact, partenaires
            $table->string('type', 20)->default('text');        // text, textarea, json, color, url
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vitrine_settings');
        Schema::dropIfExists('vitrine_atouts');
        Schema::dropIfExists('vitrine_captures');
        Schema::dropIfExists('vitrine_modules');
        Schema::dropIfExists('vitrine_slides_hero');
    }
};
