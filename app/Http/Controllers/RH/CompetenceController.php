<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\CompetenceRequest;
use App\Models\Competence;
use App\Models\Employee;
use Illuminate\Http\Request;

class CompetenceController extends Controller
{
    public function index(Request $request)
    {
        $competences = Competence::query()
            ->with('employee')
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->niveau, fn($q, $n) => $q->where('niveau', $n))
            ->when($request->q, fn($q, $s) => $q->where('label', 'ilike', "%$s%"))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::where('statut', 1)->orderBy('noms')->get();

        return view('rh.competences.index', compact('competences', 'employees'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.competences.create', compact('employees'));
    }

    public function store(CompetenceRequest $request)
    {
        $data = $request->validated();
        $data['statut'] = $data['statut'] ?? 1;
        Competence::create($data);

        return redirect()->route('rh.competences.index')->with('success', 'Competence ajoutee.');
    }

    public function show(Competence $competence)
    {
        $competence->load('employee');
        return view('rh.competences.show', compact('competence'));
    }

    public function edit(Competence $competence)
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.competences.edit', compact('competence', 'employees'));
    }

    public function update(CompetenceRequest $request, Competence $competence)
    {
        $competence->update($request->validated());
        return redirect()->route('rh.competences.index')->with('success', 'Competence mise a jour.');
    }

    public function destroy(Competence $competence)
    {
        $competence->delete();
        return redirect()->route('rh.competences.index')->with('success', 'Competence supprimee.');
    }
}
