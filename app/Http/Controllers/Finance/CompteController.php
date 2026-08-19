<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use Illuminate\Http\Request;

class CompteController extends Controller
{
    public function index(Request $request)
    {
        $comptes = Compte::query()
            ->where('effacer', 0)
            ->when($request->search, fn($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('nom', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")->orWhere('rib', 'like', "%{$s}%");
            }))
            ->when($request->type, fn($q, $t) => $q->where('type', (int) $t))
            ->when($request->boolean('inactifs'), fn($q) => $q->where('actif', false), fn($q) => $q->where('actif', true))
            ->orderBy('type')->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_banque'       => (float) Compte::actif()->banque()->sum('solde'),
            'total_caisse'       => (float) Compte::actif()->caisse()->sum('solde'),
            'total_electronique' => (float) Compte::actif()->electronique()->sum('solde'),
            'nb_actifs'          => Compte::actif()->count(),
        ];

        return view('finance.comptes.index', compact('comptes', 'stats'));
    }

    public function create()
    {
        return view('finance.comptes.create', ['compte' => new Compte(['type' => Compte::TYPE_BANQUE, 'devise' => 'XAF', 'actif' => true])]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['actif'] = $request->boolean('actif', true);
        // Solde initial = solde courant à la création (référence historique)
        $validated['solde'] = $validated['solde_initial'] = (float) ($validated['solde_initial'] ?? $validated['solde'] ?? 0);

        Compte::create($validated);
        return redirect()->route('finance.comptes.index')->with('success', 'Compte créé.');
    }

    public function show(string $id)
    {
        $compte = Compte::with(['transactionComptes', 'grandLivres'])->findOrFail($id);

        return view('finance.comptes.show', compact('compte'));
    }

    public function edit(string $id)
    {
        $compte = Compte::findOrFail($id);

        return view('finance.comptes.edit', compact('compte'));
    }

    public function update(Request $request, string $id)
    {
        $compte = Compte::findOrFail($id);
        $validated = $this->validated($request);
        $validated['actif'] = $request->boolean('actif', true);
        $compte->update($validated);
        return redirect()->route('finance.comptes.index')->with('success', 'Compte mis à jour.');
    }

    public function destroy(string $id)
    {
        $compte = Compte::findOrFail($id);
        // Refus si compte lié à des ordres exécutés
        if ($compte->ordres()->where('statut', \App\Models\Finance\Ordre::STATUT_EXECUTE)->exists()) {
            return back()->with('error', "Impossible de supprimer : ce compte a été utilisé dans des ordres exécutés. Désactivez-le à la place.");
        }
        $compte->delete();
        return redirect()->route('finance.comptes.index')->with('success', 'Compte supprimé.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'code'                 => 'nullable|string|max:60',
            'nom'                  => 'required|string|max:255',
            'type'                 => 'required|integer|in:' . implode(',', array_keys(Compte::TYPES)),
            'entite_id'            => 'nullable|integer',
            'solde'                => 'nullable|numeric',
            'solde_initial'        => 'nullable|numeric',
            'devise'               => 'nullable|string|max:10',
            'rib'                  => 'nullable|string|max:60',
            'responsable'          => 'nullable|string|max:255',
            'gestionnaire'         => 'nullable|string|max:255',
            'contact_gestionnaire' => 'nullable|string|max:255',
            'domiciliation'        => 'nullable|string|max:255',
        ]);
    }
}
