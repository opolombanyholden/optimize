<?php

namespace App\Http\Controllers\Referentiel;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\NatureIntervention;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NatureInterventionController extends Controller
{
    public function index()
    {
        $natures = NatureIntervention::withCount('interventions')->orderBy('libelle')->get();
        return view('referentiel.natures-intervention.index', compact('natures'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['code'] = $data['code'] ?: Str::slug($data['libelle']);
        $data['actif'] = $request->boolean('actif', true);
        NatureIntervention::create($data);
        return back()->with('success', 'Nature d\'intervention créée.');
    }

    public function update(Request $request, NatureIntervention $nature)
    {
        $data = $this->validateData($request, $nature->id);
        $data['actif'] = $request->boolean('actif');
        $nature->update($data);
        return back()->with('success', 'Nature mise à jour.');
    }

    public function destroy(NatureIntervention $nature)
    {
        $count = Intervention::where('nature_id', $nature->id)->count();
        if ($count > 0) {
            return back()->with('error', "Suppression impossible : {$count} intervention(s) utilise(nt) cette nature.");
        }
        $nature->delete();
        return back()->with('success', 'Nature supprimée.');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code'        => 'nullable|string|max:60|unique:natures_intervention,code,' . ($id ?? 'NULL'),
            'libelle'     => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'couleur'     => 'nullable|string|max:20',
        ]);
    }
}
