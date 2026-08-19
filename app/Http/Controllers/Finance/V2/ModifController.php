<?php

namespace App\Http\Controllers\Finance\V2;

use App\Http\Controllers\Controller;
use App\Models\Finance\BudgetSource;
use App\Models\Finance\Modif;
use App\Services\Finance\ModifService;
use Illuminate\Http\Request;

class ModifController extends Controller
{
    public function index()
    {
        $modifs = Modif::with(['budgetEmission.source', 'budgetEmission.ligne',
                              'budgetReception.source', 'budgetReception.ligne',
                              'soumetteur'])
            ->orderByDesc('id')
            ->paginate(20);
        return view('finance.v2.modifs.index', compact('modifs'));
    }

    public function create()
    {
        $budgetSources = BudgetSource::with(['source', 'ligne', 'exercice'])
            ->where('montant', '>', 0)
            ->get();
        return view('finance.v2.modifs.create', compact('budgetSources'));
    }

    public function store(Request $r, ModifService $service)
    {
        $data = $r->validate([
            'comment'             => 'required|string|max:255',
            'date'                => 'nullable|date',
            'montant'             => 'required|numeric|min:0.01',
            'budget_emission_id'  => 'nullable|exists:budget_sources,id',
            'budget_reception_id' => 'required|exists:budget_sources,id|different:budget_emission_id',
        ]);
        $data['status'] = 0;
        $modif = Modif::create($data);
        return redirect()->route('finance.v2.modifs.show', $modif)->with('success', 'Modification créée en brouillon.');
    }

    public function show(Modif $modif)
    {
        $modif->load(['budgetEmission.source', 'budgetEmission.ligne',
                      'budgetReception.source', 'budgetReception.ligne',
                      'soumetteur', 'approbateur', 'applicateur']);
        return view('finance.v2.modifs.show', compact('modif'));
    }

    public function soumettre(Modif $modif)
    {
        if (!$modif->est_soumissible) return back()->with('error', 'Non soumissible.');
        $modif->update(['status' => 1, 'soumis_par' => auth()->id(), 'soumis_at' => now()]);
        return back()->with('success', 'Modification soumise.');
    }

    public function approuver(Modif $modif, ModifService $service)
    {
        if (!$modif->est_approuvable) return back()->with('error', 'Non approuvable.');
        $err = $service->verifier($modif);
        if (!empty($err)) return back()->with('error', implode(' ', $err));
        $modif->update(['status' => 2, 'approuve_par' => auth()->id(), 'approuve_at' => now()]);
        return back()->with('success', 'Modification approuvée.');
    }

    public function rejeter(Request $r, Modif $modif)
    {
        if (!$modif->est_rejetable) return back()->with('error', 'Non rejetable.');
        $data = $r->validate(['motif_rejet' => 'required|string|max:500']);
        $modif->update(['status' => 4, 'motif_rejet' => $data['motif_rejet'], 'approuve_par' => auth()->id(), 'approuve_at' => now()]);
        return back()->with('success', 'Modification rejetée.');
    }

    public function appliquer(Modif $modif, ModifService $service)
    {
        try { $service->appliquer($modif, auth()->id()); }
        catch (\Throwable $e) { return back()->with('error', $e->getMessage()); }
        return back()->with('success', 'Modification appliquée : transfert effectif.');
    }

    public function annuler(Modif $modif, ModifService $service)
    {
        try { $service->annuler($modif); }
        catch (\Throwable $e) { return back()->with('error', $e->getMessage()); }
        return back()->with('success', 'Application annulée.');
    }
}
