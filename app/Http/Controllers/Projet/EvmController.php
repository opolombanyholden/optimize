<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetEvm;
use Illuminate\Http\Request;

class EvmController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load(['evm.auteur']);
        $evms = $projet->evm;
        $latest = $projet->dernierEvm;
        return view('projet.evm.index', compact('projet', 'evms', 'latest'));
    }

    public function store(Request $request, Projet $projet)
    {
        $request->validate([
            'date_mesure' => 'required|date',
            'bac' => 'required|numeric', 'pv' => 'required|numeric',
            'ev' => 'required|numeric', 'ac' => 'required|numeric',
            'commentaire' => 'nullable|string',
        ]);

        $d = $request->only(['date_mesure', 'bac', 'pv', 'ev', 'ac', 'commentaire']);
        $d['sv']  = $d['ev'] - $d['pv'];
        $d['cv']  = $d['ev'] - $d['ac'];
        $d['spi'] = $d['pv'] != 0 ? round($d['ev'] / $d['pv'], 4) : 0;
        $d['cpi'] = $d['ac'] != 0 ? round($d['ev'] / $d['ac'], 4) : 0;
        $d['etc'] = $d['cpi'] != 0 ? round(($d['bac'] - $d['ev']) / $d['cpi'], 2) : 0;
        $d['eac'] = $d['ac'] + $d['etc'];
        $d['created_by'] = auth()->id();

        $projet->evm()->create($d);
        return back()->with('success', 'Mesure EVM enregistrée.');
    }

    public function destroy(Projet $projet, ProjetEvm $evm)
    {
        $evm->delete();
        return back()->with('success', 'Mesure supprimée.');
    }
}
