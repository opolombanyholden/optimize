<?php

namespace App\Models;

use App\Traits\Intranet\HasPiecesJointes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CommandeFournisseur extends Model
{
    use SoftDeletes, HasPiecesJointes;

    protected $fillable = [
        'numero_commande', 'objet', 'fournisseur_id', 'approvisionnement_id',
        'date_commande', 'date_livraison_prevue', 'date_livraison_effective',
        'montant_ht', 'montant_tva', 'montant_ttc',
        'mode_reglement', 'conditions', 'commentaire',
        'valideur_id', 'fichiersjoin', 'statut', 'extra_attributes',
        'created_by',
        'soumis_at', 'soumis_par',
        'approuve_at', 'approuve_par',
        'livre_at', 'livre_par',
        'annule_at', 'annule_par', 'motif_annulation',
        'facture_id',
    ];

    protected function casts(): array
    {
        return [
            'date_commande'            => 'date',
            'date_livraison_prevue'    => 'date',
            'date_livraison_effective' => 'date',
            'soumis_at'                => 'datetime',
            'approuve_at'              => 'datetime',
            'livre_at'                 => 'datetime',
            'annule_at'                => 'datetime',
            'extra_attributes'         => 'array',
        ];
    }

    // ─── Workflow ─────────────────────────────
    public const STATUT_BROUILLON        = 0;
    public const STATUT_SOUMISE          = 1;
    public const STATUT_APPROUVEE        = 2;
    public const STATUT_LIVREE           = 3;
    public const STATUT_ANNULEE          = 4;
    public const STATUT_LIVREE_PARTIELLE = 5;

    public const MODES_REGLEMENT = [
        'virement_immediat'  => 'Virement immédiat',
        'virement_30j'       => 'Virement 30 jours',
        'virement_45j'       => 'Virement 45 jours',
        'virement_60j'       => 'Virement 60 jours',
        'cheque'             => 'Chèque',
        'especes'            => 'Espèces',
        'mobile_money'       => 'Mobile Money',
        'traite'             => 'Traite / Effet de commerce',
        'a_reception'        => 'Paiement à réception',
        'acompte_50_50'      => 'Acompte 50% / solde 50%',
        'autre'              => 'Autre (préciser)',
    ];

    public const STATUTS = [
        self::STATUT_BROUILLON        => 'Brouillon',
        self::STATUT_SOUMISE          => 'Soumise',
        self::STATUT_APPROUVEE        => 'Approuvée',
        self::STATUT_LIVREE_PARTIELLE => 'Livrée partielle',
        self::STATUT_LIVREE           => 'Livrée',
        self::STATUT_ANNULEE          => 'Annulée',
    ];

    public const STATUT_COULEURS = [
        self::STATUT_BROUILLON        => 'secondary',
        self::STATUT_SOUMISE          => 'info',
        self::STATUT_APPROUVEE        => 'primary',
        self::STATUT_LIVREE_PARTIELLE => 'warning',
        self::STATUT_LIVREE           => 'success',
        self::STATUT_ANNULEE          => 'danger',
    ];

    // ─── Relations ─────────────────────────────
    /**
     * Fournisseur : depuis la fusion CRM (2026-07-09), pointe vers ContactOrganisation.
     * Fallback sur Fournisseur legacy pour les enregistrements historiques.
     */
    public function fournisseur()
    {
        return $this->belongsTo(\App\Models\Intranet\ContactOrganisation::class, 'fournisseur_id');
    }
    public function fournisseurLegacy()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }
    public function approvisionnement() { return $this->belongsTo(Approvisionnement::class, 'approvisionnement_id'); }
    public function valideur()          { return $this->belongsTo(User::class, 'valideur_id'); }
    public function livraisons()        { return $this->hasMany(LivraisonFournisseur::class, 'commande_fournisseur_id')->latest('date_livraison'); }
    public function devis()             { return $this->hasMany(DevisFournisseur::class, 'commande_fournisseur_id')->latest('date_reception'); }
    public function devisSelectionne()  { return $this->hasOne(DevisFournisseur::class, 'commande_fournisseur_id')->where('statut', DevisFournisseur::STATUT_SELECTIONNE); }

    public function peutRecevoirLivraison(): bool
    {
        return in_array($this->statut, [self::STATUT_APPROUVEE, self::STATUT_LIVREE_PARTIELLE], true);
    }
    public function createur()          { return $this->belongsTo(User::class, 'created_by'); }
    public function soumetteur()        { return $this->belongsTo(User::class, 'soumis_par'); }
    public function approbateur()       { return $this->belongsTo(User::class, 'approuve_par'); }
    public function livreur()           { return $this->belongsTo(User::class, 'livre_par'); }
    public function annuleur()          { return $this->belongsTo(User::class, 'annule_par'); }
    public function facture()           { return $this->belongsTo(Facture::class, 'facture_id'); }
    public function lignes()            { return $this->hasMany(CommandeLigne::class, 'commande_id')->orderBy('ordre'); }
    public function mouvements()        { return $this->morphMany(ProduitMouvement::class, 'source'); }

    // ─── Accessors ─────────────────────────────
    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut] ?? '—'; }
    public function getStatutCouleurAttribute(): string { return self::STATUT_COULEURS[$this->statut] ?? 'secondary'; }
    public function getFournisseurLibelleAttribute(): ?string
    {
        return $this->fournisseur?->nom_affichage
            ?? $this->fournisseur?->raison_sociale
            ?? $this->fournisseur?->nom;
    }

    // ─── Autorisations ────────────────────────
    public function peutEtreModifiee(): bool { return $this->statut === self::STATUT_BROUILLON; }
    public function peutEtreSoumise(): bool  { return $this->statut === self::STATUT_BROUILLON && $this->lignes()->exists(); }
    public function peutEtreApprouvee(): bool{ return $this->statut === self::STATUT_SOUMISE; }
    public function peutEtreLivree(): bool   { return in_array($this->statut, [self::STATUT_APPROUVEE, self::STATUT_LIVREE_PARTIELLE], true); }
    public function peutEtreAnnulee(): bool  { return in_array($this->statut, [self::STATUT_BROUILLON, self::STATUT_SOUMISE, self::STATUT_APPROUVEE]); }
    public function peutGenererFacture(): bool { return $this->statut === self::STATUT_LIVREE && !$this->facture_id; }

    // ─── Actions workflow ────────────────────
    public function soumettre(?int $userId = null): void
    {
        $this->update(['statut' => self::STATUT_SOUMISE, 'soumis_at' => now(), 'soumis_par' => $userId ?? auth()->id()]);
    }

    public function approuver(?int $userId = null): void
    {
        $this->update([
            'statut' => self::STATUT_APPROUVEE,
            'approuve_at' => now(),
            'approuve_par' => $userId ?? auth()->id(),
            'valideur_id'  => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Marque la commande livrée : met à jour les quantités livrées par ligne
     * et crée un mouvement de stock (entrée) pour chaque produit.
     *
     * @param array $quantitesParLigne  [ligne_id => quantite_livree]
     */
    public function livrer(array $quantitesParLigne = [], ?int $userId = null, array $emplacementsParLigne = []): void
    {
        DB::transaction(function () use ($quantitesParLigne, $userId, $emplacementsParLigne) {
            foreach ($this->lignes as $ligne) {
                $qteLivree = isset($quantitesParLigne[$ligne->id])
                    ? (float) $quantitesParLigne[$ligne->id]
                    : (float) $ligne->quantite_commandee;
                if ($qteLivree <= 0) continue;

                $qteLivree = min($qteLivree, (float) $ligne->reste_a_livrer);
                if ($qteLivree <= 0) continue;

                $ligne->increment('quantite_livree', $qteLivree);

                // Emplacement de destination : passé lors de la livraison, sinon celui pré-affecté à la ligne
                $emplacementId = $emplacementsParLigne[$ligne->id] ?? $ligne->emplacement_id;

                // Mouvement de stock si un produit référentiel est lié
                if ($ligne->produit_id) {
                    $produit = $ligne->produit;
                    if ($emplacementId) {
                        $nouveauStock = $produit->ajusterStockEmplacement((int) $emplacementId, $qteLivree);
                    } else {
                        // Legacy : aucun emplacement — on met à jour l'agrégat sans détail
                        $nouveauStock = (float) $produit->stock_actuel + $qteLivree;
                        $produit->update(['stock_actuel' => $nouveauStock]);
                    }

                    ProduitMouvement::create([
                        'produit_id'      => $produit->id,
                        'type'            => ProduitMouvement::TYPE_ENTREE,
                        'quantite'        => $qteLivree,
                        'stock_apres'     => (float) $produit->fresh()->stock_actuel,
                        'emplacement_id'  => $emplacementId,
                        'reference'       => $this->numero_commande,
                        'motif'           => 'Livraison commande',
                        'source_type'     => self::class,
                        'source_id'       => $this->id,
                        'user_id'         => $userId ?? auth()->id(),
                    ]);
                }
            }
            $this->update([
                'statut'                   => self::STATUT_LIVREE,
                'livre_at'                 => now(),
                'livre_par'                => $userId ?? auth()->id(),
                'date_livraison_effective' => now()->toDateString(),
            ]);
        });
    }

    public function annuler(string $motif, ?int $userId = null): void
    {
        $this->update([
            'statut'           => self::STATUT_ANNULEE,
            'annule_at'        => now(),
            'annule_par'       => $userId ?? auth()->id(),
            'motif_annulation' => $motif,
        ]);
    }

    // ─── Calculs ─────────────────────────────
    public function recalculerMontants(): void
    {
        $ht = (float) $this->lignes()->sum('montant');
        $tva = 0; // Optionnel : à calculer selon produits si taux_tva par produit
        $this->update([
            'montant_ht'  => round($ht, 2),
            'montant_tva' => round($tva, 2),
            'montant_ttc' => round($ht + $tva, 2),
        ]);
    }

    // ─── Scopes ──────────────────────────────
    public function scopeStatut($q, int $s) { return $q->where('statut', $s); }
    public function scopeBrouillons($q)     { return $q->where('statut', self::STATUT_BROUILLON); }
    public function scopeEnCours($q)        { return $q->whereIn('statut', [self::STATUT_SOUMISE, self::STATUT_APPROUVEE]); }
}
