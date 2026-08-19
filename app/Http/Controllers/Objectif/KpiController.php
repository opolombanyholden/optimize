<?php

namespace App\Http\Controllers\Objectif;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Kpi;
use App\Models\Intranet\KpiValeur;
use App\Models\Intranet\Objectif;
use Illuminate\Http\Request;

class KpiController extends Controller
{
    public function index(Request $request)
    {
        $query = Kpi::with(['objectif', 'valeurs' => fn($q) => $q->orderByDesc('date_mesure')]);

        if ($q = $request->input('q')) {
            $query->where(fn($w) => $w->where('titre', 'ilike', "%$q%")->orWhere('description', 'ilike', "%$q%"));
        }
        if ($obj = $request->input('objectif_id'))    $query->where('objectif_id', $obj);
        if ($per = $request->input('periodicite'))    $query->where('periodicite', $per);
        if ($tend = $request->input('tendance'))      $query->where('tendance', $tend);

        $kpis = $query->orderBy('titre')->get();

        // Filtres calculés (sur la collection chargée)
        if ($etat = $request->input('etat')) {
            $kpis = match ($etat) {
                'au_dessus'  => $kpis->filter(fn($k) => $k->valeur_cible && $k->valeur_actuelle >= $k->valeur_cible),
                'en_alerte'  => $kpis->filter(fn($k) => $k->valeur_cible && $k->progression < 50),
                'sans_cible' => $kpis->filter(fn($k) => ! $k->valeur_cible),
                default      => $kpis,
            };
            $kpis = $kpis->values();
        }

        $objectifs = Objectif::actif()->orderBy('titre')->get(['id', 'titre']);

        // Stats globales (toujours sur l'ensemble pour cohérence)
        $allKpis = Kpi::all();
        $stats = [
            'total'        => $allKpis->count(),
            'au_dessus'    => $allKpis->filter(fn($k) => $k->valeur_cible && $k->valeur_actuelle >= $k->valeur_cible)->count(),
            'en_alerte'    => $allKpis->filter(fn($k) => $k->valeur_cible && $k->progression < 50)->count(),
            'sans_cible'   => $allKpis->filter(fn($k) => ! $k->valeur_cible)->count(),
        ];

        return view('objectif.kpi.index', compact('kpis', 'objectifs', 'stats'));
    }

    public function show(Kpi $kpi)
    {
        $kpi->load(['objectif', 'valeurs.auteur']);
        return view('objectif.kpi.show', compact('kpi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'objectif_id'     => 'nullable|exists:intranet_objectifs,id',
            'valeur_cible'    => 'nullable|numeric',
            'valeur_actuelle' => 'nullable|numeric',
            'unite'           => 'nullable|string|max:50',
            'tendance'        => 'nullable|in:hausse,baisse,stable',
            'periodicite'     => 'nullable|in:quotidien,hebdo,mensuel,trimestriel,annuel',
        ]);

        Kpi::create(array_merge($validated, ['created_by' => auth()->id()]));

        return back()->with('success', 'KPI créé.');
    }

    public function update(Request $request, Kpi $kpi)
    {
        $validated = $request->validate([
            'titre'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'objectif_id'     => 'nullable|exists:intranet_objectifs,id',
            'valeur_cible'    => 'nullable|numeric',
            'valeur_actuelle' => 'nullable|numeric',
            'unite'           => 'nullable|string|max:50',
            'tendance'        => 'nullable|in:hausse,baisse,stable',
            'periodicite'     => 'nullable|in:quotidien,hebdo,mensuel,trimestriel,annuel',
        ]);

        $kpi->update($validated);

        return back()->with('success', 'KPI mis à jour.');
    }

    public function destroy(Kpi $kpi)
    {
        $kpi->valeurs()->delete();
        $kpi->delete();
        return back()->with('success', 'KPI supprimé.');
    }

    /**
     * Saisir une nouvelle valeur de mesure pour un KPI.
     */
    public function saisirValeur(Request $request, Kpi $kpi)
    {
        $validated = $request->validate([
            'valeur'      => 'required|numeric',
            'date_mesure' => 'required|date',
            'commentaire' => 'nullable|string',
        ]);

        KpiValeur::create(array_merge($validated, [
            'kpi_id'     => $kpi->id,
            'created_by' => auth()->id(),
        ]));

        // Mettre à jour la valeur actuelle du KPI
        $derniere = $kpi->valeurs()->orderByDesc('date_mesure')->first();
        if ($derniere) {
            // Calcul de tendance
            $avantDerniere = $kpi->valeurs()->where('id', '!=', $derniere->id)->orderByDesc('date_mesure')->first();
            $tendance = 'stable';
            if ($avantDerniere) {
                if ((float) $derniere->valeur > (float) $avantDerniere->valeur) $tendance = 'hausse';
                elseif ((float) $derniere->valeur < (float) $avantDerniere->valeur) $tendance = 'baisse';
            }
            $kpi->update([
                'valeur_actuelle' => $derniere->valeur,
                'tendance'        => $tendance,
            ]);
        }

        return back()->with('success', 'Valeur enregistrée.');
    }
}
