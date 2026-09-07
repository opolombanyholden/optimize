<?php

namespace App\Http\Controllers\MG;

use App\Http\Controllers\Controller;
use App\Models\Dysfonctionnement;
use App\Models\Immobilisation;
use App\Models\Intervention;
use App\Models\TypeDysfonctionnement;
use App\Models\Organisation;
use App\Models\User;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\Service;
use Illuminate\Http\Request;

class DysfonctionnementController extends Controller
{
    public function index(Request $request)
    {
        $dysfonctionnements = Dysfonctionnement::query()
            ->with(['type', 'declarant', 'immobilisation'])
            ->when($request->q, fn($q, $s) => $q->where('label', 'ilike', "%{$s}%"))
            ->when($request->priorite, fn($q, $p) => $q->where('priorite', $p))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->when($request->immobilisation, fn($q, $i) => $q->where('immobilisation_id', $i))
            ->orderByDesc('date_signalement')
            ->paginate(15)
            ->withQueryString();

        $statuts = Dysfonctionnement::STATUTS;
        $priorites = Dysfonctionnement::PRIORITES;
        $immobilisations = Immobilisation::orderBy('designation')->get(['id', 'designation', 'code']);

        return view('mg.dysfonctionnements.index', compact('dysfonctionnements', 'statuts', 'priorites', 'immobilisations'));
    }

    public function create()
    {
        return view('mg.dysfonctionnements.create', array_merge(
            ['dysfonctionnement' => new Dysfonctionnement(['priorite' => 'normale'])],
            $this->assignationOptions(),
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['declarant_id'] = $request->user()->id;
        $validated['date_signalement'] = now();
        $validated['statut'] = Dysfonctionnement::STATUT_SIGNALE;

        $d = Dysfonctionnement::create($validated);
        if ($request->hasFile('pieces_jointes')) $d->attacherFichiers($request->file('pieces_jointes'), 'mg/dysfonctionnements');
        $d->assignerA($this->parseCibles($request->input('assignees', [])), null, $request->user()->id);

        return redirect()->route('mg.dysfonctionnements.show', $d)->with('success', 'Dysfonctionnement signalé.');
    }

    public function show(Dysfonctionnement $dysfonctionnement)
    {
        $dysfonctionnement->load(['type', 'declarant', 'immobilisation', 'interventions.technicien', 'piecesJointes', 'priseurEnCharge', 'resoluteur']);
        return view('mg.dysfonctionnements.show', compact('dysfonctionnement'));
    }

    public function edit(Dysfonctionnement $dysfonctionnement)
    {
        $dysfonctionnement->load('assignationsActives');
        return view('mg.dysfonctionnements.edit', array_merge(
            ['dysfonctionnement' => $dysfonctionnement],
            $this->assignationOptions(),
        ));
    }

    public function update(Request $request, Dysfonctionnement $dysfonctionnement)
    {
        $dysfonctionnement->update($this->validateData($request));
        if ($request->hasFile('pieces_jointes')) $dysfonctionnement->attacherFichiers($request->file('pieces_jointes'), 'mg/dysfonctionnements');
        $dysfonctionnement->synchroniserAssignations($this->parseCibles($request->input('assignees', [])), $request->user()->id);
        return redirect()->route('mg.dysfonctionnements.show', $dysfonctionnement)->with('success', 'Dysfonctionnement mis à jour.');
    }

    public function destroy(Dysfonctionnement $dysfonctionnement)
    {
        $dysfonctionnement->delete();
        return redirect()->route('mg.dysfonctionnements.index')->with('success', 'Dysfonctionnement supprimé.');
    }

    // ═════════ Workflow ═════════

    public function prendreEnCharge(Dysfonctionnement $dysfonctionnement)
    {
        if (!$dysfonctionnement->peutEtrePrisEnCharge()) {
            return back()->with('error', 'Statut incompatible avec cette action.');
        }
        $dysfonctionnement->prendreEnCharge();
        return back()->with('success', 'Dysfonctionnement pris en charge.');
    }

    public function resoudre(Request $request, Dysfonctionnement $dysfonctionnement)
    {
        if (!$dysfonctionnement->peutEtreResolu()) {
            return back()->with('error', 'Statut incompatible avec cette action.');
        }
        $data = $request->validate(['commentaire_resolution' => 'nullable|string|max:2000']);
        $dysfonctionnement->resoudre($data['commentaire_resolution'] ?? null);
        return back()->with('success', 'Dysfonctionnement résolu.');
    }

    public function prioriser(Request $request, Dysfonctionnement $dysfonctionnement)
    {
        $data = $request->validate([
            'priorite_admin' => 'required|in:basse,normale,haute,critique',
            'moment_intervention' => 'nullable|date',
        ]);
        $dysfonctionnement->prioriser(
            $data['priorite_admin'],
            !empty($data['moment_intervention']) ? new \DateTimeImmutable($data['moment_intervention']) : null,
        );
        return back()->with('success', 'Ticket priorisé.');
    }

    public function fermer(Dysfonctionnement $dysfonctionnement)
    {
        if (!$dysfonctionnement->peutEtreFerme()) {
            return back()->with('error', 'Le dysfonctionnement doit être résolu avant fermeture.');
        }
        $dysfonctionnement->fermer();
        return back()->with('success', 'Dysfonctionnement fermé.');
    }

    /**
     * L'émetteur (déclarant) valide la résolution proposée par le technicien.
     * Seul le déclarant (ou un super-admin) peut valider.
     */
    public function validerResolution(Request $request, Dysfonctionnement $dysfonctionnement)
    {
        if (!$dysfonctionnement->attendValidationResolution()) {
            return back()->with('error', 'Aucune résolution en attente de validation.');
        }
        $user = $request->user();
        if (!$dysfonctionnement->emetteurEst($user->id) && !$user->hasRole('super-admin')) {
            return back()->with('error', 'Seul l\'émetteur du ticket peut valider la résolution.');
        }
        $data = $request->validate(['commentaire_validation_resolution' => 'nullable|string|max:2000']);
        $dysfonctionnement->validerResolutionParEmetteur($data['commentaire_validation_resolution'] ?? null, $user->id);
        return back()->with('success', 'Résolution validée — ticket marqué résolu.');
    }

    /**
     * L'émetteur rejette la résolution proposée : le ticket reste ouvert.
     */
    public function rejeterResolution(Request $request, Dysfonctionnement $dysfonctionnement)
    {
        if (!$dysfonctionnement->attendValidationResolution()) {
            return back()->with('error', 'Aucune résolution en attente de validation.');
        }
        $user = $request->user();
        if (!$dysfonctionnement->emetteurEst($user->id) && !$user->hasRole('super-admin')) {
            return back()->with('error', 'Seul l\'émetteur du ticket peut rejeter la résolution.');
        }
        $data = $request->validate(['motif_rejet' => 'required|string|max:2000']);
        $dysfonctionnement->rejeterResolutionParEmetteur($data['motif_rejet'], $user->id);
        return back()->with('success', 'Résolution rejetée — le ticket reste ouvert.');
    }

    /**
     * Planifie une intervention à partir du dysfonctionnement (rattachement direct).
     */
    public function planifierIntervention(Request $request, Dysfonctionnement $dysfonctionnement)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'type_intervention' => 'nullable|in:preventive,corrective,curative,ameliorative',
            'technicien_id' => 'nullable|exists:users,id',
            'date_planifiee' => 'nullable|date',
        ]);
        $intervention = Intervention::create(array_merge($data, [
            'dysfonctionnement_id' => $dysfonctionnement->id,
            'immobilisation_id' => $dysfonctionnement->immobilisation_id,
            'statut' => Intervention::STATUT_PLANIFIEE,
        ]));
        return redirect()->route('mg.interventions.show', $intervention)->with('success', 'Intervention planifiée.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'label' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'type_id' => 'nullable|exists:typesdysfonctionnements,id',
            'thematique_id' => 'nullable|exists:mg_thematiques,id',
            'immobilisation_id' => 'nullable|exists:immobilisations,id',
            'localisation' => 'nullable|string|max:255',
            'priorite' => 'nullable|in:basse,normale,haute,critique',
            'pieces_jointes.*' => 'nullable|file|max:20480',
            'assignees'   => 'nullable|array',
            'assignees.*' => ['string', 'regex:/^(user|service|groupe|entite):\d+$/'],
        ]);
    }

    /**
     * Options communes aux formulaires de création/édition pour le sélecteur d'assignés.
     */
    private function assignationOptions(): array
    {
        return [
            'types'           => TypeDysfonctionnement::all(),
            'immobilisations' => Immobilisation::orderBy('designation')->get(),
            'priorites'       => Dysfonctionnement::PRIORITES,
            'usersDispo'      => User::orderBy('name')->get(['id', 'name', 'prenoms']),
            'servicesDispo'   => Service::orderBy('nom')->get(['id', 'nom']),
            'groupesDispo'    => Groupe::orderBy('nom')->get(['id', 'nom']),
            'entitesDispo'    => Organisation::orderBy('label')->get(['id', 'label']),
        ];
    }

    /**
     * Transforme les valeurs du <select multiple> ("user:12", "service:3", ...) en tableau exploitable
     * par Dysfonctionnement::assignerA().
     */
    private function parseCibles(array $raw): array
    {
        $out = [];
        foreach ($raw as $item) {
            if (!is_string($item) || !preg_match('/^(user|service|groupe|entite):(\d+)$/', $item, $m)) continue;
            $out[] = ['type' => $m[1], 'id' => (int) $m[2]];
        }
        return $out;
    }
}
