<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercice extends Model
{
    protected $fillable = [
        'exercice', 'libelle', 'id_regroupement', 'dateeffect',
        'datedebut', 'datefin', 'entite', 'budgetglobalinitial',
        'commentaire', 'attribut1', 'attribut2', 'attribut3',
        'isvalide', 'id_user', 'effacer', 'version_exercice',
        'statut', 'dotationglobale', 'fondpropreglobal',
        'reportbudgetare', 'reporttresorerieglobal',
        // Workflow validation top management
        'validation_statut',
        'soumis_par', 'soumis_at',
        'valide_par', 'valide_at',
        'cloture_par', 'cloture_at',
        'annule_par', 'annule_at', 'motif_annulation',
        'motif_rejet',
        'type_planification',
    ];

    protected function casts(): array
    {
        return [
            'dateeffect' => 'datetime',
            'datedebut'  => 'datetime',
            'datefin'    => 'datetime',
            'entite'     => 'datetime',
            'soumis_at'  => 'datetime',
            'valide_at'  => 'datetime',
            'cloture_at' => 'datetime',
            'annule_at'  => 'datetime',
        ];
    }

    public const STATUT_PLANIFICATION = 1;
    public const STATUT_EN_EXECUTION  = 2;
    public const STATUT_CLOTURE       = 3;
    public const STATUT_ANNULE        = 4;

    public const STATUTS = [
        self::STATUT_PLANIFICATION => 'Planification',
        self::STATUT_EN_EXECUTION  => 'En exécution',
        self::STATUT_CLOTURE       => 'Clôturé',
        self::STATUT_ANNULE        => 'Annulé',
    ];

    // ─── Périmètre de planification ──────────────────────
    public const TYPE_DEPENSE = 'depense';
    public const TYPE_RECETTE = 'recette';
    public const TYPE_MIXTE   = 'mixte';

    public const TYPES_PLANIFICATION = [
        self::TYPE_DEPENSE => 'Dépenses uniquement (fonctionnement & investissement)',
        self::TYPE_RECETTE => 'Recettes uniquement',
        self::TYPE_MIXTE   => 'Mixte (dépenses + recettes)',
    ];

    public const TYPE_PLANIFICATION_COULEURS = [
        self::TYPE_DEPENSE => 'danger',
        self::TYPE_RECETTE => 'success',
        self::TYPE_MIXTE   => 'primary',
    ];

    public const TYPE_PLANIFICATION_ICONES = [
        self::TYPE_DEPENSE => 'fa-arrow-trend-down',
        self::TYPE_RECETTE => 'fa-arrow-trend-up',
        self::TYPE_MIXTE   => 'fa-arrows-up-down',
    ];

    public const VALIDATION_STATUTS = [
        0 => 'Brouillon',
        1 => 'Soumis pour validation',
        2 => 'Validé',
        3 => 'Rejeté',
    ];

    public function user()        { return $this->belongsTo(User::class, 'id_user'); }
    public function budgetLignes(){ return $this->hasMany(BudgetLigne::class, 'id_exercicebudgetaire'); }
    public function grandLivres() { return $this->hasMany(GrandLivre::class, 'id_exercicebudgetaire'); }
    public function soumetteur()  { return $this->belongsTo(User::class, 'soumis_par'); }
    public function validateur()  { return $this->belongsTo(User::class, 'valide_par'); }
    public function cloturePar()  { return $this->belongsTo(User::class, 'cloture_par'); }
    public function annulePar()   { return $this->belongsTo(User::class, 'annule_par'); }

    // ─── Accessors / état métier ──────────────────────────
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? '—';
    }

    public function getStatutCouleurAttribute(): string
    {
        return match ((int) $this->statut) {
            self::STATUT_PLANIFICATION => 'info',
            self::STATUT_EN_EXECUTION  => 'success',
            self::STATUT_CLOTURE       => 'secondary',
            self::STATUT_ANNULE        => 'danger',
            default => 'light',
        };
    }

    // ─── Règles métier centralisées (utilisées par contrôleur ET policy) ───

    /**
     * Un exercice n'est modifiable QU'en planification brouillon (statut=1, validation=0).
     * Dès qu'il est soumis, validé, en exécution, clôturé ou annulé → verrouillé.
     */
    public function peutEtreModifie(): bool
    {
        return (int) $this->statut === self::STATUT_PLANIFICATION
            && (int) $this->validation_statut === 0;
    }

    /**
     * Un exercice ne peut être supprimé que s'il est en planification brouillon
     * ET qu'il n'a pas de lignes budgétaires. Un exercice en exécution, clôturé
     * ou annulé conserve sa trace pour l'audit.
     */
    public function peutEtreSupprime(): bool
    {
        return (int) $this->statut === self::STATUT_PLANIFICATION
            && (int) $this->validation_statut === 0
            && !$this->budgetLignes()->exists();
    }

    /**
     * Un exercice en cours d'exécution peut être annulé (arrêt anticipé
     * ou décision top management), avec motif obligatoire.
     */
    public function peutEtreAnnule(): bool
    {
        return (int) $this->statut === self::STATUT_EN_EXECUTION;
    }

    public function estActifEnExecution(): bool
    {
        return (int) $this->statut === self::STATUT_EN_EXECUTION;
    }

    public function getValidationLibelleAttribute(): string
    {
        return self::VALIDATION_STATUTS[$this->validation_statut] ?? '—';
    }

    public function getTypePlanificationLibelleAttribute(): string
    {
        return self::TYPES_PLANIFICATION[$this->type_planification] ?? '—';
    }

    public function getTypePlanificationCouleurAttribute(): string
    {
        return self::TYPE_PLANIFICATION_COULEURS[$this->type_planification] ?? 'secondary';
    }

    public function getTypePlanificationIconeAttribute(): string
    {
        return self::TYPE_PLANIFICATION_ICONES[$this->type_planification] ?? 'fa-arrows-up-down';
    }

    /**
     * Retourne les valeurs `type_ligne` de titres à inclure dans la planification
     * selon le type choisi pour cet exercice.
     * - depense → depense
     * - recette → recette
     * - mixte   → depense + recette + mixte (tout)
     *
     * @return string[]
     */
    public function typesLigneAcceptes(): array
    {
        return match ($this->type_planification) {
            self::TYPE_DEPENSE => ['depense'],
            self::TYPE_RECETTE => ['recette'],
            default            => ['depense', 'recette', 'mixte'],
        };
    }

    /**
     * Réconcilie la planification avec le type de périmètre courant :
     *  - Retire (soft) les BudgetLignes hors scope sans engagement.
     *  - Remet (annule le retrait) les BudgetLignes revenues dans le scope
     *    et qui avaient été retirées par une réconciliation précédente.
     *  - Signale les lignes hors scope AVEC engagement (elles ne peuvent
     *    pas être retirées → l'appelant doit prévenir l'utilisateur).
     *
     * @return array{retirees:int, remises:int, bloquees:\Illuminate\Support\Collection}
     */
    public function reconcilierAvecTypePlanification(): array
    {
        $typesAcceptes = $this->typesLigneAcceptes();
        $titreIdsAcceptes = \App\Models\Titre::whereIn('type_ligne', $typesAcceptes)->pluck('id')->all();

        $toutesBl = $this->budgetLignes()
            ->whereNotNull('id_codeanalytique')
            ->with('ligne')
            ->get();

        $retirees = 0;
        $remises  = 0;
        $bloquees = collect();

        foreach ($toutesBl as $bl) {
            $ligneRef = $bl->ligne;
            if (!$ligneRef) continue; // ligne libre ou orpheline
            $enScope = in_array($ligneRef->id_titre, $titreIdsAcceptes, true);

            if (!$enScope) {
                // hors scope : soit retirer, soit signaler comme bloquée
                if ((float) ($bl->engagement ?? 0) > 0) {
                    $bloquees->push($bl);
                } elseif (!$bl->retiree_de_planification) {
                    $bl->update(['retiree_de_planification' => true]);
                    $retirees++;
                }
            } else {
                // en scope : si retirée précédemment, on remet automatiquement
                if ($bl->retiree_de_planification) {
                    $bl->update(['retiree_de_planification' => false]);
                    $remises++;
                }
            }
        }

        return ['retirees' => $retirees, 'remises' => $remises, 'bloquees' => $bloquees];
    }

    /**
     * En attente de clôture : exercice en exécution avec date fin dépassée.
     */
    public function getEnAttenteClotureAttribute(): bool
    {
        return $this->statut == 2
            && $this->datefin
            && \Carbon\Carbon::parse($this->datefin)->isPast();
    }

    /**
     * Pendant la planification (statut=1) et tant que pas soumis, on peut modifier les lignes.
     */
    public function getPeutPlanifierAttribute(): bool
    {
        return $this->statut == 1 && (int) $this->validation_statut === 0;
    }

    public function getPeutSoumettreAttribute(): bool
    {
        return $this->statut == 1
            && (int) $this->validation_statut === 0
            && $this->budgetLignes()->exists();
    }

    public function getPeutValiderAttribute(): bool
    {
        return $this->statut == 1 && (int) $this->validation_statut === 1;
    }

    public function getPeutRejeterAttribute(): bool
    {
        return $this->statut == 1 && (int) $this->validation_statut === 1;
    }

    public function getPeutCloturerAttribute(): bool
    {
        return $this->statut == 2;
    }

    /**
     * Budget total cumulé de toutes les lignes de l'exercice.
     */
    public function getBudgetTotalAttribute(): float
    {
        return (float) $this->budgetLignes->sum(fn($l) => $l->budget_total);
    }

    /**
     * Matérialise dans `budget_lignes` toutes les lignes du référentiel qui ne sont
     * pas encore présentes pour cet exercice. Chaque ligne créée est initialisée
     * à 0 sur les quatre sources de financement (dotation, fonds propres, reports).
     *
     * Idempotent : les lignes déjà créées ne sont pas touchées (retirées comprises,
     * pour ne pas les ressusciter).
     *
     * Convention métier : `budget_lignes.id_codeanalytique` stocke la clé primaire
     * de `lignes` (nom historique trompeur — c'est bien la FK Ligne::id).
     *
     * @return int Nombre de lignes nouvellement créées.
     */
    public function initialiserPlanification(?int $userId = null): int
    {
        // On regarde les BudgetLignes existantes (incluant les retirées) pour ne pas les recréer
        $existantsPk = $this->budgetLignes()
            ->withoutGlobalScopes() // au cas où
            ->whereNotNull('id_codeanalytique')
            ->pluck('id_codeanalytique') // FK Ligne::id
            ->all();

        // Filtre selon le périmètre de l'exercice (depense / recette / mixte)
        $typesAcceptes = $this->typesLigneAcceptes();
        $titreIdsAcceptes = \App\Models\Titre::whereIn('type_ligne', $typesAcceptes)->pluck('id')->all();

        $lignesRef = \App\Models\Ligne::query()
            ->whereIn('id_titre', $titreIdsAcceptes)
            ->when(count($existantsPk) > 0, fn($q) => $q->whereNotIn('id', $existantsPk))
            ->get();

        $userId ??= $this->id_user ?? auth()->id();
        $ex     = $this->exercice ?: $this->id;
        $creees = 0;

        foreach ($lignesRef as $ligne) {
            BudgetLigne::create([
                'id_exercicebudgetaire'    => $this->id,
                'exercice'                 => $ex,
                'id_budgetligne'           => sprintf('BL-%s-%d', $ex, $ligne->id),
                'id_codeanalytique'        => $ligne->id,       // FK Ligne (convention métier)
                'id_famillecodeanalytique' => $ligne->id_titre,
                'commentaire'              => $ligne->libelle,
                'dotation_etat'            => 0,
                'fonds_propres'            => 0,
                'reports_budgetaire'       => 0,
                'reports_tresorerie'       => 0,
                'isvalide'                 => 0,
                'id_user'                  => $userId,
            ]);
            $creees++;
        }

        return $creees;
    }

    // ─── Scopes ───────────────────────────────────────────
    public function scopePlanifie($q) { return $q->where('statut', 1); }
    public function scopeEnCours($q)  { return $q->where('statut', 2); }
    public function scopeCloture($q)  { return $q->where('statut', 3); }
    public function scopeSoumis($q)   { return $q->where('validation_statut', 1); }
    public function scopeEnAttenteCloture($q)
    {
        return $q->where('statut', 2)->whereDate('datefin', '<', now()->toDateString());
    }
}
