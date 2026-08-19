<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Inventaire extends Model
{
    use SoftDeletes;

    public const STATUT_BROUILLON = 0;
    public const STATUT_EN_COURS = 1;
    public const STATUT_CLOTURE = 2;
    public const STATUT_ANNULE = 3;

    public const STATUTS = [
        self::STATUT_BROUILLON => 'Brouillon',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_CLOTURE => 'Clôturé',
        self::STATUT_ANNULE => 'Annulé',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date_prevue' => 'date',
            'date_realisation' => 'date',
            'cloture_at' => 'datetime',
            'statut' => 'integer',
        ];
    }

    public function emplacement() { return $this->belongsTo(Emplacement::class); }
    public function responsable() { return $this->belongsTo(User::class, 'responsable_id'); }
    public function clotureur() { return $this->belongsTo(User::class, 'cloture_par'); }
    public function lignes() { return $this->hasMany(InventaireLigne::class); }

    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut] ?? '—'; }

    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_BROUILLON => 'secondary',
            self::STATUT_EN_COURS => 'info',
            self::STATUT_CLOTURE => 'success',
            self::STATUT_ANNULE => 'warning',
            default => 'light',
        };
    }

    public function peutEtreModifie(): bool { return $this->statut === self::STATUT_BROUILLON; }
    public function peutEtreLance(): bool { return $this->statut === self::STATUT_BROUILLON && $this->lignes()->exists(); }
    public function peutEtreCloture(): bool { return $this->statut === self::STATUT_EN_COURS; }

    /**
     * Génère les lignes de l'inventaire à partir du stock actuel dans le périmètre.
     * Si emplacement_id est défini, ne prend que ce sous-arbre. Sinon, tous les emplacements.
     * Un produit ayant du stock (même 0) dans un emplacement génère une ligne.
     */
    public function genererLignes(?array $produitIds = null): int
    {
        return DB::transaction(function () use ($produitIds) {
            $this->lignes()->delete();

            $emplacementIds = null;
            if ($this->emplacement_id) {
                $emplacementIds = $this->emplacement->descendantsIds();
            }

            $q = ProduitEmplacement::query();
            if ($emplacementIds) $q->whereIn('emplacement_id', $emplacementIds);
            if ($produitIds) $q->whereIn('produit_id', $produitIds);

            $count = 0;
            foreach ($q->get() as $pe) {
                $this->lignes()->create([
                    'produit_id' => $pe->produit_id,
                    'emplacement_id' => $pe->emplacement_id,
                    'quantite_theorique' => (float) $pe->quantite,
                ]);
                $count++;
            }
            return $count;
        });
    }

    public function lancer(): void
    {
        $this->update(['statut' => self::STATUT_EN_COURS]);
    }

    /**
     * Clôture l'inventaire : pour chaque ligne comptée avec écart, génère un ProduitMouvement
     * de type ajustement + met à jour le stock (pivot emplacement + agrégat).
     * Les lignes non comptées (quantite_reelle NULL) sont ignorées.
     */
    public function cloturer(?int $userId = null): array
    {
        return DB::transaction(function () use ($userId) {
            $stats = ['ajustements' => 0, 'ignorees' => 0, 'positives' => 0, 'negatives' => 0];

            foreach ($this->lignes as $ligne) {
                if ($ligne->quantite_reelle === null) { $stats['ignorees']++; continue; }
                $ecart = (float) $ligne->quantite_reelle - (float) $ligne->quantite_theorique;
                $ligne->update(['ecart' => $ecart]);

                if (abs($ecart) < 0.0005) continue;

                $produit = $ligne->produit;
                if (!$produit) continue;

                if ($ligne->emplacement_id) {
                    $produit->ajusterStockEmplacement($ligne->emplacement_id, $ecart);
                } else {
                    // Ajustement sur agrégat sans emplacement (legacy)
                    $produit->update(['stock_actuel' => max((float) $produit->stock_actuel + $ecart, 0)]);
                }

                ProduitMouvement::create([
                    'produit_id'     => $produit->id,
                    'type'           => ProduitMouvement::TYPE_AJUSTEMENT,
                    'quantite'       => abs($ecart),
                    'stock_apres'    => (float) $produit->fresh()->stock_actuel,
                    'emplacement_id' => $ligne->emplacement_id,
                    'reference'      => $this->numero,
                    'motif'          => 'Ajustement inventaire' . ($ecart > 0 ? ' (positif)' : ' (négatif)'),
                    'source_type'    => self::class,
                    'source_id'      => $this->id,
                    'user_id'        => $userId ?? auth()->id(),
                ]);

                $stats['ajustements']++;
                if ($ecart > 0) $stats['positives']++; else $stats['negatives']++;
            }

            $this->update([
                'statut' => self::STATUT_CLOTURE,
                'cloture_at' => now(),
                'cloture_par' => $userId ?? auth()->id(),
                'date_realisation' => now()->toDateString(),
            ]);

            return $stats;
        });
    }

    public function annuler(): void
    {
        $this->update(['statut' => self::STATUT_ANNULE]);
    }

    public static function genererNumero(): string
    {
        $annee = date('Y');
        $count = static::whereYear('created_at', $annee)->count() + 1;
        return sprintf('INV-%s-%04d', $annee, $count);
    }
}
