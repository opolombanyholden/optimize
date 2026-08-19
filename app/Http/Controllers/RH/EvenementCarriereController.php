<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\EvenementCarriereRequest;
use App\Models\Employee;
use App\Models\EvenementCarriere;
use App\Models\TypeEvenementCarriere;
use Illuminate\Http\Request;

class EvenementCarriereController extends Controller
{
    public function index(Request $request)
    {
        $evenements = EvenementCarriere::query()
            ->with(['employee', 'type'])
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->type_id, fn($q, $tid) => $q->where('typesevenementscarriere_id', $tid))
            ->orderByDesc('date_effet')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        $types = TypeEvenementCarriere::orderBy('libelle')->get();

        return view('rh.evenements-carriere.index', compact('evenements', 'employees', 'types'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        $types = TypeEvenementCarriere::orderBy('libelle')->get();
        return view('rh.evenements-carriere.create', compact('employees', 'types'));
    }

    public function store(EvenementCarriereRequest $request)
    {
        EvenementCarriere::create($request->validated());
        return redirect()->route('rh.evenements-carriere.index')->with('success', 'Evenement de carriere enregistre.');
    }

    public function show(EvenementCarriere $evenements_carriere)
    {
        $evenements_carriere->load(['employee', 'type']);
        return view('rh.evenements-carriere.show', ['evenement' => $evenements_carriere]);
    }

    public function edit(EvenementCarriere $evenements_carriere)
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        $types = TypeEvenementCarriere::orderBy('libelle')->get();
        return view('rh.evenements-carriere.edit', [
            'evenement' => $evenements_carriere,
            'employees' => $employees,
            'types'     => $types,
        ]);
    }

    public function update(EvenementCarriereRequest $request, EvenementCarriere $evenements_carriere)
    {
        $evenements_carriere->update($request->validated());
        return redirect()->route('rh.evenements-carriere.index')->with('success', 'Evenement de carriere mis a jour.');
    }

    public function destroy(EvenementCarriere $evenements_carriere)
    {
        $evenements_carriere->delete();
        return redirect()->route('rh.evenements-carriere.index')->with('success', 'Evenement supprime.');
    }
}
