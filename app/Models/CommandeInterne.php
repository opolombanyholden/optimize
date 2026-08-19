<?php

namespace App\Models;

use App\Traits\Intranet\HasPiecesJointes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CommandeInterne extends Model
{
    use SoftDeletes, HasPiecesJointes;

    public const STATUT_BROUILLON = 0;
    public const STATUT_EN_ATTENTE_N1 = 1;
    public const STATUT_VALIDEE_N1 = 2;
    public const STATUT_REFUSEE_N1 = 3;
    public const STATUT_TRANSMISE_APPRO = 4;
    public const STATUT_LIVREE_PARTIELLE = 5;
    public const STATUT_LIVREE = 6;
    public const STATUT_ANNULEE = 7;

    public const STATUTS = [
        self::STATUT_BROUILLON => 'Brouillon',
        self::STATUT_EN_ATTENTE_N1 => 'En attente N+1',
        self::STATUT_VALIDEE_N1 => 'Validée N+1',
        self::STATUT_REFUSEE_N1 => 'Refusée N+1',
        self::STATUT_TRANSMISE_APPRO => 'Transmise Appro',
        self::STATUT_LIVREE_PARTIELLE => 'Livrée partielle',
        self::STATUT_LIVREE => 'Livrée',
        self::STATUT_ANNULEE => 'Annulée',
    ];

    protected $table = 'commandes_internes';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date_demande' => 'date',
            'date_besoin' => 'date',
            'soumise_n1_at' => 'datetime',
            'validee_n1_at' => 'datetime',
            'transmise_appro_at' => 'datetime',
            'livree_at' => 'datetime',
            'annulee_at' => 'datetime',
            'montant_estime' => 'decimal:2',
            'statut' => 'integer',
        ];
    }

    public function demandeur() { return $this->belongsTo(User::class, 'demandeur_id'); }
    public function superieur() { return $this->belongsTo(User::class, 'superieur_id'); }
    public function valideurAppro() { return $this->belongsTo(User::class, 'valide_par_appro_id'); }
    public function commandeFournisseur() { return $this->belongsTo(CommandeFournisseur::class, 'commande_fournisseur_id'); }
    public function lignes() { return $this->hasMany(CommandeInterneLigne::class, 'commande_interne_id'); }
    public function livraisons() { return $this->hasMany(LivraisonInterne::class, 'commande_interne_id')->latest('date_livraison'); }

    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut] ?? '—'; }

    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_BROUILLON => 'secondary',
            self::STATUT_EN_ATTENTE_N1 => 'info',
            self::STATUT_VALIDEE_N1 => 'primary',
            self::STATUT_REFUSEE_N1, self::STATUT_ANNULEE => 'warning',
            self::STATUT_TRANSMISE_APPRO => 'info',
            self::STATUT_LIVREE_PARTIELLE => 'warning',
            self::STATUT_LIVREE => 'success',
            default => 'light',
        };
    }

    public function peutEtreModifiee(): bool { return in_array($this->statut, [self::STATUT_BROUILLON, self::STATUT_REFUSEE_N1], true); }
    public function peutEtreSoumise(): bool { return $this->peutEtreModifiee() && $this->lignes()->exists(); }
    public function peutEtreValideeN1(): bool { return $this->statut === self::STATUT_EN_ATTENTE_N1; }
    public function peutEtreTransmise(): bool { return $this->statut === self::STATUT_VALIDEE_N1; }
    public function peutEtreLivree(): bool { return in_array($this->statut, [self::STATUT_TRANSMISE_APPRO, self::STATUT_LIVREE_PARTIELLE], true); }
    /** Peut être renvoyé vers un N+1 après coup (par Appro) : uniquement si actuellement en triage Appro sans validation préalable. */
    public function peutEtreAssignee(): bool { return $this->statut === self::STATUT_TRANSMISE_APPRO; }
    public function peutEtreAnnulee(): bool { return !in_array($this->statut, [self::STATUT_LIVREE, self::STATUT_ANNULEE], true); }

    // ═════════ Actions ═════════

    public function soumettreN1(?int $userId = null): void
    {
        // Si un N+1 est désigné → validation N+1 attendue. Sinon → passe directement en
        // triage Appro (les appro décideront : traiter en direct OU assigner un N+1 après coup).
        if ($this->superieur_id) {
            $this->update([
                'statut' => self::STATUT_EN_ATTENTE_N1,
                'soumise_n1_at' => now(),
            ]);
        } else {
            $this->update([
                'statut' => self::STATUT_TRANSMISE_APPRO,
                'soumise_n1_at' => now(),
                'transmise_appro_at' => now(),
            ]);
        }
    }

    /**
     * Après coup : Appro assigne un N+1 valideur à une commande soumise sans N+1.
     * Repasse la commande en EN_ATTENTE_N1 et notifie le demandeur.
     */
    public function assignerN1(int $n1Id, ?string $commentaire = null, ?int $auteurId = null): void
    {
        $auteurId = $auteurId ?? auth()->id();
        \Illuminate\Support\Facades\DB::transaction(function () use ($n1Id, $commentaire, $auteurId) {
            $this->update([
                'superieur_id'       => $n1Id,
                'statut'             => self::STATUT_EN_ATTENTE_N1,
                'soumise_n1_at'      => now(),
                'transmise_appro_at' => null,
                'commentaire_appro'  => $commentaire,
            ]);
        });
        // Notification au demandeur
        $n1 = User::find($n1Id);
        $auteur = User::find($auteurId);
        if ($this->demandeur && $n1 && $auteur) {
            $this->demandeur->notify(new \App\Notifications\CommandeInterneAssigneeN1($this, $n1, $auteur));
        }
    }

    public function validerN1(?int $userId = null): void
    {
        $this->update([
            'statut' => self::STATUT_VALIDEE_N1,
            'validee_n1_at' => now(),
        ]);
    }

    public function refuserN1(string $motif, ?int $userId = null): void
    {
        $this->update([
            'statut' => self::STATUT_REFUSEE_N1,
            'motif_refus_n1' => $motif,
        ]);
    }

    public function transmettreAppro(?int $userId = null): void
    {
        $this->update([
            'statut' => self::STATUT_TRANSMISE_APPRO,
            'transmise_appro_at' => now(),
            'valide_par_appro_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Enregistre une livraison interne (partielle ou complète).
     * $quantites = [ligne_id => quantite_livree_ce_coup]
     */
    public function livrer(array $quantites, ?int $userId = null, ?int $recuPar = null, ?string $commentaire = null, array $emplacementsSourceParLigne = []): LivraisonInterne
    {
        return DB::transaction(function () use ($quantites, $userId, $recuPar, $commentaire, $emplacementsSourceParLigne) {
            $livraison = $this->livraisons()->create([
                'date_livraison' => now()->toDateString(),
                'livre_par' => $userId ?? auth()->id(),
                'recu_par' => $recuPar,
                'type' => 'partielle', // recalculé après
                'commentaire' => $commentaire,
            ]);

            foreach ($this->lignes as $ligne) {
                $qte = (float) ($quantites[$ligne->id] ?? 0);
                if ($qte <= 0) continue;
                $reste = (float) $ligne->quantite_demandee - (float) $ligne->quantite_livree;
                $qte = min($qte, max($reste, 0));
                if ($qte <= 0) continue;

                $livraison->lignes()->create([
                    'commande_ligne_id' => $ligne->id,
                    'quantite' => $qte,
                ]);
                $ligne->increment('quantite_livree', $qte);

                // Décrémenter le stock du produit référencé si stockable
                if ($ligne->produit_id && $ligne->produit && $ligne->produit->est_stockable) {
                    $produit = $ligne->produit;
                    $emplacementSourceId = $emplacementsSourceParLigne[$ligne->id] ?? $ligne->emplacement_source_id;

                    if ($emplacementSourceId) {
                        $produit->ajusterStockEmplacement((int) $emplacementSourceId, -$qte);
                    } else {
                        // Legacy : sans emplacement, on décrémente juste l'agrégat
                        $nouveauStock = max((float) $produit->stock_actuel - $qte, 0);
                        $produit->update(['stock_actuel' => $nouveauStock]);
                    }

                    ProduitMouvement::create([
                        'produit_id'            => $produit->id,
                        'type'                  => ProduitMouvement::TYPE_SORTIE,
                        'quantite'              => $qte,
                        'stock_apres'           => (float) $produit->fresh()->stock_actuel,
                        'emplacement_source_id' => $emplacementSourceId,
                        'reference'             => $this->numero,
                        'motif'                 => 'Livraison interne à agent',
                        'source_type'           => LivraisonInterne::class,
                        'source_id'             => $livraison->id,
                        'user_id'               => $userId ?? auth()->id(),
                    ]);
                }
            }

            $this->refresh();
            $tousLivres = $this->lignes()->whereColumn('quantite_livree', '<', 'quantite_demandee')->doesntExist();
            $livraison->update(['type' => $tousLivres ? 'complete' : 'partielle']);
            $this->update([
                'statut' => $tousLivres ? self::STATUT_LIVREE : self::STATUT_LIVREE_PARTIELLE,
                'livree_at' => $tousLivres ? now() : null,
            ]);

            return $livraison;
        });
    }

    public function annuler(string $motif, ?int $userId = null): void
    {
        $this->update([
            'statut' => self::STATUT_ANNULEE,
            'annulee_at' => now(),
            'motif_annulation' => $motif,
        ]);
    }

    public function recalculerMontant(): void
    {
        $total = (float) $this->lignes()->selectRaw('COALESCE(SUM(quantite_demandee * COALESCE(prix_unitaire_estime, 0)), 0) as t')->value('t');
        $this->update(['montant_estime' => $total]);
    }

    public static function genererNumero(): string
    {
        $annee = date('Y');
        $count = static::whereYear('created_at', $annee)->count() + 1;
        return sprintf('CI-%s-%04d', $annee, $count);
    }
}
