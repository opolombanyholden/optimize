<?php

namespace App\Models;

use App\Traits\Intranet\HasPiecesJointes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class LivraisonFournisseur extends Model
{
    use SoftDeletes, HasPiecesJointes;

    public const TYPE_PARTIELLE = 'partielle';
    public const TYPE_TOTALE    = 'totale';

    public const TYPES = [
        self::TYPE_PARTIELLE => 'Partielle',
        self::TYPE_TOTALE    => 'Totale',
    ];

    protected $table = 'livraisons_fournisseur';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date_livraison' => 'date',
            'receptionnee_at' => 'datetime',
        ];
    }

    public function commande() { return $this->belongsTo(CommandeFournisseur::class, 'commande_fournisseur_id'); }
    public function emplacementReception() { return $this->belongsTo(Emplacement::class, 'emplacement_reception_id'); }
    public function receptionneur() { return $this->belongsTo(User::class, 'receptionnee_par'); }
    public function lignes() { return $this->hasMany(LivraisonFournisseurLigne::class, 'livraison_id'); }

    public function getTypeCouleurAttribute(): string
    {
        return $this->type === self::TYPE_TOTALE ? 'success' : 'warning text-dark';
    }

    /**
     * Applique la livraison : crée les mouvements de stock + met à jour quantite_livree sur commande_lignes.
     * Détermine automatiquement type = totale ou partielle. Met à jour le statut de la commande parente.
     */
    public function appliquer(?int $userId = null): void
    {
        DB::transaction(function () use ($userId) {
            foreach ($this->lignes as $ligne) {
                $commandeLigne = $ligne->commandeLigne;
                if (!$commandeLigne) continue;

                $qte = (float) $ligne->quantite;
                if ($qte <= 0) continue;

                // Increment la quantite_livree sur la ligne de commande
                $commandeLigne->increment('quantite_livree', $qte);

                if ($ligne->produit_id) {
                    $produit = $ligne->produit;
                    $emplacementId = $ligne->emplacement_id ?? $this->emplacement_reception_id;
                    if ($emplacementId) {
                        $produit->ajusterStockEmplacement((int) $emplacementId, $qte);
                    } else {
                        $produit->update(['stock_actuel' => (float) $produit->stock_actuel + $qte]);
                    }
                    ProduitMouvement::create([
                        'produit_id'     => $produit->id,
                        'type'           => ProduitMouvement::TYPE_ENTREE,
                        'quantite'       => $qte,
                        'stock_apres'    => (float) $produit->fresh()->stock_actuel,
                        'emplacement_id' => $emplacementId,
                        'reference'      => $this->numero,
                        'motif'          => 'Livraison fournisseur ' . ($this->bon_livraison_ref ? " (BL {$this->bon_livraison_ref})" : ''),
                        'source_type'    => self::class,
                        'source_id'      => $this->id,
                        'user_id'        => $userId ?? auth()->id(),
                    ]);
                }
            }

            $this->update([
                'receptionnee_at' => now(),
                'receptionnee_par' => $userId ?? auth()->id(),
            ]);

            // Recalcule le type et le statut de la commande parente
            $this->commande->refresh();
            $commande = $this->commande;
            $tousLivres = $commande->lignes()->whereColumn('quantite_livree', '<', 'quantite_commandee')->doesntExist();
            $partielle = !$tousLivres;

            $this->update(['type' => $tousLivres ? self::TYPE_TOTALE : self::TYPE_PARTIELLE]);

            if ($tousLivres) {
                $commande->update([
                    'statut' => CommandeFournisseur::STATUT_LIVREE,
                    'livre_at' => now(),
                    'livre_par' => $userId ?? auth()->id(),
                    'date_livraison_effective' => now()->toDateString(),
                ]);
            } elseif ($commande->statut === CommandeFournisseur::STATUT_APPROUVEE
                   || $commande->statut === CommandeFournisseur::STATUT_LIVREE_PARTIELLE) {
                $commande->update(['statut' => CommandeFournisseur::STATUT_LIVREE_PARTIELLE]);
            }
        });
    }

    public static function genererNumero(): string
    {
        $annee = date('Y');
        $count = static::whereYear('created_at', $annee)->count() + 1;
        return sprintf('LF-%s-%04d', $annee, $count);
    }
}
