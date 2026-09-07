<?php

namespace App\Http\Controllers\Appro;

use App\Http\Controllers\Controller;
use App\Models\ContratFournisseur;
use App\Models\ContratFournisseurAvenant;
use App\Models\FrequencePaiement;
use App\Models\Intranet\ContactOrganisation;
use App\Models\TypeEngagement;
use Illuminate\Http\Request;

class ContratFournisseurController extends Controller
{
    public function index(Request $request)
    {
        $query = ContratFournisseur::with(['fournisseur', 'auteur'])
            ->when($request->q, fn($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('reference', 'ilike', "%{$s}%")->orWhere('objet', 'ilike', "%{$s}%");
            }))
            ->when($request->statut, fn($q, $st) => $q->where('statut', $st))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->fournisseur, fn($q, $f) => $q->where('fournisseur_id', $f))
            ->when($request->expiration, function ($q, $val) {
                if ($val === 'proche') $q->procheExpiration(60);
                elseif ($val === 'expires') $q->where('statut', ContratFournisseur::STATUT_EXPIRE);
            })
            ->latest('date_debut');

        $contrats = $query->paginate(15)->withQueryString();

        $stats = [
            'actifs'   => ContratFournisseur::actifs()->count(),
            'expiration_proche' => ContratFournisseur::procheExpiration(60)->count(),
            'expires'  => ContratFournisseur::where('statut', ContratFournisseur::STATUT_EXPIRE)->count(),
            'montant_actif' => (float) ContratFournisseur::actifs()->sum('montant_ttc'),
        ];

        return view('appro.contrats.index', [
            'contrats'      => $contrats,
            'stats'         => $stats,
            'statuts'       => ContratFournisseur::STATUTS,
            'types'         => TypeEngagement::actifs()->orderBy('ordre')->pluck('libelle', 'code')->toArray(),
            'fournisseurs'  => ContactOrganisation::where('type', 'fournisseur')->orderBy('raison_sociale')->get(['id', 'raison_sociale']),
        ]);
    }

    public function create()
    {
        return view('appro.contrats.create', $this->formData(new ContratFournisseur([
            'statut'     => ContratFournisseur::STATUT_BROUILLON,
            'devise'     => 'XAF',
            'date_debut' => now()->toDateString(),
            'type'       => 'prestation',
        ])));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;
        $contrat = ContratFournisseur::create($data);
        if ($request->hasFile('pieces_jointes')) {
            $contrat->attacherFichiers($request->file('pieces_jointes'), 'appro/contrats');
        }
        return redirect()->route('appro.contrats.show', $contrat)->with('success', 'Contrat créé.');
    }

    public function show(ContratFournisseur $contrat)
    {
        $contrat->load(['fournisseur', 'auteur', 'avenants.auteur', 'piecesJointes', 'parent', 'enfants']);
        return view('appro.contrats.show', compact('contrat'));
    }

    public function edit(ContratFournisseur $contrat)
    {
        return view('appro.contrats.edit', $this->formData($contrat));
    }

    public function update(Request $request, ContratFournisseur $contrat)
    {
        $contrat->update($this->validateData($request));
        if ($request->hasFile('pieces_jointes')) {
            $contrat->attacherFichiers($request->file('pieces_jointes'), 'appro/contrats');
        }
        return redirect()->route('appro.contrats.show', $contrat)->with('success', 'Contrat mis à jour.');
    }

    public function destroy(ContratFournisseur $contrat)
    {
        $contrat->delete();
        return redirect()->route('appro.contrats.index')->with('success', 'Contrat supprimé.');
    }

    // ═════════ Actions workflow ═════════

    public function activer(ContratFournisseur $contrat)
    {
        $contrat->activer();
        return back()->with('success', 'Contrat activé.');
    }

    public function resilier(Request $request, ContratFournisseur $contrat)
    {
        $data = $request->validate(['motif' => 'nullable|string|max:2000']);
        $contrat->resilier($data['motif'] ?? null);
        return back()->with('success', 'Contrat résilié.');
    }

    public function renouveler(Request $request, ContratFournisseur $contrat)
    {
        $data = $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'nullable|date|after:date_debut',
            'montant_ht' => 'nullable|numeric|min:0',
            'montant_ttc'=> 'nullable|numeric|min:0',
        ]);
        $nouveau = $contrat->renouveler($data);
        return redirect()->route('appro.contrats.show', $nouveau)->with('success', 'Nouveau contrat créé par renouvellement.');
    }

    public function addAvenant(Request $request, ContratFournisseur $contrat)
    {
        $data = $request->validate([
            'date_avenant'      => 'required|date',
            'objet'             => 'required|string|max:255',
            'impact'            => 'nullable|string|max:2000',
            'delta_montant_ht'  => 'nullable|numeric',
            'nouvelle_date_fin' => 'nullable|date',
        ]);
        $numero = ($contrat->avenants()->max('numero') ?? 0) + 1;
        $av = ContratFournisseurAvenant::create(array_merge($data, [
            'contrat_id' => $contrat->id,
            'numero'     => $numero,
            'created_by' => $request->user()->id,
        ]));
        // Applique la nouvelle date fin au contrat si fournie
        if (!empty($data['nouvelle_date_fin'])) {
            $contrat->update(['date_fin' => $data['nouvelle_date_fin']]);
        }
        // Applique le delta au montant HT si fourni
        if (isset($data['delta_montant_ht'])) {
            $contrat->update(['montant_ht' => (float) $contrat->montant_ht + (float) $data['delta_montant_ht']]);
        }
        return back()->with('success', 'Avenant n°'.$numero.' ajouté.');
    }

    // ═════════ Helpers ═════════

    private function formData(ContratFournisseur $contrat): array
    {
        return [
            'contrat'      => $contrat,
            'statuts'      => ContratFournisseur::STATUTS,
            'types'        => TypeEngagement::actifs()->orderBy('ordre')->pluck('libelle', 'code')->toArray(),
            'frequences'   => FrequencePaiement::actifs()->orderBy('ordre')->pluck('libelle', 'code')->toArray(),
            'fournisseurs' => ContactOrganisation::where('type', 'fournisseur')->orderBy('raison_sociale')->get(['id', 'raison_sociale']),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'fournisseur_id' => 'required|exists:intranet_contact_organisations,id',
            'objet'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:5000',
            'type'           => 'required|exists:types_engagement,code',
            'date_signature' => 'nullable|date',
            'date_debut'     => 'required|date',
            'date_fin'       => 'nullable|date|after_or_equal:date_debut',
            'montant_ht'     => 'nullable|numeric|min:0',
            'montant_ttc'    => 'nullable|numeric|min:0',
            'devise'         => 'required|string|max:3',
            'frequence_paiement'   => 'required|exists:frequences_paiement,code',
            'montant_par_paiement' => 'nullable|numeric|min:0',
            'jour_paiement'        => 'nullable|integer|min:1|max:31',
            'delai_paiement_jours' => 'nullable|integer|min:0|max:365',
            'statut'         => 'required|in:'.implode(',', array_keys(ContratFournisseur::STATUTS)),
            'renouvellement_auto'       => 'nullable|boolean',
            'preavis_resiliation_jours' => 'nullable|integer|min:0',
            'conditions'     => 'nullable|string|max:5000',
            'pieces_jointes.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp'],
        ]) + ['renouvellement_auto' => (bool) $request->input('renouvellement_auto')];
    }
}
