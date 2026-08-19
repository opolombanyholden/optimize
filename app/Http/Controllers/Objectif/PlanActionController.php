<?php

namespace App\Http\Controllers\Objectif;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Objectif;
use App\Models\Intranet\PlanAction;
use App\Models\User;
use Illuminate\Http\Request;

class PlanActionController extends Controller
{
    public function index(Request $request)
    {
        $statut = $request->input('statut');

        $query = PlanAction::with(['objectif', 'responsable']);
        if ($statut) $query->where('statut', $statut);

        $plans = $query->orderByDesc('priorite')->orderBy('date_echeance')->get();

        $objectifs = Objectif::actif()->orderBy('titre')->get(['id', 'titre']);
        $users     = User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);

        $stats = [
            'total'    => PlanAction::count(),
            'actifs'   => PlanAction::actif()->count(),
            'realises' => PlanAction::realise()->count(),
            'retard'   => PlanAction::all()->filter(fn($p) => $p->estEnRetard())->count(),
        ];

        return view('objectif.plans-action.index', compact('plans', 'objectifs', 'users', 'stats', 'statut'));
    }

    public function show(PlanAction $plan)
    {
        $plan->load(['objectif.responsable', 'objectif.service', 'responsable', 'auteur']);
        $objectifs = Objectif::actif()->orderBy('titre')->get(['id', 'titre']);
        $users     = User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);
        return view('objectif.plans-action.show', compact('plan', 'objectifs', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'objectif_id'        => 'required|exists:intranet_objectifs,id',
            'responsable_id'     => 'nullable|exists:users,id',
            'date_debut'         => 'nullable|date',
            'date_echeance'      => 'nullable|date|after_or_equal:date_debut',
            'statut'             => 'required|in:planifie,en_cours,realise,reporte,annule',
            'priorite'           => 'required|in:basse,normale,haute,urgente',
            'avancement'         => 'nullable|integer|min:0|max:100',
            'resultats_attendus' => 'nullable|string',
            'moyens_requis'      => 'nullable|string',
            'budget_estime'      => 'nullable|numeric|min:0',
        ]);

        PlanAction::create(array_merge($validated, ['created_by' => auth()->id()]));

        return back()->with('success', 'Plan d\'action créé.');
    }

    public function update(Request $request, PlanAction $plan)
    {
        $validated = $request->validate([
            'titre'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'objectif_id'        => 'required|exists:intranet_objectifs,id',
            'responsable_id'     => 'nullable|exists:users,id',
            'date_debut'         => 'nullable|date',
            'date_echeance'      => 'nullable|date',
            'date_realisation'   => 'nullable|date',
            'statut'             => 'required|in:planifie,en_cours,realise,reporte,annule',
            'priorite'           => 'required|in:basse,normale,haute,urgente',
            'avancement'         => 'nullable|integer|min:0|max:100',
            'resultats_attendus' => 'nullable|string',
            'moyens_requis'      => 'nullable|string',
            'budget_estime'      => 'nullable|numeric|min:0',
            'budget_reel'        => 'nullable|numeric|min:0',
        ]);

        // Si réalisé, marquer date_realisation
        if ($validated['statut'] === 'realise' && empty($validated['date_realisation'])) {
            $validated['date_realisation'] = now()->toDateString();
            $validated['avancement'] = 100;
        }

        $plan->update($validated);

        return back()->with('success', 'Plan mis à jour.');
    }

    public function destroy(PlanAction $plan)
    {
        $plan->delete();
        return back()->with('success', 'Plan supprimé.');
    }
}
