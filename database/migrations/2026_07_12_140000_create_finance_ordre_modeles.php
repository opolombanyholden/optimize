<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modèles paramétrables d'ordres de recette / ordonnances de paiement.
 *
 * Ces modèles définissent la PRÉSENTATION (entête, phrase d'intro, format
 * numérotation, sens comptable) des documents qu'un agent va émettre.
 * L'agent choisit un modèle et remplit un formulaire dynamique — dont les
 * champs et labels sont définis par le modèle (voir tables associées).
 *
 * Les données saisies sont ensuite matérialisées en écriture au `grand_livres`
 * avec le sens approprié (montant_signe_tc négatif pour dépense, positif pour recette).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('finance_ordre_modeles', function (Blueprint $t) {
            $t->id();
            $t->string('code', 60)->unique()->comment('Slug technique : ordre_recette, ordonnance_paiement…');
            $t->string('libelle')->comment('Nom affiché : "Ordre de recette", "Ordonnance de paiement"…');
            $t->string('sens', 10)->comment('depense | recette — détermine le signe du GL');
            $t->string('numerotation_format')->nullable()
                ->comment('Ex : "{n:04d}/BF/PR/ANPI-GABON/DG/DFMG/KAE"');
            $t->string('entete_titre')->nullable()->comment('Ex : "ORDRE DE RECETTE"');
            $t->string('entete_soustitre')->nullable()->comment('Ex : "AGENCE NATIONALE …"');
            $t->text('phrase_intro')->nullable()->comment('Ex : "L\'Agent Comptable est invité à prendre en charge…"');
            $t->text('phrase_conclusion')->nullable()->comment('Ex : "ARRETE LE PRESENT ORDRE À LA SOMME DE :"');
            $t->boolean('avec_mode_reglement')->default(false)->comment('Affiche le bloc Numéraire/Chèque/Virement');
            $t->boolean('avec_pieces_justif')->default(false)->comment('Affiche le bloc "Pièces justificatives"');
            $t->boolean('actif')->default(true);
            $t->json('extra_json')->nullable()->comment('Champs libres pour extension');
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('finance_ordre_modele_champs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('modele_id')->constrained('finance_ordre_modeles')->cascadeOnDelete();
            $t->string('code_champ', 60)->comment('Identifiant technique dans le formulaire, ex: imputation_budgetaire');
            $t->string('label_personnalise')->comment('Label affiché à l\'utilisateur, ex: "IMPUTATION BUDGETAIRE"');
            $t->string('type_saisie', 30)->default('text')
                ->comment('text | number | date | textarea | select | tiers | ligne_budgetaire');
            $t->json('options_json')->nullable()->comment('Pour type_saisie=select : liste des options');
            $t->string('mapping_gl', 60)->nullable()
                ->comment('Nom de colonne dans grand_livres pour le stockage (nullable = extra_json)');
            $t->boolean('obligatoire')->default(false);
            $t->integer('largeur_col')->default(12)->comment('Grille Bootstrap : 12, 6, 4, 3');
            $t->integer('ordre')->default(0);
            $t->string('placeholder')->nullable();
            $t->string('valeur_par_defaut')->nullable();
            $t->timestamps();

            $t->unique(['modele_id', 'code_champ'], 'uniq_modele_code_champ');
            $t->index(['modele_id', 'ordre']);
        });

        Schema::create('finance_ordre_modele_signataires', function (Blueprint $t) {
            $t->id();
            $t->foreignId('modele_id')->constrained('finance_ordre_modeles')->cascadeOnDelete();
            $t->string('role_libelle')->comment('Ex : "L\'AGENT COMPTABLE", "LE DIRECTEUR GENERAL"');
            $t->foreignId('user_id_par_defaut')->nullable()->constrained('users')->nullOnDelete();
            $t->integer('ordre')->default(0);
            $t->timestamps();

            $t->index(['modele_id', 'ordre']);
        });

        Schema::create('finance_ordres', function (Blueprint $t) {
            $t->id();
            $t->foreignId('modele_id')->constrained('finance_ordre_modeles');
            $t->string('numero_ordre')->unique()->comment('Généré selon numerotation_format du modèle');
            $t->foreignId('exercice_id')->constrained('exercices');
            $t->string('sens', 10)->comment('depense | recette (dénormalisé pour queries rapides)');
            $t->string('libelle')->nullable();
            $t->decimal('montant', 20, 2)->default(0);
            $t->tinyInteger('statut')->default(0)
                ->comment('0=brouillon, 1=soumis, 2=signé, 3=exécuté (dans GL), 4=annulé');
            $t->json('donnees_json')->nullable()->comment('Map code_champ → valeur saisie');
            $t->json('signataires_json')->nullable()->comment('Snapshot au moment de signature');
            $t->foreignId('grand_livre_id')->nullable()->constrained('grand_livres')->nullOnDelete()
                ->comment('Écriture matérialisée lors de l\'exécution');
            $t->foreignId('created_by')->constrained('users');
            $t->timestamp('soumis_at')->nullable();
            $t->foreignId('soumis_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('signe_at')->nullable();
            $t->foreignId('signe_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('execute_at')->nullable();
            $t->foreignId('execute_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('annule_at')->nullable();
            $t->foreignId('annule_par')->nullable()->constrained('users')->nullOnDelete();
            $t->text('motif_annulation')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['exercice_id', 'sens', 'statut'], 'idx_ordres_ex_sens_statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_ordres');
        Schema::dropIfExists('finance_ordre_modele_signataires');
        Schema::dropIfExists('finance_ordre_modele_champs');
        Schema::dropIfExists('finance_ordre_modeles');
    }
};
