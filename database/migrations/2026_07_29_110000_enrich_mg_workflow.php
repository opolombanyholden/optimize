<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Dysfonctionnements — colonnes workflow + auteurs actions
        Schema::table('dysfonctionnements', function (Blueprint $t) {
            foreach ([
                'pris_en_charge_par' => 'unsignedBigInteger',
                'pris_en_charge_at' => 'timestamp',
                'resolu_par' => 'unsignedBigInteger',
                'ferme_par' => 'unsignedBigInteger',
                'date_fermeture' => 'timestamp',
                'commentaire_resolution' => 'text',
            ] as $col => $type) {
                if (!Schema::hasColumn('dysfonctionnements', $col)) $t->{$type}($col)->nullable();
            }
        });

        // Interventions — colonnes workflow
        Schema::table('interventions', function (Blueprint $t) {
            foreach ([
                'demarree_par' => 'unsignedBigInteger',
                'terminee_par' => 'unsignedBigInteger',
                'annulee_par' => 'unsignedBigInteger',
                'annulee_at' => 'timestamp',
                'motif_annulation' => 'text',
            ] as $col => $type) {
                if (!Schema::hasColumn('interventions', $col)) $t->{$type}($col)->nullable();
            }
        });

        // Immobilisations — enrichissement amortissements
        Schema::table('immobilisations', function (Blueprint $t) {
            foreach ([
                'valeur_residuelle' => ['decimal', [20, 2], 0],
                'date_mise_en_service' => ['date', [], null],
                'date_sortie' => ['date', [], null],
                'motif_sortie' => ['string', [255], null],
                'amortissement_cumule' => ['decimal', [20, 2], 0],
                'derniere_dotation_at' => ['date', [], null],
            ] as $col => $def) {
                if (!Schema::hasColumn('immobilisations', $col)) {
                    [$type, $args, $default] = $def;
                    $column = empty($args) ? $t->{$type}($col) : $t->{$type}($col, ...$args);
                    $column->nullable();
                    if ($default !== null) $column->default($default);
                }
            }
        });

        // Historique des dotations d'amortissement (une ligne par période comptable)
        Schema::create('immobilisation_dotations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('immobilisation_id')->constrained('immobilisations')->cascadeOnDelete();
            $t->date('periode_debut');
            $t->date('periode_fin');
            $t->decimal('montant', 20, 2);
            $t->decimal('vnc_avant', 20, 2);
            $t->decimal('vnc_apres', 20, 2);
            $t->decimal('cumul_apres', 20, 2);
            $t->string('methode', 20)->default('lineaire');
            $t->foreignId('created_by')->nullable()->constrained('users');
            $t->timestamps();
            $t->unique(['immobilisation_id', 'periode_debut'], 'immo_dot_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immobilisation_dotations');
        Schema::table('dysfonctionnements', function (Blueprint $t) {
            foreach (['pris_en_charge_par','pris_en_charge_at','resolu_par','ferme_par','date_fermeture','commentaire_resolution'] as $c) {
                if (Schema::hasColumn('dysfonctionnements', $c)) $t->dropColumn($c);
            }
        });
        Schema::table('interventions', function (Blueprint $t) {
            foreach (['demarree_par','terminee_par','annulee_par','annulee_at','motif_annulation'] as $c) {
                if (Schema::hasColumn('interventions', $c)) $t->dropColumn($c);
            }
        });
        Schema::table('immobilisations', function (Blueprint $t) {
            foreach (['valeur_residuelle','date_mise_en_service','date_sortie','motif_sortie','amortissement_cumule','derniere_dotation_at'] as $c) {
                if (Schema::hasColumn('immobilisations', $c)) $t->dropColumn($c);
            }
        });
    }
};
