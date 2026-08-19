<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\QualificationRequest;
use App\Models\Employee;
use App\Models\Qualification;
use Illuminate\Http\Request;

class QualificationController extends Controller
{
    public function index(Request $request)
    {
        $qualifications = Qualification::query()
            ->with('employee')
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('label', 'ilike', "%$s%")->orWhere('organisme', 'ilike', "%$s%")))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::where('statut', 1)->orderBy('noms')->get();

        return view('rh.qualifications.index', compact('qualifications', 'employees'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.qualifications.create', compact('employees'));
    }

    public function store(QualificationRequest $request)
    {
        $data = $request->validated();
        $data['statut'] = $data['statut'] ?? 1;
        Qualification::create($data);

        return redirect()->route('rh.qualifications.index')->with('success', 'Qualification ajoutee avec succes.');
    }

    public function show(Qualification $qualification)
    {
        $qualification->load('employee');
        return view('rh.qualifications.show', compact('qualification'));
    }

    public function edit(Qualification $qualification)
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.qualifications.edit', compact('qualification', 'employees'));
    }

    public function update(QualificationRequest $request, Qualification $qualification)
    {
        $qualification->update($request->validated());
        return redirect()->route('rh.qualifications.index')->with('success', 'Qualification mise a jour.');
    }

    public function destroy(Qualification $qualification)
    {
        $qualification->delete();
        return redirect()->route('rh.qualifications.index')->with('success', 'Qualification supprimee.');
    }
}
