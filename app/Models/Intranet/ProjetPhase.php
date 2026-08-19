<?php

namespace App\Models\Intranet;

use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use App\Traits\Intranet\HasPiecesJointes;
use App\Traits\Intranet\HasProtection;
use App\Traits\Intranet\HasValidation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjetPhase extends Model
{
    use SoftDeletes, HasCommentaires, HasPiecesJointes, HasValidation, HasProtection;

    protected $table = 'intranet_projet_phases';

    protected $fillable = [
        'projet_id', 'nom', 'description', 'code_wbs', 'ordre',
        'date_debut', 'date_fin', 'date_debut_reelle', 'date_fin_reelle',
        'avancement', 'ponderation', 'couleur', 'statut_id',
        'responsable_id', 'created_by',
        'statut_cloture', 'justification_cloture', 'soumis_le', 'valide_le', 'motif_rejet',
    ];

    protected $casts = [
        'date_debut'        => 'date',
        'date_fin'          => 'date',
        'date_debut_reelle' => 'date',
        'date_fin_reelle'   => 'date',
        'ponderation'       => 'decimal:2',
        'avancement'        => 'integer',
        'soumis_le'         => 'datetime',
        'valide_le'         => 'datetime',
    ];

    public function projet()      { return $this->belongsTo(Projet::class, 'projet_id'); }
    public function statut()      { return $this->belongsTo(Statut::class, 'statut_id'); }
    public function taches()      { return $this->hasMany(Tache::class, 'phase_id'); }
    public function jalons()      { return $this->hasMany(Jalon::class, 'phase_id'); }
    public function livrables()   { return $this->hasMany(ProjetLivrable::class, 'phase_id'); }
    public function responsable() { return $this->belongsTo(User::class, 'responsable_id'); }
    public function auteur()      { return $this->belongsTo(User::class, 'created_by'); }
    public function couts()       { return $this->hasMany(ProjetCout::class, 'phase_id'); }

    /**
     * Avancement réel calculé depuis les tâches filles.
     * Si les tâches ont une pondération, on fait une moyenne pondérée.
     * Sinon, moyenne simple.
     */
    public function getAvancementRealAttribute(): int
    {
        $taches = $this->taches;
        if ($taches->isEmpty()) return $this->avancement ?? 0;

        $totalPonderation = $taches->sum('ponderation');

        if ($totalPonderation > 0) {
            // Moyenne pondérée
            $somme = $taches->sum(fn($t) => $t->avancement * ($t->ponderation ?? 0));
            return (int) round($somme / $totalPonderation);
        }

        // Moyenne simple
        return (int) round($taches->avg('avancement'));
    }

    public function getStatutCouleurAttribute(): string
    {
        return $this->statut?->couleur ?? '#64748B';
    }

    /**
     * Coût estimé total de la phase :
     * Somme des coûts estimés des tâches filles + coûts directs de la phase.
     */
    public function getCoutEstimeAttribute(): float
    {
        $coutTaches = $this->taches->sum(fn($t) => $t->cout_total_estime);
        $coutDirect = (float) $this->couts->sum('montant_estime');
        return $coutTaches + $coutDirect;
    }

    /**
     * Coût réel total de la phase :
     * Somme des coûts réels des tâches filles + coûts directs de la phase.
     */
    public function getCoutReelAttribute(): float
    {
        $coutTaches = $this->taches->sum(fn($t) => $t->cout_total_reel);
        $coutDirect = (float) $this->couts->sum('montant_reel');
        return $coutTaches + $coutDirect;
    }

    /**
     * Écart de coût de la phase (réel - estimé).
     */
    public function getEcartCoutAttribute(): float
    {
        return $this->cout_reel - $this->cout_estime;
    }

    protected function determinerVerrouillage(): bool
    {
        return $this->taches()->exists()
            || $this->couts()->exists()
            || $this->jalons()->exists();
    }

    public function estEnRetard(): bool
    {
        return ! in_array($this->statut?->libelle, ['Terminé', 'Annulé'])
            && $this->date_fin?->isPast();
    }

    /**
     * Recalcule et sauvegarde l'avancement depuis les tâches filles.
     */
    public function recalculerAvancement(): void
    {
        $this->update(['avancement' => $this->avancement_real]);
    }
}
