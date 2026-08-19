<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\MissionRequest;
use App\Models\Employee;
use App\Models\Mission;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function index(Request $request)
    {
        $missions = Mission::query()
            ->with('employee')
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', $request->statut))
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('label', 'ilike', "%$s%")->orWhere('lieu', 'ilike', "%$s%")))
            ->orderByDesc('debut')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::where('statut', 1)->orderBy('noms')->get();

        return view('rh.missions.index', compact('missions', 'employees'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.missions.create', compact('employees'));
    }

    public function store(MissionRequest $request)
    {
        $data = $request->validated();
        $data['statut'] = $data['statut'] ?? 0;
        Mission::create($data);

        return redirect()->route('rh.missions.index')->with('success', 'Mission planifiee.');
    }

    public function show(Mission $mission)
    {
        $mission->load('employee');
        return view('rh.missions.show', compact('mission'));
    }

    public function edit(Mission $mission)
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.missions.edit', compact('mission', 'employees'));
    }

    public function update(MissionRequest $request, Mission $mission)
    {
        $mission->update($request->validated());
        return redirect()->route('rh.missions.index')->with('success', 'Mission mise a jour.');
    }

    public function destroy(Mission $mission)
    {
        $mission->delete();
        return redirect()->route('rh.missions.index')->with('success', 'Mission supprimee.');
    }
}
