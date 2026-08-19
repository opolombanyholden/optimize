<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refonte Finance V2 — Alignement au schéma initial OPTIMIZE Finance.
 *
 * Fondations (Étape 1) :
 *  1. sources           — sources de financement (FP, RB, ETAT…) — CONFIGURABLES
 *  2. configs           — paramétrage clé/valeur avec JSON objet
 *  3. extra_fields      — champs personnalisés dynamiques par modèle
 *  4. Enrichissement entites (label, description, code, extra si absents)
 *  5. Enrichissement titres (label = libelle, code = imputation, extra)
 *  6. Enrichissement lignes (code = nature, label, status, type, extra)
 */
return new class extends Migration {

    public function up(): void
    {
        // ─── 1. SOURCES (sources de financement) ────────────
        if (!Schema::hasTable('sources')) {
            Schema::create('sources', function (Blueprint $t) {
                $t->id();
                $t->string('code')->unique();
                $t->string('label');
                $t->text('description')->nullable();
                $t->json('extra')->nullable();
                $t->softDeletes();
                $t->timestamps();
            });
        }

        // ─── 2. CONFIGS (paramétrage clé/valeur) ────────────
        if (!Schema::hasTable('configs')) {
            Schema::create('configs', function (Blueprint $t) {
                $t->id();
                $t->string('key')->unique();
                $t->string('value')->nullable();
                $t->json('config_object')->nullable();
                $t->json('extra')->nullable();
                $t->softDeletes();
                $t->timestamps();
            });
        }

        // ─── 3. EXTRA_FIELDS (champs personnalisés dynamiques) ────────────
        if (!Schema::hasTable('extra_fields')) {
            Schema::create('extra_fields', function (Blueprint $t) {
                $t->id();
                $t->string('identifier');   // slug technique
                $t->string('name');         // libellé humain
                $t->string('type');         // text / number / date / select / boolean
                $t->string('model');        // classe : App\Models\Finance\Transaction, etc.
                $t->softDeletes();
                $t->timestamps();

                $t->index(['model', 'identifier']);
            });
        }

        // ─── 4. ENTITES — enrichissement ────────────────────
        Schema::table('entites', function (Blueprint $t) {
            if (!Schema::hasColumn('entites', 'code'))        $t->string('code')->nullable()->after('id');
            if (!Schema::hasColumn('entites', 'label'))       $t->string('label')->nullable()->after('code');
            if (!Schema::hasColumn('entites', 'description')) $t->string('description', 500)->nullable()->after('label');
            if (!Schema::hasColumn('entites', 'extra'))       $t->json('extra')->nullable();
            if (!Schema::hasColumn('entites', 'deleted_at'))  $t->softDeletes();
        });

        // ─── 5. TITRES — enrichissement (alignement CdC : code = imputation, label = libelle) ────────────
        Schema::table('titres', function (Blueprint $t) {
            if (!Schema::hasColumn('titres', 'code'))        $t->string('code')->nullable()->after('id')->comment('Imputation OHADA (ex: 60, 61…)');
            if (!Schema::hasColumn('titres', 'label'))       $t->string('label')->nullable()->after('code');
            if (!Schema::hasColumn('titres', 'extra'))       $t->json('extra')->nullable();
            if (!Schema::hasColumn('titres', 'deleted_at'))  $t->softDeletes();
        });

        // ─── 6. LIGNES — enrichissement (alignement CdC : code = nature, label = libelle, type d'entrée) ────────────
        Schema::table('lignes', function (Blueprint $t) {
            if (!Schema::hasColumn('lignes', 'code'))        $t->string('code')->nullable()->after('id')->comment('Nature (code OHADA : 7313, 601101…)');
            if (!Schema::hasColumn('lignes', 'label'))       $t->string('label')->nullable()->after('code');
            if (!Schema::hasColumn('lignes', 'status'))      $t->smallInteger('status')->default(0);
            if (!Schema::hasColumn('lignes', 'type'))        $t->smallInteger('type')->default(0)->comment('0=depense, 1=recette');
            if (!Schema::hasColumn('lignes', 'titre_id'))    $t->foreignId('titre_id')->nullable()->constrained('titres')->nullOnDelete();
            if (!Schema::hasColumn('lignes', 'extra'))       $t->json('extra')->nullable();
            if (!Schema::hasColumn('lignes', 'deleted_at'))  $t->softDeletes();
        });

        // ─── 7. COMPTES — enrichissement (label, extra) ────────────
        Schema::table('comptes', function (Blueprint $t) {
            if (!Schema::hasColumn('comptes', 'label'))      $t->string('label')->nullable()->after('code');
            if (!Schema::hasColumn('comptes', 'extra'))      $t->json('extra')->nullable();
            if (!Schema::hasColumn('comptes', 'deleted_at')) $t->softDeletes();
        });

        // ─── 8. MODE_REGLEMENTS — enrichissement label/extra/softdelete ────────────
        if (Schema::hasTable('mode_reglements')) {
            Schema::table('mode_reglements', function (Blueprint $t) {
                if (!Schema::hasColumn('mode_reglements', 'label'))      $t->string('label')->nullable()->after('code');
                if (!Schema::hasColumn('mode_reglements', 'extra'))      $t->json('extra')->nullable();
                if (!Schema::hasColumn('mode_reglements', 'deleted_at')) $t->softDeletes();
            });
        }

        // ─── 9. EXERCICES — enrichissement (alignement CdC : code, label, debut/fin, global, entite_id) ────────────
        Schema::table('exercices', function (Blueprint $t) {
            if (!Schema::hasColumn('exercices', 'code'))                 $t->string('code')->nullable()->after('id');
            if (!Schema::hasColumn('exercices', 'label'))                $t->string('label')->nullable()->after('code');
            if (!Schema::hasColumn('exercices', 'description'))          $t->text('description')->nullable();
            if (!Schema::hasColumn('exercices', 'budgetglobal'))         $t->decimal('budgetglobal', 14, 2)->default(0);
            if (!Schema::hasColumn('exercices', 'budgetglobalrestant'))  $t->decimal('budgetglobalrestant', 14, 2)->nullable();
            if (!Schema::hasColumn('exercices', 'debut'))                $t->date('debut')->nullable();
            if (!Schema::hasColumn('exercices', 'fin'))                  $t->date('fin')->nullable();
            if (!Schema::hasColumn('exercices', 'global'))               $t->boolean('global')->default(false);
            if (!Schema::hasColumn('exercices', 'status'))               $t->smallInteger('status')->default(0)->comment('0=planification, 1=execution, 2=cloturé');
            if (!Schema::hasColumn('exercices', 'entite_id'))            $t->foreignId('entite_id')->nullable()->constrained('entites')->nullOnDelete();
            if (!Schema::hasColumn('exercices', 'extra'))                $t->json('extra')->nullable();
            if (!Schema::hasColumn('exercices', 'deleted_at'))           $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_fields');
        Schema::dropIfExists('configs');
        Schema::dropIfExists('sources');
        // Colonnes enrichies : on ne les retire pas (données peuvent exister).
    }
};
