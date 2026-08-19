<?php

namespace App\Models;

use App\Models\Intranet\ContactOrganisation;
use App\Traits\Intranet\HasPiecesJointes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class DevisFournisseur extends Model
{
    use SoftDeletes, HasPiecesJointes;

    public const STATUT_RECU        = 0;
    public const STATUT_SELECTIONNE = 1;
    public const STATUT_REJETE      = 2;

    public const STATUTS = [
        self::STATUT_RECU        => 'Reçu',
        self::STATUT_SELECTIONNE => 'Sélectionné',
        self::STATUT_REJETE      => 'Rejeté',
    ];

    protected $table = 'devis_fournisseurs';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date_reception' => 'date',
            'date_validite'  => 'date',
            'decide_at'      => 'datetime',
            'montant_ht'     => 'decimal:2',
            'montant_tva'    => 'decimal:2',
            'montant_ttc'    => 'decimal:2',
            'statut'         => 'integer',
        ];
    }

    public function commande()     { return $this->belongsTo(CommandeFournisseur::class, 'commande_fournisseur_id'); }
    public function fournisseur()  { return $this->belongsTo(ContactOrganisation::class, 'fournisseur_id'); }
    public function decideur()     { return $this->belongsTo(User::class, 'decide_par'); }
    public function facture()      { return $this->belongsTo(Facture::class, 'facture_id'); }
    public function lignes()       { return $this->hasMany(DevisFournisseurLigne::class, 'devis_id'); }

    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut] ?? '—'; }
    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_RECU        => 'info',
            self::STATUT_SELECTIONNE => 'success',
            self::STATUT_REJETE      => 'danger',
            default => 'secondary',
        };
    }

    public function peutEtreSelectionne(): bool { return $this->statut === self::STATUT_RECU; }
    public function peutEtreRejete(): bool { return $this->statut === self::STATUT_RECU; }

    /**
     * Sélectionne ce devis avec motivation.
     * Rejette automatiquement les autres devis en statut RECU de la même commande.
     */
    public function selectionner(string $motivation, ?int $userId = null): void
    {
        DB::transaction(function () use ($motivation, $userId) {
            // Rejette les autres devis reçus de la même commande
            static::where('commande_fournisseur_id', $this->commande_fournisseur_id)
                ->where('id', '!=', $this->id)
                ->where('statut', self::STATUT_RECU)
                ->update([
                    'statut' => self::STATUT_REJETE,
                    'motivation' => 'Rejeté automatiquement — devis concurrent sélectionné',
                    'decide_par' => $userId ?? auth()->id(),
                    'decide_at' => now(),
                ]);

            $this->update([
                'statut'     => self::STATUT_SELECTIONNE,
                'motivation' => $motivation,
                'decide_par' => $userId ?? auth()->id(),
                'decide_at'  => now(),
            ]);
        });
    }

    public function rejeter(string $motivation, ?int $userId = null): void
    {
        $this->update([
            'statut'     => self::STATUT_REJETE,
            'motivation' => $motivation,
            'decide_par' => $userId ?? auth()->id(),
            'decide_at'  => now(),
        ]);
    }

    public function recalculerMontants(): void
    {
        $ht = (float) $this->lignes()->sum('montant');
        $this->update([
            'montant_ht'  => $ht,
            'montant_ttc' => $ht + (float) $this->montant_tva,
        ]);
    }

    public static function genererNumero(): string
    {
        $annee = date('Y');
        $count = static::whereYear('created_at', $annee)->count() + 1;
        return sprintf('DEV-%s-%04d', $annee, $count);
    }
}
