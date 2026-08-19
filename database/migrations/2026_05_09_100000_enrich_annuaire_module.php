<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enrichir intranet_services
        Schema::table('intranet_services', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->unique();
            $table->string('couleur', 20)->nullable();
            $table->string('icone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('telephone', 50)->nullable();
            $table->text('localisation')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('intranet_services')->nullOnDelete();
            $table->boolean('est_actif')->default(true);
            $table->softDeletes();
        });

        // Pivot user ↔ service (un user peut être dans plusieurs services)
        Schema::create('intranet_service_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('intranet_services')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('poste', 150)->nullable();           // Intitulé du poste dans ce service
            $table->boolean('est_principal')->default(false);   // Service principal du user
            $table->date('date_arrivee')->nullable();
            $table->date('date_depart')->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'user_id']);
            $table->index('user_id');
        });

        // Enrichir users avec champs annuaire
        Schema::table('users', function (Blueprint $table) {
            $table->string('poste', 150)->nullable()->after('matricule');
            $table->date('date_naissance')->nullable()->after('poste');
            $table->date('date_embauche')->nullable()->after('date_naissance');
            $table->string('bureau', 100)->nullable()->after('date_embauche');
            $table->text('bio')->nullable()->after('bureau');
            $table->string('linkedin')->nullable()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['poste', 'date_naissance', 'date_embauche', 'bureau', 'bio', 'linkedin']);
        });

        Schema::dropIfExists('intranet_service_user');

        Schema::table('intranet_services', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['code', 'couleur', 'icone', 'email', 'telephone', 'localisation', 'parent_id', 'est_actif', 'deleted_at']);
        });
    }
};
