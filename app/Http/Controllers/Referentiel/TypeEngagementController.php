<?php

namespace App\Http\Controllers\Referentiel;

use App\Http\Controllers\Controller;
use App\Models\ContratFournisseur;
use App\Models\TypeEngagement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TypeEngagementController extends Controller
{
    public function index()
    {
        $types = TypeEngagement::orderBy('ordre')->orderBy('libelle')->get();
        return view('referentiel.types-engagement.index', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['code'] = $data['code'] ?: Str::slug($data['libelle']);
        $data['actif'] = $request->boolean('actif', true);
        TypeEngagement::create($data);
        return back()->with('success', 'Type d\'engagement créé.');
    }

    public function update(Request $request, TypeEngagement $type)
    {
        $data = $this->validateData($request, $type->id);
        $data['actif'] = $request->boolean('actif');
        $type->update($data);
        return back()->with('success', 'Type mis à jour.');
    }

    public function destroy(TypeEngagement $type)
    {
        $used = ContratFournisseur::where('type', $type->code)->count();
        if ($used > 0) {
            return back()->with('error', "Suppression impossible : {$used} engagement(s) utilisent ce type.");
        }
        $type->delete();
        return back()->with('success', 'Type supprimé.');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code'        => ['nullable', 'string', 'max:40', 'unique:types_engagement,code,'.($id ?? 'NULL')],
            'libelle'     => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'ordre'       => 'nullable|integer',
        ]);
    }
}
