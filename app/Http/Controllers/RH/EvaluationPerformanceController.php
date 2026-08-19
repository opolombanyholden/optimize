<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\EvaluationPerformanceRequest;
use App\Models\Employee;
use App\Models\EvaluationPerformance;
use App\Models\User;
use Illuminate\Http\Request;

class EvaluationPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $evaluations = EvaluationPerformance::query()
            ->with(['employee', 'evaluateur'])
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->periode, fn($q, $p) => $q->where('periode', $p))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', $request->statut))
            ->orderByDesc('date_evaluation')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::where('statut', 1)->orderBy('noms')->get();

        return view('rh.evaluations-performance.index', compact('evaluations', 'employees'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        $evaluateurs = User::where('statut', 1)->orderBy('name')->get();
        return view('rh.evaluations-performance.create', compact('employees', 'evaluateurs'));
    }

    public function store(EvaluationPerformanceRequest $request)
    {
        $data = $request->validated();
        $data['evaluateur_id'] = $data['evaluateur_id'] ?? auth()->id();
        $data['statut'] = $data['statut'] ?? 0;
        EvaluationPerformance::create($data);
        return redirect()->route('rh.evaluations-performance.index')->with('success', 'Evaluation creee.');
    }

    public function show(EvaluationPerformance $evaluations_performance)
    {
        $evaluations_performance->load(['employee', 'evaluateur']);
        return view('rh.evaluations-performance.show', ['evaluation' => $evaluations_performance]);
    }

    public function edit(EvaluationPerformance $evaluations_performance)
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        $evaluateurs = User::where('statut', 1)->orderBy('name')->get();
        return view('rh.evaluations-performance.edit', [
            'evaluation' => $evaluations_performance,
            'employees' => $employees,
            'evaluateurs' => $evaluateurs,
        ]);
    }

    public function update(EvaluationPerformanceRequest $request, EvaluationPerformance $evaluations_performance)
    {
        $evaluations_performance->update($request->validated());
        return redirect()->route('rh.evaluations-performance.index')->with('success', 'Evaluation mise a jour.');
    }

    public function destroy(EvaluationPerformance $evaluations_performance)
    {
        $evaluations_performance->delete();
        return redirect()->route('rh.evaluations-performance.index')->with('success', 'Evaluation supprimee.');
    }
}
