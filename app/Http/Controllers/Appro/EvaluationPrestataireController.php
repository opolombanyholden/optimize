<?php

namespace App\Http\Controllers\Appro;

use App\Http\Controllers\Controller;
use App\Models\CampagneEvaluation;
use App\Models\CommandeFournisseur;
use App\Models\CritereEvaluation;
use App\Models\EvaluationPrestataire;
use App\Models\Intranet\ContactOrganisation;
use App\Models\ThemeEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationPrestataireController extends Controller
{
    public function index(Request $request)
    {
        $evaluations = EvaluationPrestataire::query()
            ->with(['prestataire', 'evaluateur', 'campagne'])
            ->when($request->prestataire, fn($q, $p) => $q->where('prestataire_id', $p))
            ->when($request->source, fn($q, $s) => $q->where('source', $s))
            ->when($request->campagne, fn($q, $c) => $q->where('campagne_id', $c))
            ->orderByDesc('date_evaluation')
            ->paginate(20)
            ->withQueryString();

        return view('appro.evaluations.index', [
            'evaluations' => $evaluations,
            'prestataires' => ContactOrganisation::where('type', 'fournisseur')->orderBy('nom')->get(['id', 'nom', 'raison_sociale']),
            'campagnes' => CampagneEvaluation::orderByDesc('date_debut')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $themes = ThemeEvaluation::where('actif', true)->with(['criteres' => fn($q) => $q->where('actif', true)->orderBy('ordre')])->orderBy('ordre')->get();
        $criteresIndep = CritereEvaluation::whereNull('theme_id')->where('actif', true)->orderBy('ordre')->get();

        return view('appro.evaluations.create', [
            'themes' => $themes,
            'criteresIndep' => $criteresIndep,
            'prestataires' => ContactOrganisation::where('type', 'fournisseur')->orderBy('nom')->get(),
            'commande' => $request->query('commande_id') ? CommandeFournisseur::find($request->query('commande_id')) : null,
            'campagne' => $request->query('campagne_id') ? CampagneEvaluation::find($request->query('campagne_id')) : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prestataire_id' => 'required|exists:intranet_contact_organisations,id',
            'source' => 'required|in:livraison,campagne,ad_hoc',
            'commande_fournisseur_id' => 'nullable|exists:commande_fournisseurs,id',
            'campagne_id' => 'nullable|exists:campagnes_evaluation,id',
            'commentaire' => 'nullable|string|max:5000',
            'notes' => 'required|array|min:1',
            'notes.*.critere_id' => 'required|exists:criteres_evaluation,id',
            'notes.*.note' => 'required|numeric|min:0',
            'notes.*.commentaire' => 'nullable|string|max:1000',
        ]);

        $eval = DB::transaction(function () use ($data, $request) {
            $eval = EvaluationPrestataire::create([
                'prestataire_id' => $data['prestataire_id'],
                'evaluateur_id' => $request->user()->id,
                'source' => $data['source'],
                'commande_fournisseur_id' => $data['commande_fournisseur_id'] ?? null,
                'campagne_id' => $data['campagne_id'] ?? null,
                'date_evaluation' => now()->toDateString(),
                'commentaire' => $data['commentaire'] ?? null,
            ]);
            foreach ($data['notes'] as $n) {
                $eval->notes()->create([
                    'critere_id' => $n['critere_id'],
                    'note' => $n['note'],
                    'commentaire' => $n['commentaire'] ?? null,
                ]);
            }
            $eval->recalculerNoteGlobale();

            // Cocher la campagne si applicable
            if ($eval->campagne_id) {
                DB::table('campagne_prestataires')
                    ->where('campagne_id', $eval->campagne_id)
                    ->where('prestataire_id', $eval->prestataire_id)
                    ->update(['evaluation_faite' => true]);
            }
            return $eval;
        });

        return redirect()->route('appro.evaluations.show', $eval)->with('success', 'Évaluation enregistrée.');
    }

    public function show(EvaluationPrestataire $evaluation)
    {
        $evaluation->load(['prestataire', 'evaluateur', 'campagne', 'commandeFournisseur', 'notes.critere.theme']);
        return view('appro.evaluations.show', compact('evaluation'));
    }

    public function destroy(EvaluationPrestataire $evaluation)
    {
        $evaluation->delete();
        return redirect()->route('appro.evaluations.index')->with('success', 'Évaluation supprimée.');
    }
}
