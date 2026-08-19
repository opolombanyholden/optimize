<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\CongeSoldeRequest;
use App\Models\CongeSolde;
use App\Models\Employee;
use Illuminate\Http\Request;

class CongeSoldeController extends Controller
{
    public function index(Request $request)
    {
        $annee = (int) ($request->annee ?? now()->year);

        $soldes = CongeSolde::query()
            ->with('employee')
            ->where('annee', $annee)
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->type_conge, fn($q, $t) => $q->where('type_conge', $t))
            ->orderBy('employee_id')
            ->paginate(30)
            ->withQueryString();

        $employees = Employee::where('statut', 1)->orderBy('noms')->get();

        return view('rh.conges-soldes.index', compact('soldes', 'employees', 'annee'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.conges-soldes.create', compact('employees'));
    }

    public function store(CongeSoldeRequest $request)
    {
        CongeSolde::create($request->validated());
        return redirect()->route('rh.conges-soldes.index')->with('success', 'Solde de conges cree.');
    }

    public function show(CongeSolde $conges_solde)
    {
        $conges_solde->load('employee');
        return view('rh.conges-soldes.show', ['solde' => $conges_solde]);
    }

    public function edit(CongeSolde $conges_solde)
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.conges-soldes.edit', [
            'solde' => $conges_solde,
            'employees' => $employees,
        ]);
    }

    public function update(CongeSoldeRequest $request, CongeSolde $conges_solde)
    {
        $conges_solde->update($request->validated());
        return redirect()->route('rh.conges-soldes.index')->with('success', 'Solde de conges mis a jour.');
    }

    public function destroy(CongeSolde $conges_solde)
    {
        $conges_solde->delete();
        return redirect()->route('rh.conges-soldes.index')->with('success', 'Solde supprime.');
    }
}
