<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\RH\AffilieRequest;
use App\Models\Affilie;
use App\Models\Employee;
use Illuminate\Http\Request;

class AffilieController extends Controller
{
    public function index(Request $request)
    {
        $affilies = Affilie::query()
            ->with('employee')
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->liens, fn($q, $l) => $q->where('liens', $l))
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('noms', 'ilike', "%$s%")->orWhere('prenoms', 'ilike', "%$s%")))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::where('statut', 1)->orderBy('noms')->get();

        return view('rh.affilies.index', compact('affilies', 'employees'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.affilies.create', compact('employees'));
    }

    public function store(AffilieRequest $request)
    {
        $data = $request->validated();
        $data['statut'] = $data['statut'] ?? 1;
        Affilie::create($data);

        return redirect()->route('rh.affilies.index')->with('success', 'Ayant-droit ajoute avec succes.');
    }

    public function show(Affilie $affilie)
    {
        $affilie->load('employee');
        return view('rh.affilies.show', compact('affilie'));
    }

    public function edit(Affilie $affilie)
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.affilies.edit', compact('affilie', 'employees'));
    }

    public function update(AffilieRequest $request, Affilie $affilie)
    {
        $affilie->update($request->validated());
        return redirect()->route('rh.affilies.index')->with('success', 'Ayant-droit mis a jour avec succes.');
    }

    public function destroy(Affilie $affilie)
    {
        $affilie->delete();
        return redirect()->route('rh.affilies.index')->with('success', 'Ayant-droit supprime avec succes.');
    }
}
