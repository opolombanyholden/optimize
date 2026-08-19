<?php

namespace App\Models;

use App\Models\Intranet\ContactOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class EvaluationPrestataire extends Model
{
    use SoftDeletes;

    public const SOURCE_LIVRAISON = 'livraison';
    public const SOURCE_CAMPAGNE = 'campagne';
    public const SOURCE_AD_HOC = 'ad_hoc';

    protected $table = 'evaluations_prestataires';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date_evaluation' => 'date',
            'note_globale' => 'decimal:2',
        ];
    }

    public function prestataire() { return $this->belongsTo(ContactOrganisation::class, 'prestataire_id'); }
    public function evaluateur() { return $this->belongsTo(User::class, 'evaluateur_id'); }
    public function commandeFournisseur() { return $this->belongsTo(CommandeFournisseur::class); }
    public function campagne() { return $this->belongsTo(CampagneEvaluation::class, 'campagne_id'); }
    public function notes() { return $this->hasMany(EvaluationNote::class, 'evaluation_id'); }

    /**
     * Recalcule la note globale (moyenne pondérée sur 5).
     * Chaque note est normalisée sur l'échelle du critère puis pondérée.
     */
    public function recalculerNoteGlobale(): float
    {
        $rows = $this->notes()->with('critere')->get();
        if ($rows->isEmpty()) return 0;

        $poidsTotal = 0;
        $somme = 0;
        foreach ($rows as $n) {
            $c = $n->critere;
            if (!$c) continue;
            $max = max((int) $c->echelle_max, 1);
            $normalisee = ((float) $n->note / $max) * 5;
            $somme += $normalisee * (float) $c->poids;
            $poidsTotal += (float) $c->poids;
        }
        $note = $poidsTotal > 0 ? round($somme / $poidsTotal, 2) : 0;
        $this->update(['note_globale' => $note]);
        return $note;
    }
}
