<?php

namespace App\Http\Controllers\Appro;

use App\Http\Controllers\Controller;
use App\Models\Emplacement;
use App\Models\Inventaire;
use App\Models\Produit;
use Illuminate\Http\Request;

class InventaireController extends Controller
{
    public function index(Request $request)
    {
        $inventaires = Inventaire::with(['emplacement', 'responsable'])
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->orderByDesc('date_prevue')
            ->paginate(20)
            ->withQueryString();

        return view('appro.inventaires.index', [
            'inventaires' => $inventaires,
            'statuts' => Inventaire::STATUTS,
        ]);
    }

    public function create()
    {
        return view('appro.inventaires.create', [
            'inventaire' => new Inventaire(['date_prevue' => now()->toDateString()]),
            'emplacements' => Emplacement::orderBy('libelle')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $inv = Inventaire::create($data + [
            'numero' => Inventaire::genererNumero(),
            'responsable_id' => $request->user()->id,
            'statut' => Inventaire::STATUT_BROUILLON,
        ]);
        $created = $inv->genererLignes();

        return redirect()->route('appro.inventaires.show', $inv)
            ->with('success', "Inventaire créé avec {$created} article(s) à compter.");
    }

    public function show(Inventaire $inventaire)
    {
        $inventaire->load(['emplacement', 'responsable', 'clotureur', 'lignes.produit', 'lignes.emplacement']);
        return view('appro.inventaires.show', compact('inventaire'));
    }

    public function edit(Inventaire $inventaire)
    {
        if (!$inventaire->peutEtreModifie()) {
            return redirect()->route('appro.inventaires.show', $inventaire)->with('error', 'Non modifiable.');
        }
        return view('appro.inventaires.edit', [
            'inventaire' => $inventaire,
            'emplacements' => Emplacement::orderBy('libelle')->get(),
        ]);
    }

    public function update(Request $request, Inventaire $inventaire)
    {
        if (!$inventaire->peutEtreModifie()) return back()->with('error', 'Non modifiable.');
        $data = $this->validateData($request);
        $inventaire->update($data);
        // Périmètre a peut-être changé → régénérer les lignes
        $inventaire->genererLignes();
        return redirect()->route('appro.inventaires.show', $inventaire)->with('success', 'Inventaire mis à jour.');
    }

    public function destroy(Inventaire $inventaire)
    {
        if (!$inventaire->peutEtreModifie()) return back()->with('error', 'Non supprimable après démarrage.');
        $inventaire->delete();
        return redirect()->route('appro.inventaires.index')->with('success', 'Inventaire supprimé.');
    }

    /**
     * Vue de saisie : formulaire ligne par ligne avec quantite_reelle.
     */
    public function saisie(Inventaire $inventaire)
    {
        if ($inventaire->statut === Inventaire::STATUT_BROUILLON) {
            return redirect()->route('appro.inventaires.show', $inventaire)->with('error', 'Lancez l\'inventaire avant la saisie.');
        }
        $inventaire->load(['lignes.produit', 'lignes.emplacement']);
        return view('appro.inventaires.saisie', compact('inventaire'));
    }

    public function enregistrerSaisie(Request $request, Inventaire $inventaire)
    {
        if ($inventaire->statut !== Inventaire::STATUT_EN_COURS) return back()->with('error', 'Inventaire non en cours.');
        $data = $request->validate([
            'lignes' => 'required|array',
            'lignes.*.id' => 'required|exists:inventaire_lignes,id',
            'lignes.*.quantite_bon_etat' => 'nullable|numeric|min:0',
            'lignes.*.quantite_mauvais_etat' => 'nullable|numeric|min:0',
            'lignes.*.commentaire' => 'nullable|string|max:500',
        ]);
        foreach ($data['lignes'] as $lg) {
            $ligne = $inventaire->lignes()->find($lg['id']);
            if (!$ligne) continue;
            $bon    = $lg['quantite_bon_etat'] ?? null;
            $mauvais = $lg['quantite_mauvais_etat'] ?? null;
            $bon    = ($bon    !== null && $bon    !== '') ? (float) $bon : null;
            $mauvais = ($mauvais !== null && $mauvais !== '') ? (float) $mauvais : null;
            // Un compte est considéré fait dès qu'AU MOINS l'un des 2 champs est renseigné.
            $isCompte = $bon !== null || $mauvais !== null;
            // Seule la quantité en bon état est effectivement disponible pour livraison
            // → c'est elle qui pilote quantite_reelle et l'écart contre le théorique.
            $reelle = $isCompte ? (float) ($bon ?? 0) : null;
            $ligne->update([
                'quantite_bon_etat'     => $bon,
                'quantite_mauvais_etat' => $mauvais,
                'quantite_reelle'       => $reelle,
                'ecart'                 => $reelle !== null ? $reelle - (float) $ligne->quantite_theorique : null,
                'commentaire'           => $lg['commentaire'] ?? null,
                'compte_par'            => $isCompte ? $request->user()->id : null,
                'compte_at'             => $isCompte ? now() : null,
            ]);
        }
        return back()->with('success', 'Saisie enregistrée. Seul le bon état alimente le stock livrable.');
    }

    public function lancer(Inventaire $inventaire)
    {
        if (!$inventaire->peutEtreLance()) return back()->with('error', 'Inventaire non lancable.');
        $inventaire->lancer();
        return redirect()->route('appro.inventaires.saisie', $inventaire)->with('success', 'Inventaire lancé. Vous pouvez commencer la saisie.');
    }

    public function cloturer(Inventaire $inventaire)
    {
        if (!$inventaire->peutEtreCloture()) return back()->with('error', 'Inventaire non clôturable.');
        $stats = $inventaire->cloturer();
        return back()->with('success', "Inventaire clôturé : {$stats['ajustements']} ajustement(s) appliqué(s) ({$stats['positives']} positifs, {$stats['negatives']} négatifs).");
    }

    public function annuler(Inventaire $inventaire)
    {
        if ($inventaire->statut === Inventaire::STATUT_CLOTURE) return back()->with('error', 'Inventaire déjà clôturé.');
        $inventaire->annuler();
        return back()->with('success', 'Inventaire annulé.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'libelle' => 'required|string|max:255',
            'date_prevue' => 'required|date',
            'emplacement_id' => 'nullable|exists:emplacements,id',
            'commentaire' => 'nullable|string|max:2000',
        ]);
    }
}
