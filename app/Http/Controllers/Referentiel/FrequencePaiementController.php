<?php

namespace App\Http\Controllers\Referentiel;

use App\Http\Controllers\Controller;
use App\Models\ContratFournisseur;
use App\Models\FrequencePaiement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FrequencePaiementController extends Controller
{
    public function index()
    {
        $frequences = FrequencePaiement::orderBy('ordre')->orderBy('libelle')->get();
        return view('referentiel.frequences-paiement.index', compact('frequences'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['code'] = $data['code'] ?: Str::slug($data['libelle']);
        $data['actif'] = $request->boolean('actif', true);
        FrequencePaiement::create($data);
        return back()->with('success', 'Fréquence de paiement créée.');
    }

    public function update(Request $request, FrequencePaiement $frequence)
    {
        $data = $this->validateData($request, $frequence->id);
        $data['actif'] = $request->boolean('actif');
        $frequence->update($data);
        return back()->with('success', 'Fréquence mise à jour.');
    }

    public function destroy(FrequencePaiement $frequence)
    {
        $used = ContratFournisseur::where('frequence_paiement', $frequence->code)->count();
        if ($used > 0) {
            return back()->with('error', "Suppression impossible : {$used} engagement(s) utilisent cette fréquence.");
        }
        $frequence->delete();
        return back()->with('success', 'Fréquence supprimée.');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code'           => ['nullable', 'string', 'max:40', 'unique:frequences_paiement,code,'.($id ?? 'NULL')],
            'libelle'        => 'required|string|max:255',
            'description'    => 'nullable|string|max:500',
            'mois_increment' => 'nullable|integer|min:1|max:24',
            'ordre'          => 'nullable|integer',
        ]);
    }
}
