<?php

namespace App\Http\Controllers\MG;

use App\Http\Controllers\Controller;
use App\Models\Dysfonctionnement;
use App\Models\Immobilisation;
use App\Models\Intervention;
use App\Models\User;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    public function index(Request $request)
    {
        $interventions = Intervention::query()
            ->with(['dysfonctionnement', 'immobilisation', 'technicien'])
            ->when($request->q, fn($q, $s) => $q->where('label', 'ilike', "%{$s}%"))
            ->when($request->type_intervention, fn($q, $t) => $q->where('type_intervention', $t))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->when($request->technicien, fn($q, $t) => $q->where('technicien_id', $t))
            ->orderByDesc('date_planifiee')
            ->paginate(15)
            ->withQueryString();

        return view('mg.interventions.index', [
            'interventions' => $interventions,
            'statuts' => Intervention::STATUTS,
            'types' => Intervention::TYPES,
            'techniciens' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request)
    {
        $intervention = new Intervention([
            'dysfonctionnement_id' => $request->query('dysfonctionnement_id'),
            'immobilisation_id' => $request->query('immobilisation_id'),
            'type_intervention' => 'corrective',
        ]);
        return view('mg.interventions.create', $this->formData($intervention));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['statut'] = Intervention::STATUT_PLANIFIEE;
        $intervention = Intervention::create($data);
        if ($request->hasFile('pieces_jointes')) $intervention->attacherFichiers($request->file('pieces_jointes'), 'mg/interventions');
        return redirect()->route('mg.interventions.show', $intervention)->with('success', 'Intervention planifiée.');
    }

    public function show(Intervention $intervention)
    {
        $intervention->load(['dysfonctionnement.immobilisation', 'immobilisation', 'technicien', 'piecesJointes']);
        return view('mg.interventions.show', compact('intervention'));
    }

    public function edit(Intervention $intervention)
    {
        return view('mg.interventions.edit', $this->formData($intervention));
    }

    public function update(Request $request, Intervention $intervention)
    {
        $intervention->update($this->validateData($request));
        if ($request->hasFile('pieces_jointes')) $intervention->attacherFichiers($request->file('pieces_jointes'), 'mg/interventions');
        return redirect()->route('mg.interventions.show', $intervention)->with('success', 'Intervention mise à jour.');
    }

    public function destroy(Intervention $intervention)
    {
        $intervention->delete();
        return redirect()->route('mg.interventions.index')->with('success', 'Intervention supprimée.');
    }

    // ═════════ Workflow ═════════

    public function demarrer(Intervention $intervention)
    {
        if (!$intervention->peutEtreDemarree()) return back()->with('error', 'Statut incompatible.');
        $intervention->demarrer();
        return back()->with('success', 'Intervention démarrée.');
    }

    public function terminer(Request $request, Intervention $intervention)
    {
        if (!$intervention->peutEtreTerminee()) return back()->with('error', 'Statut incompatible.');
        $data = $request->validate([
            'rapport' => 'nullable|string|max:5000',
            'cout' => 'nullable|numeric|min:0',
            'nature_probleme' => 'nullable|string|max:5000',
            'pistes_solution' => 'nullable|string|max:5000',
            'solution_appliquee' => 'nullable|string|max:5000',
            'resultat' => 'nullable|string|max:5000',
            'statut_resolution' => 'required|in:resolu,partiel,non_resolu',
        ]);
        $intervention->terminer($data);
        return back()->with('success', 'Intervention terminée.');
    }

    /**
     * Priorisation d'un ticket dysfonctionnement (accessoire — proxy vers Dysfonctionnement).
     */
    public function prioriserTicket(Request $request, Dysfonctionnement $dysfonctionnement)
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

    public function annuler(Request $request, Intervention $intervention)
    {
        if (!$intervention->peutEtreAnnulee()) return back()->with('error', 'Statut incompatible.');
        $data = $request->validate(['motif_annulation' => 'required|string|min:5|max:500']);
        $intervention->annuler($data['motif_annulation']);
        return back()->with('success', 'Intervention annulée.');
    }

    // ═════════ Helpers ═════════

    private function formData(Intervention $intervention): array
    {
        return [
            'intervention' => $intervention,
            'dysfonctionnements' => Dysfonctionnement::whereIn('statut', [
                Dysfonctionnement::STATUT_SIGNALE, Dysfonctionnement::STATUT_PRIS_EN_CHARGE,
            ])->orderByDesc('date_signalement')->get(),
            'immobilisations' => Immobilisation::orderBy('designation')->get(),
            'techniciens' => User::orderBy('name')->get(['id', 'name']),
            'types' => Intervention::TYPES,
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'label' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'dysfonctionnement_id' => 'nullable|exists:dysfonctionnements,id',
            'thematique_id' => 'nullable|exists:mg_thematiques,id',
            'immobilisation_id' => 'nullable|exists:immobilisations,id',
            'technicien_id' => 'nullable|exists:users,id',
            'type_intervention' => 'nullable|in:preventive,corrective,curative,ameliorative',
            'date_planifiee' => 'nullable|date',
            'cout' => 'nullable|numeric|min:0',
            'pieces_jointes.*' => 'nullable|file|max:20480',
        ]);
    }
}
