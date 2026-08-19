<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produit extends Model
{
    use SoftDeletes;

    public const TYPE_BIEN = 'bien';
    public const TYPE_SERVICE = 'service';

    public const TYPES = [
        self::TYPE_BIEN => 'Bien',
        self::TYPE_SERVICE => 'Service',
    ];

    protected $fillable = [
        'code', 'designation', 'description',
        'categorie', 'sous_categorie', 'famille_id',
        'type_article', 'est_stockable',
        'unite_mesure', 'prix_unitaire', 'taux_tva',
        'stock_actuel', 'stock_minimum', 'stock_maximum', 'seuil_alerte',
        'emplacement', 'emplacement_id', 'fichiersjoin', 'statut', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
            'est_stockable' => 'boolean',
            'prix_unitaire' => 'decimal:2',
            'stock_actuel' => 'decimal:3',
            'stock_minimum' => 'decimal:3',
            'seuil_alerte' => 'decimal:3',
        ];
    }

    public function famille() { return $this->belongsTo(FamilleArticle::class, 'famille_id'); }
    public function emplacementPrincipal() { return $this->belongsTo(Emplacement::class, 'emplacement_id'); }
    public function approvisionnementProduits() { return $this->hasMany(ApprovisionnementProduit::class, 'produit_id'); }
    public function grandLivreDetails() { return $this->hasMany(GrandLivreDetail::class, 'id_produit'); }
    public function mouvements() { return $this->hasMany(ProduitMouvement::class, 'produit_id')->latest(); }
    public function emplacements() { return $this->belongsToMany(Emplacement::class, 'produit_emplacements')->withPivot('quantite')->withTimestamps(); }
    public function stocksParEmplacement() { return $this->hasMany(ProduitEmplacement::class); }

    /**
     * Quantité de cet article dans un emplacement donné (0 si non stocké).
     */
    public function stockDans(int $emplacementId): float
    {
        $row = $this->stocksParEmplacement()->where('emplacement_id', $emplacementId)->first();
        return $row ? (float) $row->quantite : 0.0;
    }

    /**
     * Applique un mouvement de stock sur un emplacement précis.
     * Met à jour le pivot ET l'agrégat stock_actuel, dans une même transaction.
     * Retourne la nouvelle quantité dans l'emplacement.
     */
    public function ajusterStockEmplacement(int $emplacementId, float $delta): float
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($emplacementId, $delta) {
            $pivot = $this->stocksParEmplacement()
                ->where('emplacement_id', $emplacementId)
                ->lockForUpdate()
                ->first();

            if ($pivot) {
                $nouvelle = max((float) $pivot->quantite + $delta, 0);
                $pivot->update(['quantite' => $nouvelle]);
            } else {
                $nouvelle = max($delta, 0);
                $this->stocksParEmplacement()->create([
                    'emplacement_id' => $emplacementId,
                    'quantite' => $nouvelle,
                ]);
            }

            // Agrégat = somme réelle des pivots (source de vérité)
            $total = (float) $this->stocksParEmplacement()->sum('quantite');
            $this->update(['stock_actuel' => $total]);

            return $nouvelle;
        });
    }

    public function scopeEnStock($q) { return $q->where('stock_actuel', '>', 0); }

    /**
     * Articles nécessitant une alerte : stock actuel ≤ seuil d'alerte (ou stock_minimum si non défini).
     * Uniquement pour les biens stockables.
     */
    public function scopeAlertStock($q)
    {
        return $q->where('est_stockable', true)
                 ->where('type_article', self::TYPE_BIEN)
                 ->where(function ($sub) {
                     $sub->whereRaw('stock_actuel <= COALESCE(seuil_alerte, stock_minimum, 0)');
                 });
    }

    public function scopeRupture($q)
    {
        return $q->where('est_stockable', true)
                 ->where('type_article', self::TYPE_BIEN)
                 ->where('stock_actuel', '<=', 0);
    }

    public function getEtatStockAttribute(): string
    {
        if (!$this->est_stockable || $this->type_article === self::TYPE_SERVICE) return 'non_applicable';
        $stock = (float) $this->stock_actuel;
        if ($stock <= 0) return 'rupture';
        $seuil = (float) ($this->seuil_alerte ?? $this->stock_minimum ?? 0);
        if ($seuil > 0 && $stock <= $seuil) return 'alerte';
        return 'ok';
    }

    public function getEtatStockCouleurAttribute(): string
    {
        return match ($this->etat_stock) {
            'rupture' => 'danger',
            'alerte' => 'warning',
            'ok' => 'success',
            default => 'secondary',
        };
    }
}
