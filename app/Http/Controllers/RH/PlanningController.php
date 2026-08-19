<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\PlanningRequest;
use App\Models\Employee;
use App\Models\Planning;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $debut = $request->debut ? Carbon::parse($request->debut) : Carbon::now()->startOfWeek();
        $fin = $request->fin ? Carbon::parse($request->fin) : (clone $debut)->endOfWeek();

        $plannings = Planning::query()
            ->with('employee')
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->type_journee, fn($q, $t) => $q->where('type_journee', $t))
            ->whereBetween('date_jour', [$debut->toDateString(), $fin->toDateString()])
            ->orderBy('date_jour')
            ->orderBy('employee_id')
            ->paginate(50)
            ->withQueryString();

        $employees = Employee::where('statut', 1)->orderBy('noms')->get();

        return view('rh.plannings.index', compact('plannings', 'employees', 'debut', 'fin'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.plannings.create', compact('employees'));
    }

    public function store(PlanningRequest $request)
    {
        $data = $request->validated();
        // Évite doublon journalier
        Planning::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date_jour' => $data['date_jour']],
            $data
        );
        return redirect()->route('rh.plannings.index')->with('success', 'Planning enregistre.');
    }

    public function show(Planning $planning)
    {
        $planning->load('employee');
        return view('rh.plannings.show', compact('planning'));
    }

    public function edit(Planning $planning)
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.plannings.edit', compact('planning', 'employees'));
    }

    public function update(PlanningRequest $request, Planning $planning)
    {
        $planning->update($request->validated());
        return redirect()->route('rh.plannings.index')->with('success', 'Planning mis a jour.');
    }

    public function destroy(Planning $planning)
    {
        $planning->delete();
        return redirect()->route('rh.plannings.index')->with('success', 'Planning supprime.');
    }
}
