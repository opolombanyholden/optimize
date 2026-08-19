<?php

namespace App\Http\Controllers\Systeme;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\TypeOrganisation;
use App\Models\User;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    public function index(Request $request)
    {
        $organisations = Organisation::query()
            ->with(['type', 'chef', 'parent'])
            ->when($request->search, fn($q, $s) => $q->where('label', 'like', "%{$s}%"))
            ->when($request->type, fn($q, $t) => $q->where('typesorganisation_id', $t))
            ->when($request->statut, fn($q, $s) => $q->where('statut', $s))
            ->orderBy('label')
            ->paginate(15)
            ->withQueryString();

        $types = TypeOrganisation::orderBy('label')->get();

        return view('systeme.organisations.index', compact('organisations', 'types'));
    }

    public function create()
    {
        $types = TypeOrganisation::orderBy('label')->get();
        $parents = Organisation::where('statut', 1)->orderBy('label')->get();
        $users = User::orderBy('name')->get();

        return view('systeme.organisations.create', compact('types', 'parents', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'introduction' => 'nullable|string',
            'description' => 'nullable|string',
            'typesorganisation_id' => 'required|exists:typesorganisations,id',
            'chefs' => 'nullable|exists:users,id',
            'peres' => 'nullable|exists:organisations,id',
            'statut' => 'nullable|integer|in:0,1',
        ]);

        $validated['statut'] = $validated['statut'] ?? 1;
        Organisation::create($validated);

        return redirect()->route('systeme.organisations.index')
            ->with('success', 'Organisation cr\u00e9\u00e9e avec succ\u00e8s.');
    }

    public function show(string $id)
    {
        $organisation = Organisation::with(['type', 'chef', 'parent', 'enfants'])->findOrFail($id);

        return view('systeme.organisations.show', compact('organisation'));
    }

    public function edit(string $id)
    {
        $organisation = Organisation::findOrFail($id);
        $types = TypeOrganisation::orderBy('label')->get();
        $parents = Organisation::where('statut', 1)->where('id', '!=', $id)->orderBy('label')->get();
        $users = User::orderBy('name')->get();

        return view('systeme.organisations.edit', compact('organisation', 'types', 'parents', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $organisation = Organisation::findOrFail($id);

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'introduction' => 'nullable|string',
            'description' => 'nullable|string',
            'typesorganisation_id' => 'required|exists:typesorganisations,id',
            'chefs' => 'nullable|exists:users,id',
            'peres' => 'nullable|exists:organisations,id',
            'statut' => 'nullable|integer|in:0,1',
        ]);

        $organisation->update($validated);

        return redirect()->route('systeme.organisations.index')
            ->with('success', 'Organisation mise \u00e0 jour avec succ\u00e8s.');
    }

    public function destroy(string $id)
    {
        $organisation = Organisation::findOrFail($id);
        $organisation->delete();

        return redirect()->route('systeme.organisations.index')
            ->with('success', 'Organisation supprim\u00e9e avec succ\u00e8s.');
    }
}
