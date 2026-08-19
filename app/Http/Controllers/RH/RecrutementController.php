<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Recrutement;
use Illuminate\Http\Request;

class RecrutementController extends Controller
{
    public function index(Request $request)
    {
        $recrutements = Recrutement::query()
            ->withCount(['profils', 'postulants'])
            ->when($request->statut !== null, fn($q) => $q->where('statut', $request->statut))
            ->when($request->search, fn($q, $s) => $q->where('label', 'like', "%{$s}%"))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('rh.recrutements.index', compact('recrutements'));
    }

    public function create()
    {
        return view('rh.recrutements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'introduction' => 'nullable|string',
            'description' => 'nullable|string',
            'debut' => 'nullable|date',
            'fin' => 'nullable|date|after_or_equal:debut',
        ]);

        $validated['statut'] = 0;
        Recrutement::create($validated);

        return redirect()->route('rh.recrutements.index')->with('success', 'Recrutement cree avec succes.');
    }

    public function show(string $id)
    {
        $recrutement = Recrutement::with(['profils', 'postulants'])->findOrFail($id);

        return view('rh.recrutements.show', compact('recrutement'));
    }

    public function edit(string $id)
    {
        $recrutement = Recrutement::findOrFail($id);

        return view('rh.recrutements.edit', compact('recrutement'));
    }

    public function update(Request $request, string $id)
    {
        $recrutement = Recrutement::findOrFail($id);

        $validated = $request->validate([
            'label' => 'sometimes|required|string|max:255',
            'introduction' => 'nullable|string',
            'description' => 'nullable|string',
            'debut' => 'nullable|date',
            'fin' => 'nullable|date|after_or_equal:debut',
            'statut' => 'nullable|integer|in:0,1,2',
        ]);

        $recrutement->update($validated);

        return redirect()->route('rh.recrutements.index')->with('success', 'Recrutement mis a jour avec succes.');
    }

    public function destroy(string $id)
    {
        $recrutement = Recrutement::findOrFail($id);
        $recrutement->delete();

        return redirect()->route('rh.recrutements.index')->with('success', 'Recrutement supprime avec succes.');
    }
}
