<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\AnnonceRequest;
use App\Models\Intranet\Annonce;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\PieceJointe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnnonceController extends Controller
{
    private const FOLDER = 'intranet/annonces';

    // ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $annonces = Annonce::with(['auteur', 'publication', 'piecesJointes'])
            ->withCount(['likes', 'commentaires'])
            ->recherche($request->input('q'))
            ->when($request->filled('categorie'), fn($q) => $q->where('categorie', $request->categorie))
            ->when($request->filled('statut'), function ($q) use ($request) {
                if ($request->statut === 'urgentes')  $q->urgentes();
                if ($request->statut === 'epinglees') $q->epinglees();
                if ($request->statut === 'actives')   $q->actives();
            })
            ->orderByDesc('epingle')
            ->orderByDesc('is_urgent')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Annonce::query()
            ->whereNotNull('categorie')
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie');

        return view('intranet.annonces.index', compact('annonces', 'categories'));
    }

    // ──────────────────────────────────────────────────────
    public function create()
    {
        return view('intranet.annonces.create', [
            'annonce' => new Annonce(['couleur' => '#7C3AED']),
            'users'   => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']),
            'groupes' => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
        ]);
    }

    // ──────────────────────────────────────────────────────
    public function store(AnnonceRequest $request)
    {
        $annonce = DB::transaction(function () use ($request) {
            $data = $request->safe()->only([
                'title', 'extrait', 'categorie', 'content',
                'date_debut', 'date_fin', 'is_urgent', 'epingle', 'couleur',
            ]);
            $data['created_by'] = auth()->id();
            $data['is_public']  = $request->input('visibilite') === 'public';

            // Média principal
            if ($request->hasFile('media_principal')) {
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $annonce = Annonce::create($data);

            // Pièces jointes additionnelles
            if ($request->hasFile('pieces_jointes')) {
                $annonce->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $annonce->id);
            }

            // Publication
            $this->synchroniserPublication($annonce, $request);

            return $annonce;
        });

        return redirect()
            ->route('intranet.annonces.show', $annonce)
            ->with('success', 'Annonce créée avec succès.');
    }

    // ──────────────────────────────────────────────────────
    public function show(Annonce $annonce)
    {
        $annonce->load([
            'auteur', 'publication.cibles', 'piecesJointes',
            'commentaires.user', 'commentaires.reponses.user',
        ]);

        // Enregistrer la vue
        if (auth()->check()) {
            $annonce->enregistrerVue();
            $annonce->increment('vues_count');
        }

        return view('intranet.annonces.show', compact('annonce'));
    }

    // ──────────────────────────────────────────────────────
    public function edit(Annonce $annonce)
    {
        $annonce->load(['publication.cibles', 'piecesJointes']);

        $ciblesUsers   = $annonce->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $annonce->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return view('intranet.annonces.edit', [
            'annonce'        => $annonce,
            'users'          => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']),
            'groupes'        => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'    => $ciblesUsers,
            'ciblesGroupes'  => $ciblesGroupes,
        ]);
    }

    // ──────────────────────────────────────────────────────
    public function update(AnnonceRequest $request, Annonce $annonce)
    {
        DB::transaction(function () use ($request, $annonce) {
            $data = $request->safe()->only([
                'title', 'extrait', 'categorie', 'content',
                'date_debut', 'date_fin', 'is_urgent', 'epingle', 'couleur',
            ]);
            $data['is_public'] = $request->input('visibilite') === 'public';

            // Remplacement du média principal
            if ($request->hasFile('media_principal')) {
                if ($annonce->media_principal) {
                    Storage::disk('public')->delete($annonce->media_principal);
                }
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $annonce->update($data);

            // Nouvelles pièces jointes
            if ($request->hasFile('pieces_jointes')) {
                $annonce->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $annonce->id);
            }

            // Synchronisation publication
            $this->synchroniserPublication($annonce, $request);
        });

        return redirect()
            ->route('intranet.annonces.show', $annonce)
            ->with('success', 'Annonce mise à jour.');
    }

    // ──────────────────────────────────────────────────────
    public function destroy(Annonce $annonce)
    {
        $annonce->delete();
        return redirect()
            ->route('intranet.annonces.index')
            ->with('success', 'Annonce supprimée.');
    }

    // ──────────────────────────────────────────────────────
    // Suppression d'une pièce jointe individuelle (AJAX ou redirect)
    public function destroyPieceJointe(Annonce $annonce, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $annonce->id && $piece->attachable_type === Annonce::class, 404);
        $annonce->detacherFichier($piece->id);

        return back()->with('success', 'Pièce jointe supprimée.');
    }

    // ══════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════
    private function categorieMedia(?string $mime): string
    {
        if (! $mime) return 'document';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'document';
    }

    private function synchroniserPublication(Annonce $annonce, AnnonceRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) {
            $cibles[] = ['type' => 'user', 'id' => (int) $userId];
        }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) {
            $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId];
        }

        $annonce->publier(
            $request->input('visibilite', 'public'),
            $cibles,
            [
                'likes_actifs'        => $request->boolean('likes_actifs'),
                'commentaires_actifs' => $request->boolean('commentaires_actifs'),
                'publie_le'           => $request->input('publie_le') ?: now(),
                'expire_le'           => $request->input('expire_le'),
            ]
        );
    }
}
