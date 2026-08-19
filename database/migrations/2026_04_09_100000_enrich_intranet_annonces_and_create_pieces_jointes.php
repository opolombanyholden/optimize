<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════
        // ENRICHISSEMENT DE LA TABLE intranet_annonces
        // Ajout : slug, extrait, catégorie, média principal,
        // dates de publication, épingle, couleur, vues
        // ══════════════════════════════════════════════════════
        Schema::table('intranet_annonces', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
            $table->string('extrait', 500)->nullable()->after('slug');
            $table->string('categorie', 80)->nullable()->after('extrait');
            $table->string('media_principal')->nullable()->after('image');
            $table->enum('media_principal_type', ['image', 'video', 'document'])->nullable()->after('media_principal');
            $table->date('date_debut')->nullable()->after('media_principal_type');
            $table->date('date_fin')->nullable()->after('date_debut');
            $table->boolean('epingle')->default(false)->after('date_fin');
            $table->string('couleur', 20)->default('#7C3AED')->after('epingle');
            $table->unsignedInteger('vues_count')->default(0)->after('couleur');
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════════
        // PIÈCES JOINTES — table polymorphique réutilisable
        // S'attache à : annonce, news, événement, projet, tâche…
        // ══════════════════════════════════════════════════════
        Schema::create('intranet_pieces_jointes', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // attachable_type + attachable_id
            $table->string('nom_original');
            $table->string('chemin');
            $table->string('type_mime', 120)->nullable();
            $table->unsignedBigInteger('taille')->default(0); // bytes
            $table->enum('categorie', ['image', 'video', 'document', 'audio', 'autre'])->default('autre');
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_pieces_jointes');

        Schema::table('intranet_annonces', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'slug', 'extrait', 'categorie',
                'media_principal', 'media_principal_type',
                'date_debut', 'date_fin',
                'epingle', 'couleur', 'vues_count',
            ]);
        });
    }
};
