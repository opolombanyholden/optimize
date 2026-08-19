<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\SanctionRequest;
use App\Models\Employee;
use App\Models\Sanction;
use Illuminate\Http\Request;

class SanctionController extends Controller
{
    public function index(Request $request)
    {
        $sanctions = Sanction::query()
            ->with(['employee', 'decideur'])
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->niveau_gravite, fn($q, $n) => $q->where('niveau_gravite', $n))
            ->orderByDesc('date_notification')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::where('statut', 1)->orderBy('noms')->get();

        return view('rh.sanctions.index', compact('sanctions', 'employees'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.sanctions.create', compact('employees'));
    }

    public function store(SanctionRequest $request)
    {
        $data = $request->validated();
        $data['decidee_par'] = auth()->id();
        $data['statut'] = $data['statut'] ?? 1;
        Sanction::create($data);
        return redirect()->route('rh.sanctions.index')->with('success', 'Sanction enregistree.');
    }

    public function show(Sanction $sanction)
    {
        $sanction->load(['employee', 'decideur']);
        return view('rh.sanctions.show', compact('sanction'));
    }

    public function edit(Sanction $sanction)
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.sanctions.edit', compact('sanction', 'employees'));
    }

    public function update(SanctionRequest $request, Sanction $sanction)
    {
        $sanction->update($request->validated());
        return redirect()->route('rh.sanctions.index')->with('success', 'Sanction mise a jour.');
    }

    public function destroy(Sanction $sanction)
    {
        $sanction->delete();
        return redirect()->route('rh.sanctions.index')->with('success', 'Sanction supprimee.');
    }
}
