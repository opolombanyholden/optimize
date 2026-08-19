<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_evenements', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('titre');
            $table->string('extrait', 500)->nullable()->after('slug');
            $table->string('media_principal')->nullable()->after('description');
            $table->enum('media_principal_type', ['image', 'video', 'document'])->nullable()->after('media_principal');
            $table->string('couleur', 20)->nullable()->after('media_principal_type');
            $table->boolean('journee_entiere')->default(false)->after('date_fin');
            $table->enum('recurrence', ['aucune', 'quotidienne', 'hebdomadaire', 'mensuelle', 'annuelle'])->default('aucune')->after('journee_entiere');
            $table->date('recurrence_jusqu_au')->nullable()->after('recurrence');
            $table->unsignedBigInteger('parent_recurrence_id')->nullable()->after('recurrence_jusqu_au');
            $table->string('lieu_url')->nullable()->after('lieu');
            $table->boolean('est_visio')->default(false)->after('lieu_url');
            $table->string('lien_visio')->nullable()->after('est_visio');
            $table->unsignedSmallInteger('capacite_max')->nullable()->after('lien_visio');
            $table->boolean('inscription_requise')->default(false)->after('capacite_max');
            $table->unsignedSmallInteger('rappel_minutes')->nullable()->after('inscription_requise');
            $table->unsignedInteger('vues_count')->default(0)->after('rappel_minutes');

            $table->index('parent_recurrence_id');
            $table->index('date_debut');
        });

        // Pivot participants avec statut RSVP
        Schema::create('intranet_evenement_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('intranet_evenements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('statut', ['invite', 'confirme', 'decline', 'peut_etre'])->default('invite');
            $table->text('reponse')->nullable();
            $table->timestamp('repondu_le')->nullable();
            $table->timestamps();
            $table->unique(['evenement_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_evenement_participants');
        Schema::table('intranet_evenements', function (Blueprint $table) {
            $table->dropIndex(['parent_recurrence_id']);
            $table->dropIndex(['date_debut']);
            $table->dropColumn([
                'slug', 'extrait', 'media_principal', 'media_principal_type',
                'couleur', 'journee_entiere', 'recurrence', 'recurrence_jusqu_au',
                'parent_recurrence_id', 'lieu_url', 'est_visio', 'lien_visio',
                'capacite_max', 'inscription_requise', 'rappel_minutes', 'vues_count',
            ]);
        });
    }
};
