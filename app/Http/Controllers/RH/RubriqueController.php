<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\RubriqueRequest;
use App\Models\Rubrique;
use Illuminate\Http\Request;

class RubriqueController extends Controller
{
    public function index(Request $request)
    {
        $rubriques = Rubrique::query()
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', $request->statut))
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('libelle', 'ilike', "%$s%")->orWhere('code', 'ilike', "%$s%")))
            ->orderBy('type')
            ->orderBy('ordre_affichage')
            ->paginate(30)
            ->withQueryString();

        return view('rh.rubriques.index', compact('rubriques'));
    }

    public function create()
    {
        return view('rh.rubriques.create');
    }

    public function store(RubriqueRequest $request)
    {
        $data = $request->validated();
        $data['statut'] = $data['statut'] ?? 1;
        $data['ordre_affichage'] = $data['ordre_affichage'] ?? 0;
        Rubrique::create($data);

        return redirect()->route('rh.rubriques.index')->with('success', 'Rubrique creee.');
    }

    public function show(Rubrique $rubrique)
    {
        return view('rh.rubriques.show', compact('rubrique'));
    }

    public function edit(Rubrique $rubrique)
    {
        return view('rh.rubriques.edit', compact('rubrique'));
    }

    public function update(RubriqueRequest $request, Rubrique $rubrique)
    {
        $rubrique->update($request->validated());
        return redirect()->route('rh.rubriques.index')->with('success', 'Rubrique mise a jour.');
    }

    public function destroy(Rubrique $rubrique)
    {
        $rubrique->delete();
        return redirect()->route('rh.rubriques.index')->with('success', 'Rubrique supprimee.');
    }
}
