<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetCout;
use App\Models\Intranet\Tache;
use Illuminate\Http\Request;

class CoutController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load([
            'couts.tache', 'couts.phase', 'couts.auteur',
            'phases.taches.couts', 'phases.couts',
            'taches:id,titre,projet_id,phase_id,cout_execution',
        ]);

        $couts  = $projet->couts;
        $phases = $projet->phases;

        return view('projet.couts.index', compact('projet', 'couts', 'phases'));
    }

    public function store(Request $request, Projet $projet)
    {
        $request->validate([
            'libelle'        => 'required|string|max:255',
            'categorie'      => 'nullable|string|max:80',
            'phase_id'       => 'nullable|exists:intranet_projet_phases,id',
            'tache_id'       => 'nullable|exists:intranet_taches,id',
            'montant_estime' => 'nullable|numeric|min:0',
            'montant_reel'   => 'nullable|numeric|min:0',
            'date_cout'      => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        $data = $request->only(['libelle', 'categorie', 'phase_id', 'tache_id', 'montant_estime', 'montant_reel', 'date_cout', 'notes']);

        // Auto-assigner la phase_id depuis la tâche si non spécifiée
        if (empty($data['phase_id']) && !empty($data['tache_id'])) {
            $tache = Tache::find($data['tache_id']);
            if ($tache && $tache->phase_id) {
                $data['phase_id'] = $tache->phase_id;
            }
        }

        $projet->couts()->create(array_merge($data, ['created_by' => auth()->id()]));

        return back()->with('success', 'Coût enregistré.');
    }

    public function update(Request $request, Projet $projet, ProjetCout $cout)
    {
        $request->validate([
            'libelle'        => 'required|string|max:255',
            'categorie'      => 'nullable|string|max:80',
            'phase_id'       => 'nullable|exists:intranet_projet_phases,id',
            'tache_id'       => 'nullable|exists:intranet_taches,id',
            'montant_estime' => 'nullable|numeric|min:0',
            'montant_reel'   => 'nullable|numeric|min:0',
            'date_cout'      => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        $data = $request->only(['libelle', 'categorie', 'phase_id', 'tache_id', 'montant_estime', 'montant_reel', 'date_cout', 'notes']);

        // Auto-assigner la phase_id depuis la tâche si non spécifiée
        if (empty($data['phase_id']) && !empty($data['tache_id'])) {
            $tache = Tache::find($data['tache_id']);
            if ($tache && $tache->phase_id) {
                $data['phase_id'] = $tache->phase_id;
            }
        }

        $cout->update($data);

        return back()->with('success', 'Coût mis à jour.');
    }

    public function destroy(Projet $projet, ProjetCout $cout)
    {
        $cout->delete();
        return back()->with('success', 'Coût supprimé.');
    }
}
