<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_courriers', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->string('extrait', 500)->nullable()->after('objet');

            $table->string('destinataire')->nullable()->after('expediteur');
            $table->string('destinataire_email')->nullable()->after('destinataire');
            $table->string('expediteur_email')->nullable()->after('destinataire_email');
            $table->string('expediteur_organisation')->nullable()->after('expediteur_email');

            $table->foreignId('service_destinataire_id')->nullable()->after('expediteur_organisation')
                  ->constrained('intranet_services')->nullOnDelete();
            $table->foreignId('service_expediteur_id')->nullable()->after('service_destinataire_id')
                  ->constrained('intranet_services')->nullOnDelete();

            $table->foreignId('assigne_a')->nullable()->after('service_expediteur_id')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('assigne_le')->nullable()->after('assigne_a');
            $table->foreignId('assigne_par')->nullable()->after('assigne_le')
                  ->constrained('users')->nullOnDelete();

            $table->timestamp('traite_le')->nullable()->after('assigne_par');
            $table->foreignId('traite_par')->nullable()->after('traite_le')
                  ->constrained('users')->nullOnDelete();
            $table->date('echeance_traitement')->nullable()->after('traite_par');

            $table->boolean('accuse_reception')->default(false)->after('echeance_traitement');
            $table->timestamp('accuse_reception_le')->nullable()->after('accuse_reception');
            $table->string('accuse_reception_methode', 50)->nullable()->after('accuse_reception_le');
            $table->string('accuse_reception_scan')->nullable()->after('accuse_reception_methode');

            $table->boolean('confidentiel')->default(false)->after('accuse_reception_scan');
            $table->boolean('urgent')->default(false)->after('confidentiel');

            $table->string('media_principal')->nullable()->after('urgent');
            $table->enum('media_principal_type', ['image', 'video', 'document'])->nullable()->after('media_principal');

            $table->unsignedInteger('vues_count')->default(0)->after('media_principal_type');

            $table->softDeletes();

            $table->index('assigne_a');
            $table->index('statut');
            $table->index(['type', 'statut']);
        });

        // Journal de traitement
        Schema::create('intranet_courrier_traitements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courrier_id')->constrained('intranet_courriers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', [
                'cree', 'assigne', 'reassigne', 'commente',
                'accuse_reception', 'traite', 'archive', 'rouvert',
            ]);
            $table->text('commentaire')->nullable();
            $table->json('meta')->nullable(); // ex: ['ancien_assigne'=>3, 'nouveau_assigne'=>5]
            $table->timestamps();

            $table->index('courrier_id');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_courrier_traitements');

        Schema::table('intranet_courriers', function (Blueprint $table) {
            $table->dropForeign(['service_destinataire_id']);
            $table->dropForeign(['service_expediteur_id']);
            $table->dropForeign(['assigne_a']);
            $table->dropForeign(['assigne_par']);
            $table->dropForeign(['traite_par']);
            $table->dropIndex(['assigne_a']);
            $table->dropIndex(['statut']);
            $table->dropIndex(['type', 'statut']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'slug', 'extrait', 'destinataire', 'destinataire_email',
                'expediteur_email', 'expediteur_organisation',
                'service_destinataire_id', 'service_expediteur_id',
                'assigne_a', 'assigne_le', 'assigne_par',
                'traite_le', 'traite_par', 'echeance_traitement',
                'accuse_reception', 'accuse_reception_le',
                'accuse_reception_methode', 'accuse_reception_scan',
                'confidentiel', 'urgent',
                'media_principal', 'media_principal_type', 'vues_count',
            ]);
        });
    }
};
