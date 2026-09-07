<?php

namespace App\Http\Controllers\Referentiel;

use App\Http\Controllers\Controller;
use App\Models\Dysfonctionnement;
use App\Models\FamilleDysfonctionnement;
use App\Models\TypeDysfonctionnement;
use Illuminate\Http\Request;

class TypeDysfonctionnementController extends Controller
{
    public function index()
    {
        $types = TypeDysfonctionnement::with('famille')->orderBy('libelle')->get();
        $familles = FamilleDysfonctionnement::orderBy('libelle')->get();
        return view('referentiel.types-dysfonctionnement.index', compact('types', 'familles'));
    }

    public function store(Request $request)
    {
        TypeDysfonctionnement::create($this->validateData($request));
        return back()->with('success', 'Type de dysfonctionnement créé.');
    }

    public function update(Request $request, TypeDysfonctionnement $type)
    {
        $type->update($this->validateData($request));
        return back()->with('success', 'Type mis à jour.');
    }

    public function destroy(TypeDysfonctionnement $type)
    {
        $used = Dysfonctionnement::where('type_id', $type->id)->count();
        if ($used > 0) {
            return back()->with('error', "Suppression impossible : ce type est utilisé par {$used} dysfonctionnement(s).");
        }
        $type->delete();
        return back()->with('success', 'Type supprimé.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'libelle'     => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'famille_id'  => 'nullable|exists:familles_dysfonctionnement,id',
        ]);
    }
}
