<?php

namespace App\Http\Controllers\Appro;

use App\Http\Controllers\Controller;
use App\Models\CampagneEvaluation;
use App\Models\Intranet\ContactOrganisation;
use Illuminate\Http\Request;

class CampagneEvaluationController extends Controller
{
    public function index()
    {
        $campagnes = CampagneEvaluation::with('prestataires', 'evaluations')->orderByDesc('date_debut')->paginate(15);
        return view('appro.campagnes.index', [
            'campagnes' => $campagnes,
            'statuts' => CampagneEvaluation::STATUTS,
        ]);
    }

    public function create()
    {
        return view('appro.campagnes.create', [
            'campagne' => new CampagneEvaluation(['date_debut' => now()->toDateString()]),
            'prestataires' => ContactOrganisation::where('type', 'fournisseur')->orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $campagne = CampagneEvaluation::create([
            'libelle' => $data['libelle'],
            'description' => $data['description'] ?? null,
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'] ?? null,
            'statut' => CampagneEvaluation::STATUT_BROUILLON,
            'created_by' => $request->user()->id,
        ]);
        if (!empty($data['prestataires'])) {
            $campagne->prestataires()->sync(collect($data['prestataires'])->mapWithKeys(fn($id) => [$id => ['evaluation_faite' => false]]));
        }
        return redirect()->route('appro.campagnes.show', $campagne)->with('success', 'Campagne créée.');
    }

    public function show(CampagneEvaluation $campagne)
    {
        $campagne->load(['prestataires', 'evaluations.prestataire', 'auteur']);
        return view('appro.campagnes.show', compact('campagne'));
    }

    public function edit(CampagneEvaluation $campagne)
    {
        return view('appro.campagnes.edit', [
            'campagne' => $campagne,
            'prestataires' => ContactOrganisation::where('type', 'fournisseur')->orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, CampagneEvaluation $campagne)
    {
        $data = $this->validateData($request);
        $campagne->update([
            'libelle' => $data['libelle'],
            'description' => $data['description'] ?? null,
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'] ?? null,
        ]);
        if (isset($data['prestataires'])) {
            $existing = $campagne->prestataires()->pluck('prestataire_id')->all();
            $sync = collect($data['prestataires'])->mapWithKeys(function ($id) use ($existing) {
                return [$id => ['evaluation_faite' => in_array($id, $existing, true) ? false : false]];
            });
            $campagne->prestataires()->sync($sync);
        }
        return redirect()->route('appro.campagnes.show', $campagne)->with('success', 'Campagne mise à jour.');
    }

    public function destroy(CampagneEvaluation $campagne)
    {
        $campagne->delete();
        return redirect()->route('appro.campagnes.index')->with('success', 'Campagne supprimée.');
    }

    public function lancer(CampagneEvaluation $campagne)
    {
        if ($campagne->statut !== CampagneEvaluation::STATUT_BROUILLON) return back()->with('error', 'Campagne déjà lancée.');
        $campagne->update(['statut' => CampagneEvaluation::STATUT_EN_COURS]);
        return back()->with('success', 'Campagne lancée.');
    }

    public function cloturer(CampagneEvaluation $campagne)
    {
        if ($campagne->statut !== CampagneEvaluation::STATUT_EN_COURS) return back()->with('error', 'Campagne non en cours.');
        $campagne->update(['statut' => CampagneEvaluation::STATUT_CLOTUREE]);
        return back()->with('success', 'Campagne clôturée.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'prestataires' => 'nullable|array',
            'prestataires.*' => 'exists:intranet_contact_organisations,id',
        ]);
    }
}
