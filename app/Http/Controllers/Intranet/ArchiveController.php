<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\ArchiveRequest;
use App\Models\Intranet\Archive;
use App\Models\Intranet\ArchiveDossier;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\PieceJointe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    private const FOLDER = 'intranet/archives';

    public function index(Request $request)
    {
        $dossierId = $request->input('dossier');
        $dossier   = $dossierId ? ArchiveDossier::findOrFail($dossierId) : null;

        $sousDossiers = ArchiveDossier::withCount('archives')
            ->where('parent_id', $dossierId)
            ->orderBy('nom')
            ->get();

        if ($request->filled('q')) {
            $archives = Archive::with(['auteur', 'dossier'])
                ->recherche($request->input('q'))
                ->when($request->filled('nature'), fn($q) => $q->where('nature', $request->nature))
                ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
                ->orderByDesc('date_archivage')
                ->paginate(20)->withQueryString();
        } else {
            $archives = Archive::with(['auteur', 'dossier'])
                ->where('dossier_id', $dossierId)
                ->when($request->filled('nature'), fn($q) => $q->where('nature', $request->nature))
                ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
                ->orderByDesc('date_archivage')
                ->paginate(20)->withQueryString();
        }

        $natures = Archive::whereNotNull('nature')->distinct()->orderBy('nature')->pluck('nature');

        $stats = [
            'dossiers'   => ArchiveDossier::count(),
            'archives'   => Archive::count(),
            'a_detruire' => Archive::query()->aDetruire()->count(),
        ];

        return view('intranet.archives.index', compact('archives', 'sousDossiers', 'dossier', 'natures', 'stats'));
    }

    public function create(Request $request)
    {
        return view('intranet.archives.create', [
            'archive'      => new Archive(['dossier_id' => $request->input('dossier'), 'statut' => 'actif']),
            'dossiers'     => ArchiveDossier::orderBy('nom')->get(['id', 'nom']),
            'users'        => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'      => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'  => [],
            'ciblesGroupes'=> [],
        ]);
    }

    public function store(ArchiveRequest $request)
    {
        $archive = DB::transaction(function () use ($request) {
            $data = $request->safe()->except([
                'fichier', 'pieces_jointes', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags']       = $request->tagsArray();
            $data['created_by'] = auth()->id();

            if ($request->hasFile('fichier')) {
                $file = $request->file('fichier');
                $data['fichier']      = $file->store(self::FOLDER, 'public');
                $data['nom_original'] = $file->getClientOriginalName();
                $data['mime_type']    = $file->getMimeType();
                $data['taille']       = $file->getSize();
            }

            $archive = Archive::create($data);

            if ($request->hasFile('pieces_jointes')) {
                $archive->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $archive->id);
            }

            $this->synchroniserPublication($archive, $request);
            return $archive;
        });

        return redirect()->route('intranet.archives.show', $archive)
            ->with('success', 'Document archivé.');
    }

    public function show(Archive $archive)
    {
        $archive->load(['auteur', 'dossier', 'publication.cibles', 'piecesJointes', 'commentaires.user']);
        if (auth()->check()) { $archive->enregistrerVue(); $archive->increment('vues_count'); }
        return view('intranet.archives.show', compact('archive'));
    }

    public function edit(Archive $archive)
    {
        $archive->load(['publication.cibles', 'piecesJointes']);
        $ciblesUsers   = $archive->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $archive->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return view('intranet.archives.edit', [
            'archive'      => $archive,
            'dossiers'     => ArchiveDossier::orderBy('nom')->get(['id', 'nom']),
            'users'        => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'      => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'  => $ciblesUsers,
            'ciblesGroupes'=> $ciblesGroupes,
        ]);
    }

    public function update(ArchiveRequest $request, Archive $archive)
    {
        DB::transaction(function () use ($request, $archive) {
            $data = $request->safe()->except([
                'fichier', 'pieces_jointes', 'tags',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags'] = $request->tagsArray();

            if ($request->hasFile('fichier')) {
                if ($archive->fichier) Storage::disk('public')->delete($archive->fichier);
                $file = $request->file('fichier');
                $data['fichier']      = $file->store(self::FOLDER, 'public');
                $data['nom_original'] = $file->getClientOriginalName();
                $data['mime_type']    = $file->getMimeType();
                $data['taille']       = $file->getSize();
            }

            // Recalcul date destruction si durée modifiée
            if (isset($data['duree_conservation_mois']) && $data['duree_conservation_mois'] && isset($data['date_archivage'])) {
                $data['date_destruction_prevue'] = \Carbon\Carbon::parse($data['date_archivage'])->addMonths($data['duree_conservation_mois']);
            }

            $archive->update($data);

            if ($request->hasFile('pieces_jointes')) {
                $archive->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $archive->id);
            }

            $this->synchroniserPublication($archive, $request);
        });

        return redirect()->route('intranet.archives.show', $archive)
            ->with('success', 'Archive mise à jour.');
    }

    public function destroy(Archive $archive)
    {
        if ($archive->fichier) Storage::disk('public')->delete($archive->fichier);
        $archive->delete();
        return redirect()->route('intranet.archives.index', ['dossier' => $archive->dossier_id])
            ->with('success', 'Archive supprimée.');
    }

    public function download(Archive $archive)
    {
        abort_if(! $archive->fichier, 404);
        $archive->increment('telechargements_count');
        return Storage::disk('public')->download($archive->fichier, $archive->nom_original ?? basename($archive->fichier));
    }

    public function destroyPieceJointe(Archive $archive, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $archive->id && $piece->attachable_type === Archive::class, 404);
        $archive->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    // Dossiers
    public function storeDossier(Request $request)
    {
        $request->validate(['nom' => 'required|string|max:255', 'parent_id' => 'nullable|exists:intranet_archive_dossiers,id',
            'couleur' => 'nullable|string|max:20', 'icone' => 'nullable|string|max:50', 'couverture' => 'nullable|image|max:5120']);

        $data = $request->only(['nom', 'description', 'parent_id', 'couleur', 'icone']);
        $data['created_by'] = auth()->id();
        if ($request->hasFile('couverture')) $data['couverture'] = $request->file('couverture')->store(self::FOLDER . '/dossiers', 'public');

        $dossier = ArchiveDossier::create($data);
        return redirect()->route('intranet.archives.index', ['dossier' => $dossier->id])->with('success', 'Dossier créé.');
    }

    public function updateDossier(Request $request, ArchiveDossier $dossier)
    {
        $request->validate(['nom' => 'required|string|max:255', 'couleur' => 'nullable|string|max:20', 'icone' => 'nullable|string|max:50', 'couverture' => 'nullable|image|max:5120']);
        $data = $request->only(['nom', 'description', 'parent_id', 'couleur', 'icone']);
        if ($request->hasFile('couverture')) {
            if ($dossier->couverture) Storage::disk('public')->delete($dossier->couverture);
            $data['couverture'] = $request->file('couverture')->store(self::FOLDER . '/dossiers', 'public');
        }
        $dossier->update($data);
        return redirect()->route('intranet.archives.index', ['dossier' => $dossier->id])->with('success', 'Dossier modifié.');
    }

    public function destroyDossier(ArchiveDossier $dossier)
    {
        $dossier->archives()->update(['dossier_id' => $dossier->parent_id]);
        $dossier->enfants()->update(['parent_id' => $dossier->parent_id]);
        $dossier->delete();
        return redirect()->route('intranet.archives.index', ['dossier' => $dossier->parent_id])->with('success', 'Dossier supprimé.');
    }

    private function synchroniserPublication(Archive $archive, ArchiveRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) { $cibles[] = ['type' => 'user', 'id' => (int) $userId]; }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) { $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId]; }
        $archive->publier($request->input('visibilite', 'prive'), $cibles, [
            'likes_actifs' => $request->boolean('likes_actifs'),
            'commentaires_actifs' => $request->boolean('commentaires_actifs'),
            'publie_le' => now(),
        ]);
    }
}
