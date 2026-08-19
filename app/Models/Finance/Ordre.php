<?php

namespace App\Models\Finance;

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\GrandLivre;
use App\Models\User;
use App\Traits\Intranet\HasPiecesJointes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ordre extends Model
{
    use SoftDeletes, HasPiecesJointes;

    protected $table = 'finance_ordres';

    protected $fillable = [
        'modele_id', 'numero_ordre', 'exercice_id', 'budget_ligne_id', 'compte_id', 'facture_id',
        'sens', 'libelle', 'montant',
        'statut', 'donnees_json', 'signataires_json', 'grand_livre_id',
        'created_by',
        'soumis_at', 'soumis_par',
        'signe_at', 'signe_par',
        'execute_at', 'execute_par',
        'annule_at', 'annule_par', 'motif_annulation',
        'beneficiaire_source', 'beneficiaire_ref_id', 'beneficiaire_infos_json',
    ];

    protected $casts = [
        'donnees_json'            => 'array',
        'signataires_json'        => 'array',
        'beneficiaire_infos_json' => 'array',
        'montant'                 => 'decimal:2',
        'statut'                  => 'integer',
        'soumis_at'               => 'datetime',
        'signe_at'                => 'datetime',
        'execute_at'              => 'datetime',
        'annule_at'               => 'datetime',
    ];

    public const BENEFICIAIRE_SOURCES = [
        'user'         => 'Utilisateur interne',
        'contact'      => 'Contact (CRM)',
        'organisation' => 'Organisation (CRM)',
        'externe'      => 'Externe (saisie manuelle)',
    ];

    public const STATUT_BROUILLON = 0;
    public const STATUT_SOUMIS    = 1;
    public const STATUT_SIGNE     = 2;
    public const STATUT_EXECUTE   = 3;
    public const STATUT_ANNULE    = 4;

    public const STATUTS = [
        self::STATUT_BROUILLON => 'Brouillon',
        self::STATUT_SOUMIS    => 'Soumis',
        self::STATUT_SIGNE     => 'Signé',
        self::STATUT_EXECUTE   => 'Exécuté (au grand livre)',
        self::STATUT_ANNULE    => 'Annulé',
    ];

    public const STATUT_COULEURS = [
        self::STATUT_BROUILLON => 'secondary',
        self::STATUT_SOUMIS    => 'info',
        self::STATUT_SIGNE     => 'primary',
        self::STATUT_EXECUTE   => 'success',
        self::STATUT_ANNULE    => 'danger',
    ];

    // ─── Relations ─────────────────────────────
    public function modele()      { return $this->belongsTo(OrdreModele::class, 'modele_id'); }
    public function exercice()    { return $this->belongsTo(Exercice::class, 'exercice_id'); }
    public function budgetLigne() { return $this->belongsTo(BudgetLigne::class, 'budget_ligne_id'); }
    public function compte()      { return $this->belongsTo(\App\Models\Compte::class, 'compte_id'); }
    public function facture()     { return $this->belongsTo(\App\Models\Facture::class, 'facture_id'); }
    public function grandLivre()  { return $this->belongsTo(GrandLivre::class, 'grand_livre_id'); }
    public function createur()    { return $this->belongsTo(User::class, 'created_by'); }
    public function soumetteur()  { return $this->belongsTo(User::class, 'soumis_par'); }
    public function signataire()  { return $this->belongsTo(User::class, 'signe_par'); }
    public function executeur()   { return $this->belongsTo(User::class, 'execute_par'); }
    public function annuleur()    { return $this->belongsTo(User::class, 'annule_par'); }

    public function details()
    {
        return $this->hasMany(OrdreDetail::class, 'ordre_id')->orderBy('ordre');
    }

    // ─── Accessors ─────────────────────────────
    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut] ?? '—'; }
    public function getStatutCouleurAttribute(): string { return self::STATUT_COULEURS[$this->statut] ?? 'secondary'; }

    public function getSensCouleurAttribute(): string
    {
        return $this->sens === 'depense' ? 'danger' : 'success';
    }

    /**
     * Résout dynamiquement le bénéficiaire depuis sa source + ref_id.
     * Retourne null pour un bénéficiaire externe (aucune entité liée).
     */
    public function beneficiaire()
    {
        if (!$this->beneficiaire_ref_id) return null;
        return match ($this->beneficiaire_source) {
            'user'         => \App\Models\User::find($this->beneficiaire_ref_id),
            'contact'      => \App\Models\Intranet\Contact::find($this->beneficiaire_ref_id),
            'organisation' => \App\Models\Intranet\ContactOrganisation::find($this->beneficiaire_ref_id),
            default        => null,
        };
    }

    /**
     * Libellé unifié du bénéficiaire, priorise l'entité résolue puis les infos snapshot.
     */
    public function getBeneficiaireLibelleAttribute(): ?string
    {
        $entite = $this->beneficiaire();
        if ($entite instanceof \App\Models\User) {
            return trim(($entite->prenoms ?? '') . ' ' . ($entite->name ?? ''));
        }
        if ($entite instanceof \App\Models\Intranet\Contact) {
            return $entite->nom_complet;
        }
        if ($entite instanceof \App\Models\Intranet\ContactOrganisation) {
            return $entite->nom_affichage;
        }
        return $this->beneficiaire_infos_json['nom'] ?? null;
    }

    public function getBeneficiaireLabelUiAttribute(): string
    {
        return $this->sens === 'depense' ? 'Bénéficiaire' : 'Client';
    }

    public function getMontantSigneAttribute(): float
    {
        return (float) ($this->sens === 'depense' ? -$this->montant : $this->montant);
    }

    /**
     * Convertit le montant en toutes lettres (français), avec la devise du compte.
     * Ex : 1 234 567 XAF → "un million deux cent trente-quatre mille cinq cent
     * soixante-sept francs CFA"
     * Utilisé sur la fiche show et dans le PDF (mention légale).
     */
    public function getMontantEnLettresAttribute(): string
    {
        $montant = (float) $this->montant;
        $entier  = (int) floor($montant);
        $decimal = (int) round(($montant - $entier) * 100);

        $devise = $this->compte?->devise ?: 'XAF';
        $libelleDevise = match ($devise) {
            'XAF'   => 'francs CFA',
            'EUR'   => 'euros',
            'USD'   => 'dollars US',
            default => $devise,
        };
        $libelleCentimes = match ($devise) {
            'XAF'   => 'centimes',
            'EUR'   => 'centimes',
            'USD'   => 'cents',
            default => 'centimes',
        };

        if (!class_exists(\NumberFormatter::class)) {
            // Fallback si intl indisponible
            return number_format($entier, 0, ',', ' ') . ' ' . $libelleDevise;
        }
        $fmt = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
        $texte = ucfirst($fmt->format($entier)) . ' ' . $libelleDevise;
        if ($decimal > 0) {
            $texte .= ' et ' . $fmt->format($decimal) . ' ' . $libelleCentimes;
        }
        return $texte;
    }

    /**
     * Somme des montants des détails ventilés.
     * Sert à vérifier la cohérence avec le `montant` total de l'ordre.
     */
    public function getMontantTotalDetailsAttribute(): float
    {
        return (float) $this->details->sum('montant');
    }

    public function getEcartDetailsAttribute(): float
    {
        return round((float) $this->montant - $this->montant_total_details, 2);
    }

    /**
     * Aligne le `montant` total sur la somme des détails.
     * À appeler après modification des détails.
     */
    public function reventiler(): void
    {
        $this->update(['montant' => $this->montant_total_details]);
    }

    /**
     * Rubriques disponibles pour la ligne budgétaire courante et le sens de l'ordre.
     */
    public function rubriquesDisponibles()
    {
        if (!$this->budgetLigne || !$this->budgetLigne->id_codeanalytique) return collect();

        return \App\Models\RubriqueOperation::query()
            ->actif()
            ->where('ligne_id', $this->budgetLigne->id_codeanalytique)
            ->where(function ($q) {
                $q->where('sens', $this->sens)->orWhere('sens', 'mixte');
            })
            ->orderBy('ordre_affichage')
            ->orderBy('libelle')
            ->get();
    }

    // ─── Workflow ──────────────────────────────
    public function peutEtreModifie(): bool { return $this->statut === self::STATUT_BROUILLON; }
    public function peutEtreSoumis(): bool  { return $this->statut === self::STATUT_BROUILLON; }
    public function peutEtreSigne(): bool   { return $this->statut === self::STATUT_SOUMIS; }
    public function peutEtreExecute(): bool { return $this->statut === self::STATUT_SIGNE; }
    public function peutEtreAnnule(): bool  { return in_array($this->statut, [self::STATUT_BROUILLON, self::STATUT_SOUMIS, self::STATUT_SIGNE]); }

    // ─── Scopes ────────────────────────────────
    public function scopeSens($q, string $sens) { return $q->where('sens', $sens); }
    public function scopeDepense($q)            { return $q->where('sens', 'depense'); }
    public function scopeRecette($q)            { return $q->where('sens', 'recette'); }
    public function scopeStatut($q, int $s)     { return $q->where('statut', $s); }
    public function scopeExercice($q, int $id)  { return $q->where('exercice_id', $id); }

    // ─── Workflow : transitions ────────────────
    public function soumettre(?int $userId = null): void
    {
        $this->update([
            'statut'     => self::STATUT_SOUMIS,
            'soumis_at'  => now(),
            'soumis_par' => $userId ?? auth()->id(),
        ]);
    }

    public function signer(?int $userId = null): void
    {
        // Snapshot des signataires au moment de la signature (traçabilité)
        $this->loadMissing('modele.signataires.userParDefaut');
        $snapshot = $this->modele->signataires->map(fn($s) => [
            'role_libelle' => $s->role_libelle,
            'user_id'      => $s->userParDefaut?->id,
            'user_nom'     => $s->userParDefaut?->name,
            'ordre'        => $s->ordre,
        ])->all();

        $this->update([
            'statut'           => self::STATUT_SIGNE,
            'signe_at'         => now(),
            'signe_par'        => $userId ?? auth()->id(),
            'signataires_json' => $snapshot,
        ]);
    }

    /**
     * Exécute l'ordre : crée l'écriture au Grand Livre et lie via `grand_livre_id`.
     * Doit être appelé dans une transaction pour garantir la cohérence.
     */
    public function executer(?int $userId = null): \App\Models\GrandLivre
    {
        $this->loadMissing(['modele.champs', 'exercice', 'budgetLigne.ligne']);
        $userId ??= auth()->id();

        $gl = $this->construireEcritureGrandLivre($userId);

        $this->update([
            'statut'          => self::STATUT_EXECUTE,
            'execute_at'      => now(),
            'execute_par'     => $userId,
            'grand_livre_id'  => $gl->id,
        ]);

        // Impacte le budget consommé de la ligne budgétaire (engagement)
        if ($this->budgetLigne) {
            $this->budgetLigne->increment('engagement', (float) $this->montant);
        }

        // Impacte le solde du compte trésorerie (dépense = débit, recette = crédit)
        if ($this->compte_id) {
            $delta = $this->sens === 'depense' ? -((float) $this->montant) : (float) $this->montant;
            $this->compte?->ajusterSolde($delta);
        }

        return $gl;
    }

    public function annuler(string $motif, ?int $userId = null): void
    {
        $this->update([
            'statut'           => self::STATUT_ANNULE,
            'annule_at'        => now(),
            'annule_par'       => $userId ?? auth()->id(),
            'motif_annulation' => $motif,
        ]);
    }

    /**
     * Construit et persiste une écriture au Grand Livre à partir de l'ordre.
     * Le montant est signé selon le sens : négatif pour dépense, positif pour recette.
     * Les champs mappés du modèle (`mapping_gl`) sont recopiés depuis `donnees_json`.
     */
    protected function construireEcritureGrandLivre(int $userId): \App\Models\GrandLivre
    {
        $montant       = (float) $this->montant;
        $montantSigne  = $this->sens === 'depense' ? -$montant : $montant;
        $donnees       = $this->donnees_json ?? [];

        // Compte à débiter/créditer : le compte explicitement lié à l'ordre est
        // la source de vérité. Si absent, fallback sur le mode de règlement pour
        // conserver la compatibilité avec les ordres existants.
        $compteId = $this->compte_id ?: $this->determinerCompte($donnees);

        // Base d'attributs universellement remplis
        $attrs = [
            'date_ecriture'            => now()->toDateString(),
            'exercice'                 => $this->exercice?->exercice,
            'id_exercicebudgetaire'    => $this->exercice_id,
            'id_codeanalytique'        => $this->budgetLigne?->ligne?->id_codeanalytique,
            'id_famillecodeanalytique' => $this->budgetLigne?->id_famillecodeanalytique,
            'montant_tc'               => $montant,
            'montant_signe_tc'         => $montantSigne,
            'montant_tr'               => $montant,
            'montant_signe_tr'         => $montantSigne,
            'sens'                     => $this->sens,
            'type_ecriture'            => $this->sens,
            'num_piece'                => $this->numero_ordre,
            'ref_piece'                => $this->numero_ordre,
            'libelle'                  => $this->libelle ?: ($this->modele?->libelle . ' ' . $this->numero_ordre),
            'description'              => $this->libelle,
            'journal'                  => $this->sens === 'depense' ? 'JD' : 'JR', // Journal Dépenses / Recettes
            'devise'                   => 'XAF',
            'id_user'                  => $userId,
            'compte_id'                => $compteId,
            'isvalide'                 => 1,
        ];

        // Recopie les champs du formulaire dont `mapping_gl` est défini
        foreach ($this->modele->champs as $c) {
            if (!$c->mapping_gl) continue;
            $valeur = $donnees[$c->code_champ] ?? null;
            if ($valeur === null || $valeur === '') continue;

            // Cas particulier `imputation` : forcer depuis la ligne budgétaire (source de vérité)
            if ($c->mapping_gl === 'imputation') {
                $valeur = $this->budgetLigne?->ligne?->id_codeanalytique ?? $valeur;
            }
            $attrs[$c->mapping_gl] = $valeur;
        }

        // Bénéficiaire → colonnes du GL (APRÈS le loop des champs pour PRIORITÉ)
        // Le bloc bénéficiaire structuré prend le pas sur un éventuel champ modèle
        // mappé sur `beneficiaire` avec une valeur textuelle libre.
        if ($this->beneficiaire_source) {
            $attrs['beneficiaire']       = $this->beneficiaire_libelle;
            $attrs['type_beneficiaire']  = $this->beneficiaire_source;
            $attrs['beneficiaire_interne'] = $this->beneficiaire_source === 'user' ? 'user' : null;
            if ($this->beneficiaire_source === 'user') {
                $attrs['id_beneficiaire_interne'] = $this->beneficiaire_ref_id;
            } else {
                $attrs['id_beneficiaire'] = $this->beneficiaire_ref_id;
            }
        }

        return \App\Models\GrandLivre::create($attrs);
    }

    /**
     * Choisit un compte à mouvementer :
     *  - Si le mode de règlement est 'numeraire' → Caisse (type=2)
     *  - Sinon (cheque/virement) → Banque (type=1)
     *  - Fallback : premier compte non supprimé
     */
    protected function determinerCompte(array $donnees): int
    {
        $mode = $donnees['mode_reglement'] ?? null;
        $type = $mode === 'numeraire' ? 2 : ($mode === 'cheque' || $mode === 'virement' ? 1 : null);

        $q = \App\Models\Compte::where('effacer', 0);
        if ($type) $q->where('type', $type);
        $compte = $q->orderBy('id')->first() ?? \App\Models\Compte::orderBy('id')->first();

        if (!$compte) {
            // Fallback ultime : crée un compte "SYSTEM" pour ne pas bloquer l'exécution
            $compte = \App\Models\Compte::create([
                'code' => 'SYS', 'nom' => 'Compte système', 'type' => 1, 'solde' => 0, 'effacer' => 0,
            ]);
        }
        return (int) $compte->id;
    }
}
