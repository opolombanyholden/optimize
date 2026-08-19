<?php

namespace App\Models;

use App\Traits\Intranet\HasPiecesJointes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Immobilisation extends Model
{
    use SoftDeletes, HasPiecesJointes;

    public const METHODE_LINEAIRE = 'lineaire';
    public const METHODE_DEGRESSIF = 'degressif';

    public const METHODES = [
        self::METHODE_LINEAIRE => 'Linéaire',
        self::METHODE_DEGRESSIF => 'Dégressif',
    ];

    public const ETATS = [
        'neuf' => 'Neuf', 'bon' => 'Bon', 'usage' => 'Usagé', 'hors_service' => 'Hors service', 'sortie' => 'Sortie',
    ];

    public const CATEGORIES = [
        'materiel_informatique' => 'Matériel informatique',
        'mobilier' => 'Mobilier',
        'vehicule' => 'Véhicule',
        'batiment' => 'Bâtiment',
        'materiel_technique' => 'Matériel technique',
        'autre' => 'Autre',
    ];

    protected $fillable = [
        'code', 'designation', 'description', 'categorie',
        'localisation', 'date_acquisition', 'date_mise_en_service', 'date_sortie', 'motif_sortie',
        'valeur_acquisition', 'valeur_nette_comptable', 'valeur_residuelle',
        'duree_amortissement', 'methode_amortissement',
        'amortissement_cumule', 'derniere_dotation_at',
        'etat', 'affecte_a', 'entite_id',
        'fichiersjoin', 'statut', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'date_acquisition' => 'date',
            'date_mise_en_service' => 'date',
            'date_sortie' => 'date',
            'derniere_dotation_at' => 'date',
            'valeur_acquisition' => 'decimal:2',
            'valeur_nette_comptable' => 'decimal:2',
            'valeur_residuelle' => 'decimal:2',
            'amortissement_cumule' => 'decimal:2',
            'statut' => 'integer',
            'extra_attributes' => 'array',
        ];
    }

    public function employe() { return $this->belongsTo(Employee::class, 'affecte_a'); }
    public function entite() { return $this->belongsTo(Entite::class, 'entite_id'); }
    public function dysfonctionnements() { return $this->hasMany(Dysfonctionnement::class, 'immobilisation_id'); }
    public function interventions() { return $this->hasMany(Intervention::class, 'immobilisation_id'); }
    public function dotations() { return $this->hasMany(ImmobilisationDotation::class)->orderBy('periode_debut'); }

    /**
     * Dotation mensuelle théorique (méthode linéaire).
     * Base amortissable = valeur_acquisition - valeur_residuelle, étalée sur duree_amortissement mois.
     */
    public function getDotationMensuelleAttribute(): float
    {
        if (!$this->duree_amortissement || $this->duree_amortissement <= 0) return 0.0;
        $base = (float) $this->valeur_acquisition - (float) ($this->valeur_residuelle ?? 0);
        return round($base / (int) $this->duree_amortissement, 2);
    }

    public function getEstTotalementAmortiAttribute(): bool
    {
        $seuil = max((float) ($this->valeur_residuelle ?? 0), 0);
        return (float) $this->valeur_nette_comptable <= $seuil + 0.005;
    }

    /**
     * Enregistre une dotation d'amortissement pour la période donnée (mois calendaire).
     * Retourne la Dotation créée ou null si déjà comptabilisée / totalement amortie.
     */
    public function enregistrerDotation(\DateTimeInterface $periodeDebut, ?int $userId = null): ?ImmobilisationDotation
    {
        if ($this->methode_amortissement !== self::METHODE_LINEAIRE || $this->est_totalement_amorti) return null;

        $debut = \Carbon\Carbon::instance($periodeDebut)->startOfMonth();
        $fin = $debut->copy()->endOfMonth();

        // Déjà comptabilisée ?
        if ($this->dotations()->where('periode_debut', $debut->toDateString())->exists()) return null;

        $vncAvant = (float) $this->valeur_nette_comptable;
        $seuil = (float) ($this->valeur_residuelle ?? 0);
        $montantTheorique = $this->dotation_mensuelle;
        $montant = min($montantTheorique, max($vncAvant - $seuil, 0));
        if ($montant <= 0) return null;

        $vncApres = round($vncAvant - $montant, 2);
        $cumulApres = round((float) $this->amortissement_cumule + $montant, 2);

        $dotation = $this->dotations()->create([
            'periode_debut' => $debut->toDateString(),
            'periode_fin' => $fin->toDateString(),
            'montant' => $montant,
            'vnc_avant' => $vncAvant,
            'vnc_apres' => $vncApres,
            'cumul_apres' => $cumulApres,
            'methode' => self::METHODE_LINEAIRE,
            'created_by' => $userId ?? auth()->id(),
        ]);

        $this->update([
            'valeur_nette_comptable' => $vncApres,
            'amortissement_cumule' => $cumulApres,
            'derniere_dotation_at' => $fin->toDateString(),
        ]);

        return $dotation;
    }
}
