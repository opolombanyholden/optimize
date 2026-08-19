<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\DemandeModification;
use App\Models\Intranet\Jalon;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetPhase;
use App\Models\Intranet\Tache;
use Illuminate\Http\Request;

class DemandeModificationController extends Controller
{
    private function resolveEntity(string $type, int $id)
    {
        return match ($type) {
            'projet' => Projet::findOrFail($id),
            'phase'  => ProjetPhase::findOrFail($id),
            'jalon'  => Jalon::findOrFail($id),
            'tache'  => Tache::findOrFail($id),
            default  => abort(404),
        };
    }

    /**
     * Liste des demandes en attente (pour super-admin).
     */
    public function index()
    {
        $demandes = DemandeModification::with(['demandeur', 'decideur'])
            ->where('statut', 'en_attente')
            ->orderByDesc('created_at')
            ->get();

        return view('projet.demandes-modification.index', compact('demandes'));
    }

    /**
     * Demander une modification.
     */
    public function store(Request $request, string $type, int $id)
    {
        $request->validate([
            'motif'           => 'required|string|min:10',
            'champs_modifies' => 'nullable|array',
        ]);

        $entity = $this->resolveEntity($type, $id);

        if ($entity->aDemandeEnAttente()) {
            return back()->withErrors(['motif' => 'Une demande est déjà en attente pour cet élément.']);
        }

        $entity->demanderModification(
            $request->input('motif'),
            $request->input('champs_modifies', [])
        );

        return back()->with('success', 'Demande de modification soumise.');
    }

    /**
     * Demander une suppression (mise en corbeille).
     */
    public function suppression(Request $request, string $type, int $id)
    {
        $request->validate([
            'motif' => 'required|string|min:10',
        ]);

        $entity = $this->resolveEntity($type, $id);

        if ($entity->aDemandeEnAttente()) {
            return back()->withErrors(['motif' => 'Une demande est déjà en attente pour cet élément.']);
        }

        $entity->demanderSuppression($request->input('motif'));

        return back()->with('success', 'Demande de suppression soumise.');
    }

    /**
     * Approuver une demande (super-admin).
     */
    public function approuver(Request $request, DemandeModification $demande)
    {
        $demande->update([
            'statut'               => 'approuve',
            'decideur_id'          => auth()->id(),
            'decide_le'            => now(),
            'commentaire_decision' => $request->input('commentaire'),
        ]);

        // Si suppression approuvée → soft delete de l'entité
        if ($demande->type_demande === 'suppression') {
            $entity = $demande->modifiable;
            if ($entity && method_exists($entity, 'delete')) {
                $entity->delete();
            }
        }

        return back()->with('success', 'Demande approuvée.');
    }

    /**
     * Rejeter une demande (super-admin).
     */
    public function rejeter(Request $request, DemandeModification $demande)
    {
        $request->validate([
            'commentaire' => 'required|string|min:5',
        ]);

        $demande->update([
            'statut'               => 'rejete',
            'decideur_id'          => auth()->id(),
            'decide_le'            => now(),
            'commentaire_decision' => $request->input('commentaire'),
        ]);

        return back()->with('success', 'Demande rejetée.');
    }
}
