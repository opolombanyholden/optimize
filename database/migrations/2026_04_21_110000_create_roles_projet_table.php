<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Table des rôles projet (paramétrable)
        Schema::create('intranet_roles_projet', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('code', 50)->unique();
            $table->string('couleur', 20)->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
        });

        // Seed des rôles par défaut
        DB::table('intranet_roles_projet')->insert([
            ['libelle' => 'Chef de projet',    'code' => 'chef',         'couleur' => '#0D9488', 'ordre' => 1, 'est_actif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['libelle' => 'Co-chef de projet', 'code' => 'co_chef',     'couleur' => '#0891B2', 'ordre' => 2, 'est_actif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['libelle' => 'Membre',            'code' => 'membre',      'couleur' => '#6366F1', 'ordre' => 3, 'est_actif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['libelle' => 'Expert',            'code' => 'expert',      'couleur' => '#7C3AED', 'ordre' => 4, 'est_actif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['libelle' => 'Observateur',       'code' => 'observateur', 'couleur' => '#94A3B8', 'ordre' => 5, 'est_actif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['libelle' => 'Sponsor',           'code' => 'sponsor',     'couleur' => '#D97706', 'ordre' => 6, 'est_actif' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Convertir role_projet d'enum vers FK
        // 1. Ajouter la colonne FK
        Schema::table('intranet_projet_ressources', function (Blueprint $table) {
            $table->foreignId('role_projet_id')->nullable()->after('user_id')
                  ->constrained('intranet_roles_projet')->nullOnDelete();
        });

        // 2. Migrer les données existantes
        $mapping = DB::table('intranet_roles_projet')->pluck('id', 'code');
        foreach ($mapping as $code => $id) {
            DB::table('intranet_projet_ressources')
                ->where('role_projet', $code)
                ->update(['role_projet_id' => $id]);
        }

        // 3. Supprimer l'ancienne colonne enum
        Schema::table('intranet_projet_ressources', function (Blueprint $table) {
            $table->dropColumn('role_projet');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_projet_ressources', function (Blueprint $table) {
            $table->string('role_projet')->nullable()->after('user_id');
        });

        $mapping = DB::table('intranet_roles_projet')->pluck('code', 'id');
        foreach ($mapping as $id => $code) {
            DB::table('intranet_projet_ressources')
                ->where('role_projet_id', $id)
                ->update(['role_projet' => $code]);
        }

        Schema::table('intranet_projet_ressources', function (Blueprint $table) {
            $table->dropForeign(['role_projet_id']);
            $table->dropColumn('role_projet_id');
        });

        Schema::dropIfExists('intranet_roles_projet');
    }
};
