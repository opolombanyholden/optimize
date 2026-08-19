<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Employee;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        $absences = Absence::query()
            ->with(['employee'])
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->type_absence, fn($q, $t) => $q->where('type_abscence', $t))
            ->when($request->statut !== null, fn($q) => $q->where('statut', $request->statut))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('rh.absences.index', compact('absences'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->get();

        return view('rh.absences.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type_abscence' => 'nullable|string|max:50',
            'introduction' => 'nullable|string',
            'description' => 'nullable|string',
            'debut' => 'required|date',
            'fin' => 'required|date|after_or_equal:debut',
            'employee_id' => 'required|exists:employees,id',
        ]);

        $validated['statut'] = 0;
        Absence::create($validated);

        return redirect()->route('rh.absences.index')->with('success', 'Absence creee avec succes.');
    }

    public function show(string $id)
    {
        $absence = Absence::with(['employee', 'valideur'])->findOrFail($id);

        return view('rh.absences.show', compact('absence'));
    }

    public function edit(string $id)
    {
        $absence = Absence::findOrFail($id);
        $employees = Employee::where('statut', 1)->get();

        return view('rh.absences.edit', compact('absence', 'employees'));
    }

    public function update(Request $request, string $id)
    {
        $absence = Absence::findOrFail($id);

        $validated = $request->validate([
            'label' => 'sometimes|required|string|max:255',
            'type_abscence' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'debut' => 'sometimes|date',
            'fin' => 'sometimes|date|after_or_equal:debut',
            'statut' => 'nullable|integer|in:0,1,2,3',
        ]);

        if (isset($validated['statut']) && $validated['statut'] == 2) {
            $validated['valide_par'] = $request->user()->id;
            $validated['date_validation'] = now();
        }

        $absence->update($validated);

        return redirect()->route('rh.absences.index')->with('success', 'Absence mise a jour avec succes.');
    }

    public function destroy(string $id)
    {
        $absence = Absence::findOrFail($id);
        $absence->delete();

        return redirect()->route('rh.absences.index')->with('success', 'Absence supprimee avec succes.');
    }
}
