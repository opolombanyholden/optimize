<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\FormationRequest;
use App\Models\Employee;
use App\Models\Formation;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function index(Request $request)
    {
        $formations = Formation::query()
            ->with('employee')
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', $request->statut))
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('label', 'ilike', "%$s%")->orWhere('organisme', 'ilike', "%$s%")))
            ->orderByDesc('debut')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::where('statut', 1)->orderBy('noms')->get();

        return view('rh.formations.index', compact('formations', 'employees'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.formations.create', compact('employees'));
    }

    public function store(FormationRequest $request)
    {
        $data = $request->validated();
        $data['statut'] = $data['statut'] ?? 0;
        Formation::create($data);

        return redirect()->route('rh.formations.index')->with('success', 'Formation creee.');
    }

    public function show(Formation $formation)
    {
        $formation->load('employee');
        return view('rh.formations.show', compact('formation'));
    }

    public function edit(Formation $formation)
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.formations.edit', compact('formation', 'employees'));
    }

    public function update(FormationRequest $request, Formation $formation)
    {
        $formation->update($request->validated());
        return redirect()->route('rh.formations.index')->with('success', 'Formation mise a jour.');
    }

    public function destroy(Formation $formation)
    {
        $formation->delete();
        return redirect()->route('rh.formations.index')->with('success', 'Formation supprimee.');
    }
}
