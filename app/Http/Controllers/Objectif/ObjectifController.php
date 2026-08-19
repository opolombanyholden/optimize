<?php

namespace App\Http\Controllers\Objectif;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Objectif;
use App\Models\Intranet\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ObjectifController extends Controller
{
    /**
     * Liste — peut être filtrée pour stratégiques (sans parent) ou opérationnels (avec parent).
     */
    public function index(Request $request)
    {
        $type = $request->input('type');  // 'strategique' | 'operationnel' | null

        $query = Objectif::with(['parent', 'sousObjectifs', 'kpi', 'responsable', 'service']);

        // Type (hiérarchique)
        if ($type === 'strategique') {
            $query->strategique();
        } elseif ($type === 'operationnel') {
            $query->operationnel();
        }

        // Filtres avancés
        if ($q = $request->input('q')) {
            $query->where(fn($w) => $w->where('titre', 'ilike', "%$q%")
                ->orWhere('code', 'ilike', "%$q%")
                ->orWhere('description', 'ilike', "%$q%"));
        }
        if ($s = $request->input('statut'))         $query->where('statut', $s);
        if ($p = $request->input('portee'))         $query->where('portee', $p);
        if ($r = $request->input('responsable_id')) $query->where('responsable_id', $r);
        if ($sv = $request->input('service_id'))    $query->where('service_id', $sv);
        if ($du = $request->input('date_du'))       $query->whereDate('date_fin', '>=', $du);
        if ($au = $request->input('date_au'))       $query->whereDate('date_fin', '<=', $au);
        if ($request->boolean('en_retard')) {
            $query->where('statut', 'actif')->whereDate('date_fin', '<', now());
        }

        $objectifs = $query->orderBy('date_fin')->get();

        $services      = Service::actif()->orderBy('nom')->get();
        $users         = User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);
        $strategiques  = Objectif::strategique()->orderBy('titre')->get(['id', 'titre']);

        return view('objectif.objectifs.index', compact('objectifs', 'type', 'services', 'users', 'strategiques'));
    }

    public function show(Objectif $objectif)
    {
        $objectif->load([
            'parent', 'sousObjectifs.responsable', 'sousObjectifs.kpi',
            'kpi.valeurs', 'evaluations.utilisateur', 'evaluations.evaluateur',
            'projets.statut', 'taches.statut', 'responsable', 'service', 'auteur',
            'plansAction.responsable',
        ]);

        return view('objectif.objectifs.show', compact('objectif'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'          => 'required|string|max:255',
            'code'           => 'nullable|string|max:30|unique:intranet_objectifs,code',
            'description'    => 'nullable|string',
            'portee'         => 'required|in:organisation,service,equipe,individuel',
            'parent_id'      => 'nullable|exists:intranet_objectifs,id',
            'responsable_id' => 'nullable|exists:users,id',
            'service_id'     => 'nullable|exists:intranet_services,id',
            'date_debut'     => 'nullable|date',
            'date_fin'       => 'nullable|date|after_or_equal:date_debut',
            'statut'         => 'required|in:actif,atteint,non_atteint,abandonne',
            'couleur'        => 'nullable|string|max:20',
            'icone'          => 'nullable|string|max:50',
            'ponderation'    => 'nullable|integer|min:0|max:100',
        ]);

        Objectif::create(array_merge($validated, ['created_by' => auth()->id()]));

        return back()->with('success', 'Objectif créé.');
    }

    public function update(Request $request, Objectif $objectif)
    {
        $validated = $request->validate([
            'titre'          => 'required|string|max:255',
            'code'           => 'nullable|string|max:30|unique:intranet_objectifs,code,' . $objectif->id,
            'description'    => 'nullable|string',
            'portee'         => 'required|in:organisation,service,equipe,individuel',
            'parent_id'      => 'nullable|exists:intranet_objectifs,id',
            'responsable_id' => 'nullable|exists:users,id',
            'service_id'     => 'nullable|exists:intranet_services,id',
            'date_debut'     => 'nullable|date',
            'date_fin'       => 'nullable|date|after_or_equal:date_debut',
            'statut'         => 'required|in:actif,atteint,non_atteint,abandonne',
            'couleur'        => 'nullable|string|max:20',
            'icone'          => 'nullable|string|max:50',
            'ponderation'    => 'nullable|integer|min:0|max:100',
        ]);

        $objectif->update($validated);

        return back()->with('success', 'Objectif mis à jour.');
    }

    public function destroy(Objectif $objectif)
    {
        if ($objectif->sousObjectifs()->exists() || $objectif->kpi()->exists()) {
            return back()->withErrors(['_error' => 'Objectif contenant des sous-objectifs ou KPI. Supprimez-les d\'abord.']);
        }
        $objectif->delete();
        return back()->with('success', 'Objectif supprimé.');
    }
}
