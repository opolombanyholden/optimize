<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\DepartRequest;
use App\Models\Depart;
use App\Models\Employee;
use Illuminate\Http\Request;

class DepartController extends Controller
{
    public function index(Request $request)
    {
        $departs = Depart::query()
            ->with(['employee', 'traitant'])
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->type_depart, fn($q, $t) => $q->where('type_depart', $t))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', $request->statut))
            ->orderByDesc('date_effet')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::orderBy('noms')->get();

        return view('rh.departs.index', compact('departs', 'employees'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.departs.create', compact('employees'));
    }

    public function store(DepartRequest $request)
    {
        $data = $request->validated();
        $data['traite_par'] = auth()->id();
        $data['statut'] = $data['statut'] ?? 0;
        $depart = Depart::create($data);

        // Si le départ est finalisé, passer l'employé en statut=3 (parti)
        if ($depart->statut == 2) {
            $depart->employee->update(['statut' => 3]);
        }

        return redirect()->route('rh.departs.index')->with('success', 'Depart enregistre.');
    }

    public function show(Depart $depart)
    {
        $depart->load(['employee', 'traitant']);
        return view('rh.departs.show', compact('depart'));
    }

    public function edit(Depart $depart)
    {
        $employees = Employee::orderBy('noms')->get();
        return view('rh.departs.edit', compact('depart', 'employees'));
    }

    public function update(DepartRequest $request, Depart $depart)
    {
        $data = $request->validated();
        $depart->update($data);

        if ($depart->statut == 2 && $depart->employee->statut != 3) {
            $depart->employee->update(['statut' => 3]);
        }

        return redirect()->route('rh.departs.index')->with('success', 'Depart mis a jour.');
    }

    public function destroy(Depart $depart)
    {
        $depart->delete();
        return redirect()->route('rh.departs.index')->with('success', 'Depart supprime.');
    }
}
