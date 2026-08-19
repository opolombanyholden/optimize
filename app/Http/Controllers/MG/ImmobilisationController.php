<?php

namespace App\Http\Controllers\MG;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Entite;
use App\Models\Immobilisation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ImmobilisationController extends Controller
{
    public function index(Request $request)
    {
        $immobilisations = Immobilisation::query()
            ->with(['employe', 'entite'])
            ->when($request->q, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('designation', 'ilike', "%{$s}%")
                        ->orWhere('code', 'ilike', "%{$s}%")
                        ->orWhere('localisation', 'ilike', "%{$s}%");
                });
            })
            ->when($request->categorie, fn($q, $c) => $q->where('categorie', $c))
            ->when($request->etat, fn($q, $e) => $q->where('etat', $e))
            ->when($request->entite, fn($q, $e) => $q->where('entite_id', $e))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('mg.immobilisations.index', [
            'immobilisations' => $immobilisations,
            'categories' => Immobilisation::CATEGORIES,
            'etats' => Immobilisation::ETATS,
            'entites' => Entite::orderBy('libelle')->get(['id', 'libelle']),
        ]);
    }

    public function create()
    {
        return view('mg.immobilisations.create', $this->formData(new Immobilisation([
            'methode_amortissement' => Immobilisation::METHODE_LINEAIRE,
            'etat' => 'neuf',
            'statut' => 1,
        ])));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if (!isset($data['valeur_nette_comptable'])) {
            $data['valeur_nette_comptable'] = $data['valeur_acquisition'] ?? 0;
        }
        $immo = Immobilisation::create($data);
        if ($request->hasFile('pieces_jointes')) $immo->attacherFichiers($request->file('pieces_jointes'), 'mg/immobilisations');
        return redirect()->route('mg.immobilisations.show', $immo)->with('success', 'Immobilisation créée.');
    }

    public function show(Immobilisation $immobilisation)
    {
        $immobilisation->load(['employe', 'entite', 'dysfonctionnements.type', 'interventions', 'dotations', 'piecesJointes']);
        return view('mg.immobilisations.show', compact('immobilisation'));
    }

    public function edit(Immobilisation $immobilisation)
    {
        return view('mg.immobilisations.edit', $this->formData($immobilisation));
    }

    public function update(Request $request, Immobilisation $immobilisation)
    {
        $immobilisation->update($this->validateData($request, $immobilisation->id));
        if ($request->hasFile('pieces_jointes')) $immobilisation->attacherFichiers($request->file('pieces_jointes'), 'mg/immobilisations');
        return redirect()->route('mg.immobilisations.show', $immobilisation)->with('success', 'Immobilisation mise à jour.');
    }

    public function destroy(Immobilisation $immobilisation)
    {
        $immobilisation->delete();
        return redirect()->route('mg.immobilisations.index')->with('success', 'Immobilisation supprimée.');
    }

    // ═════════ Amortissements ═════════

    /**
     * Enregistre la dotation mensuelle pour la période demandée (défaut = mois courant).
     */
    public function dotation(Request $request, Immobilisation $immobilisation)
    {
        $data = $request->validate([
            'periode' => 'nullable|date_format:Y-m',
        ]);
        $periode = isset($data['periode']) ? Carbon::createFromFormat('Y-m', $data['periode'])->startOfMonth() : now()->startOfMonth();
        $dotation = $immobilisation->enregistrerDotation($periode);
        if ($dotation === null) {
            return back()->with('error', 'Aucune dotation possible (déjà comptabilisée pour cette période, ou immobilisation totalement amortie).');
        }
        return back()->with('success', "Dotation de {$dotation->montant} enregistrée pour {$periode->format('m/Y')}.");
    }

    /**
     * Sort une immobilisation (cession, mise au rebut, etc.).
     */
    public function sortir(Request $request, Immobilisation $immobilisation)
    {
        $data = $request->validate([
            'date_sortie' => 'required|date',
            'motif_sortie' => 'required|string|min:3|max:255',
        ]);
        $immobilisation->update([
            'date_sortie' => $data['date_sortie'],
            'motif_sortie' => $data['motif_sortie'],
            'etat' => 'sortie',
            'statut' => 0,
        ]);
        return back()->with('success', 'Immobilisation sortie.');
    }

    private function formData(Immobilisation $immobilisation): array
    {
        return [
            'immobilisation' => $immobilisation,
            'employees' => Employee::orderBy('noms')->get(),
            'entites' => Entite::orderBy('libelle')->get(),
            'categories' => Immobilisation::CATEGORIES,
            'etats' => Immobilisation::ETATS,
            'methodes' => Immobilisation::METHODES,
        ];
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $uniqueRule = 'unique:immobilisations,code' . ($id ? ",{$id}" : '');
        return $request->validate([
            'code' => "nullable|string|max:255|{$uniqueRule}",
            'designation' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'categorie' => 'nullable|string|max:100',
            'localisation' => 'nullable|string|max:255',
            'date_acquisition' => 'nullable|date',
            'date_mise_en_service' => 'nullable|date',
            'valeur_acquisition' => 'nullable|numeric|min:0',
            'valeur_nette_comptable' => 'nullable|numeric|min:0',
            'valeur_residuelle' => 'nullable|numeric|min:0',
            'duree_amortissement' => 'nullable|integer|min:1|max:1200',
            'methode_amortissement' => 'nullable|in:lineaire,degressif',
            'etat' => 'nullable|string|max:50',
            'affecte_a' => 'nullable|exists:employees,id',
            'entite_id' => 'nullable|exists:entites,id',
            'statut' => 'nullable|integer|in:0,1',
            'pieces_jointes.*' => 'nullable|file|max:20480',
        ]);
    }
}
