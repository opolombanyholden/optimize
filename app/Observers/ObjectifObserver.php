<?php

namespace App\Observers;

use App\Models\Intranet\Objectif;
use App\Services\Objectif\CascadeEvaluationService;

/**
 * Observer de l'Objectif : déclenche la cascade vers Evaluation lors d'un
 * changement de statut vers un statut final (atteint / non_atteint).
 */
class ObjectifObserver
{
    public function __construct(private CascadeEvaluationService $cascade) {}

    public function updated(Objectif $objectif): void
    {
        // Ne déclenche QUE si le statut vient de changer
        if (!$objectif->wasChanged('statut')) {
            return;
        }
        $this->cascade->cascader($objectif);
    }

    public function created(Objectif $objectif): void
    {
        // Si créé directement avec un statut final (rare mais possible)
        if (in_array($objectif->statut, ['atteint', 'non_atteint'], true)) {
            $this->cascade->cascader($objectif);
        }
    }
}
