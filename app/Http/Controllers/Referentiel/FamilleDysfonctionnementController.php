<?php

namespace App\Http\Controllers\Referentiel;

use App\Http\Controllers\Controller;
use App\Models\FamilleDysfonctionnement;
use Illuminate\Http\Request;

class FamilleDysfonctionnementController extends Controller
{
    public function index()
    {
        $familles = FamilleDysfonctionnement::withCount('types')->orderBy('libelle')->get();
        return view('referentiel.familles-dysfonctionnement.index', compact('familles'));
    }

    public function store(Request $request)
    {
        FamilleDysfonctionnement::create($this->validateData($request));
        return back()->with('success', 'Famille de dysfonctionnement créée.');
    }

    public function update(Request $request, FamilleDysfonctionnement $famille)
    {
        $famille->update($this->validateData($request));
        return back()->with('success', 'Famille mise à jour.');
    }

    public function destroy(FamilleDysfonctionnement $famille)
    {
        $count = $famille->types()->count();
        if ($count > 0) {
            return back()->with('error', "Suppression impossible : {$count} type(s) rattaché(s) à cette famille.");
        }
        $famille->delete();
        return back()->with('success', 'Famille supprimée.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'libelle'     => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'couleur'     => 'nullable|string|max:20',
        ]);
    }
}
