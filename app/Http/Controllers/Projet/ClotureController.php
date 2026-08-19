<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Jalon;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetPhase;
use App\Models\Intranet\Statut;
use App\Models\Intranet\Tache;
use Illuminate\Http\Request;

class ClotureController extends Controller
{
    /**
     * Résoudre l'entité à partir du type et de l'ID.
     */
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
     * Sauvegarder les valideurs d'une entité.
     */
    public function sauverValideurs(Request $request, string $type, int $id)
    {
        $request->validate([
            'valideurs_users'    => 'nullable|array',
            'valideurs_users.*'  => 'exists:users,id',
            'valideurs_groupes'  => 'nullable|array',
            'valideurs_groupes.*'=> 'exists:intranet_groupes,id',
        ]);

        $entity = $this->resolveEntity($type, $id);
        $entity->syncValideurs(
            $request->input('valideurs_users', []),
            $request->input('valideurs_groupes', [])
        );

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Valideurs mis à jour.');
    }

    /**
     * Soumettre pour validation de clôture.
     */
    public function soumettre(Request $request, string $type, int $id)
    {
        $request->validate([
            'justification'    => 'required|string|min:10',
            'pieces_jointes'   => 'nullable|array',
            'pieces_jointes.*' => 'file|max:51200',
        ], [
            'justification.required' => 'La justification de clôture est obligatoire.',
            'justification.min'      => 'La justification doit contenir au moins 10 caractères.',
        ]);

        $entity = $this->resolveEntity($type, $id);

        if (! $entity->aDesValideurs()) {
            return back()->withErrors(['justification' => 'Aucun valideur défini. Ajoutez un valideur avant de soumettre.']);
        }

        if ($entity->statut_cloture === 'soumis') {
            return back()->withErrors(['justification' => 'Cette clôture est déjà en attente de validation.']);
        }

        $action = in_array($entity->statut_cloture, ['rejete', 'revisions'])
            ? 'resoumettreCloture'
            : 'soumettreCloture';

        $entity->$action($request->input('justification'));

        // Pièces justificatives — attachées si le modèle supporte HasPiecesJointes
        if ($request->hasFile('pieces_jointes') && method_exists($entity, 'attacherFichiers')) {
            $folder = 'projets/cloture/' . $type . '/' . $entity->id;
            $entity->attacherFichiers($request->file('pieces_jointes'), $folder);
        }

        return back()->with('success', 'Clôture soumise pour validation.');
    }

    /**
     * Approuver la clôture.
     */
    public function approuver(Request $request, string $type, int $id)
    {
        $entity = $this->resolveEntity($type, $id);

        if (! $entity->estValideur()) {
            return back()->withErrors(['validation' => 'Vous n\'êtes pas autorisé à valider cette clôture.']);
        }

        if ($entity->statut_cloture !== 'soumis') {
            return back()->withErrors(['validation' => 'Aucune demande de clôture en attente.']);
        }

        $entity->approuverCloture($request->input('commentaire'));

        // Appliquer automatiquement le statut "Terminé" / "atteint" après approbation
        $this->appliquerCloture($entity, $type);

        return back()->with('success', 'Clôture approuvée.');
    }

    /**
     * Rejeter la clôture.
     */
    public function rejeter(Request $request, string $type, int $id)
    {
        $request->validate([
            'motif' => 'required|string|min:10',
        ], [
            'motif.required' => 'Le motif de rejet est obligatoire.',
            'motif.min'      => 'Le motif doit contenir au moins 10 caractères.',
        ]);

        $entity = $this->resolveEntity($type, $id);

        if (! $entity->estValideur()) {
            return back()->withErrors(['validation' => 'Vous n\'êtes pas autorisé à valider cette clôture.']);
        }

        if ($entity->statut_cloture !== 'soumis') {
            return back()->withErrors(['validation' => 'Aucune demande de clôture en attente.']);
        }

        $entity->rejeterCloture($request->input('motif'));

        return back()->with('success', 'Clôture rejetée.');
    }

    /**
     * Demander des révisions.
     */
    public function revisions(Request $request, string $type, int $id)
    {
        $request->validate([
            'commentaire' => 'required|string|min:10',
        ]);

        $entity = $this->resolveEntity($type, $id);

        if (! $entity->estValideur()) {
            return back()->withErrors(['validation' => 'Vous n\'êtes pas autorisé à demander des révisions.']);
        }

        $entity->demanderRevisions($request->input('commentaire'));

        return back()->with('success', 'Révisions demandées.');
    }

    /**
     * Historique de validation d'une entité (JSON).
     */
    public function historique(string $type, int $id)
    {
        $entity = $this->resolveEntity($type, $id);

        $historique = $entity->historiqueValidation()
            ->with('user:id,name,prenoms')
            ->get()
            ->map(fn($h) => [
                'action'        => $h->action,
                'libelle'       => $h->libelle,
                'icone'         => $h->icone,
                'couleur'       => $h->couleur,
                'justification' => $h->justification,
                'commentaire'   => $h->commentaire,
                'user'          => $h->user ? trim(($h->user->prenoms ?? '') . ' ' . $h->user->name) : null,
                'date'          => $h->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json($historique);
    }

    /**
     * Appliquer automatiquement la clôture (statut Terminé/atteint) après approbation.
     */
    private function appliquerCloture($entity, string $type): void
    {
        $statutTermine = Statut::where('libelle', 'Terminé')->first();

        match ($type) {
            'projet' => $entity->update([
                'statut_id'  => $statutTermine?->id,
                'avancement' => 100,
            ]),
            'phase' => (function () use ($entity, $statutTermine) {
                $entity->update([
                    'statut_id'  => $statutTermine?->id,
                    'avancement' => 100,
                ]);
                $entity->projet?->recalculerAvancement();
            })(),
            'jalon' => $entity->update([
                'statut'      => 'atteint',
                'date_reelle' => now()->toDateString(),
            ]),
            'tache' => (function () use ($entity, $statutTermine) {
                $entity->update([
                    'statut_id'         => $statutTermine?->id,
                    'avancement'        => 100,
                    'statut_validation' => 'approuve',
                    'valide_le'         => now(),
                ]);
                $entity->phase?->recalculerAvancement();
                $entity->projet?->recalculerAvancement();
            })(),
            default => null,
        };
    }
}
