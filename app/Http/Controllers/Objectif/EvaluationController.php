<?php

namespace App\Http\Controllers\Objectif;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Evaluation;
use App\Models\Intranet\Objectif;
use App\Models\User;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $query = Evaluation::with(['utilisateur', 'evaluateur', 'objectif']);

        if ($q = $request->input('q')) {
            $query->where(fn($w) => $w->where('titre', 'ilike', "%$q%")
                ->orWhere('commentaire', 'ilike', "%$q%"));
        }
        if ($s = $request->input('statut'))         $query->where('statut', $s);
        if ($u = $request->input('user_id'))        $query->where('user_id', $u);
        if ($e = $request->input('evaluateur_id'))  $query->where('evaluateur_id', $e);
        if ($obj = $request->input('objectif_id'))  $query->where('objectif_id', $obj);
        if ($du = $request->input('date_du'))       $query->whereDate('date_evaluation', '>=', $du);
        if ($au = $request->input('date_au'))       $query->whereDate('date_evaluation', '<=', $au);
        if ($scoreMin = $request->input('score_min'))  $query->where('score', '>=', (int) $scoreMin);
        if ($scoreMax = $request->input('score_max'))  $query->where('score', '<=', (int) $scoreMax);

        $evaluations = $query->orderByDesc('date_evaluation')->get();

        $users      = User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);
        $objectifs  = Objectif::orderBy('titre')->get(['id', 'titre']);

        // Stats sur l'ensemble (pas seulement la sélection filtrée)
        $allEvals = Evaluation::all(['statut', 'score']);
        $stats = [
            'total'       => $allEvals->count(),
            'brouillon'   => $allEvals->where('statut', 'brouillon')->count(),
            'finalises'   => $allEvals->where('statut', 'finalise')->count(),
            'valides'     => $allEvals->where('statut', 'valide')->count(),
            'score_moyen' => round($allEvals->where('score', '>', 0)->avg('score') ?? 0),
        ];

        return view('objectif.evaluations.index', compact('evaluations', 'users', 'objectifs', 'stats'));
    }

    public function show(Evaluation $evaluation)
    {
        $evaluation->load(['utilisateur', 'evaluateur', 'objectif']);
        return view('objectif.evaluations.show', compact('evaluation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'             => 'required|string|max:255',
            'user_id'           => 'required|exists:users,id',
            'evaluateur_id'     => 'required|exists:users,id|different:user_id',
            'objectif_id'       => 'nullable|exists:intranet_objectifs,id',
            'score'             => 'nullable|integer|min:0|max:100',
            'commentaire'       => 'nullable|string',
            'points_forts'      => 'nullable|string',
            'axes_amelioration' => 'nullable|string',
            'date_evaluation'   => 'required|date',
            'statut'            => 'required|in:brouillon,finalise,valide',
        ]);

        Evaluation::create($validated);

        return back()->with('success', 'Évaluation créée.');
    }

    public function update(Request $request, Evaluation $evaluation)
    {
        $validated = $request->validate([
            'titre'             => 'required|string|max:255',
            'user_id'           => 'required|exists:users,id',
            'evaluateur_id'     => 'required|exists:users,id|different:user_id',
            'objectif_id'       => 'nullable|exists:intranet_objectifs,id',
            'score'             => 'nullable|integer|min:0|max:100',
            'commentaire'       => 'nullable|string',
            'points_forts'      => 'nullable|string',
            'axes_amelioration' => 'nullable|string',
            'date_evaluation'   => 'required|date',
            'statut'            => 'required|in:brouillon,finalise,valide',
        ]);

        $evaluation->update($validated);

        return back()->with('success', 'Évaluation mise à jour.');
    }

    public function destroy(Evaluation $evaluation)
    {
        $evaluation->delete();
        return back()->with('success', 'Évaluation supprimée.');
    }
}
