<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\TemplateRequest;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\Template;
use App\Models\Intranet\TemplateCategorie;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{
    private const FOLDER = 'intranet/templates';

    public function index(Request $request)
    {
        $templates = Template::with(['categorie', 'auteur'])
            ->recherche($request->input('q'))
            ->when($request->filled('categorie'), fn($q) => $q->where('categorie_id', $request->categorie))
            ->when($request->filled('format'),    fn($q) => $q->where('format', $request->format))
            ->orderByDesc('utilisations')
            ->orderByDesc('created_at')
            ->paginate(18)
            ->withQueryString();

        $categories = TemplateCategorie::withCount('templates')->orderBy('ordre')->get();

        return view('intranet.templates.index', compact('templates', 'categories'));
    }

    public function create()
    {
        return view('intranet.templates.create', [
            'template'     => new Template(['format' => 'html']),
            'categories'   => TemplateCategorie::orderBy('ordre')->get(),
            'users'        => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'      => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'  => [],
            'ciblesGroupes'=> [],
        ]);
    }

    public function store(TemplateRequest $request)
    {
        $template = DB::transaction(function () use ($request) {
            $data = $request->safe()->except([
                'fichier_modele', 'tags', 'variables',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags']       = $request->tagsArray();
            $data['variables']  = $request->variablesArray();
            $data['created_by'] = auth()->id();

            if ($request->hasFile('fichier_modele')) {
                $file = $request->file('fichier_modele');
                $data['fichier_modele'] = $file->store(self::FOLDER, 'public');
                $data['nom_original']   = $file->getClientOriginalName();
                $data['mime_type']      = $file->getMimeType();
                $data['taille']         = $file->getSize();
            }

            // Auto-détecter les variables du contenu HTML
            if ($data['format'] === 'html' && !empty($data['contenu']) && empty($data['variables'])) {
                $t = new Template($data);
                $data['variables'] = $t->detecterVariables();
            }

            $template = Template::create($data);
            $this->synchroniserPublication($template, $request);
            return $template;
        });

        return redirect()->route('intranet.templates.show', $template)
            ->with('success', 'Template créé.');
    }

    public function show(Template $template)
    {
        $template->load(['categorie', 'auteur', 'publication.cibles', 'commentaires.user']);
        if (auth()->check()) { $template->enregistrerVue(); $template->increment('vues_count'); }
        return view('intranet.templates.show', compact('template'));
    }

    public function edit(Template $template)
    {
        $template->load('publication.cibles');
        $ciblesUsers   = $template->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $template->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return view('intranet.templates.edit', [
            'template'     => $template,
            'categories'   => TemplateCategorie::orderBy('ordre')->get(),
            'users'        => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'      => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'  => $ciblesUsers,
            'ciblesGroupes'=> $ciblesGroupes,
        ]);
    }

    public function update(TemplateRequest $request, Template $template)
    {
        DB::transaction(function () use ($request, $template) {
            $data = $request->safe()->except([
                'fichier_modele', 'tags', 'variables',
                'visibilite', 'likes_actifs', 'commentaires_actifs',
                'cibles_users', 'cibles_groupes',
            ]);
            $data['tags']      = $request->tagsArray();
            $data['variables'] = $request->variablesArray();

            if ($request->hasFile('fichier_modele')) {
                if ($template->fichier_modele) Storage::disk('public')->delete($template->fichier_modele);
                $file = $request->file('fichier_modele');
                $data['fichier_modele'] = $file->store(self::FOLDER, 'public');
                $data['nom_original']   = $file->getClientOriginalName();
                $data['mime_type']      = $file->getMimeType();
                $data['taille']         = $file->getSize();
            }

            if ($data['format'] === 'html' && !empty($data['contenu']) && empty($data['variables'])) {
                $t = new Template($data);
                $data['variables'] = $t->detecterVariables();
            }

            $template->update($data);
            $this->synchroniserPublication($template, $request);
        });

        return redirect()->route('intranet.templates.show', $template)
            ->with('success', 'Template mis à jour.');
    }

    public function destroy(Template $template)
    {
        if ($template->fichier_modele) Storage::disk('public')->delete($template->fichier_modele);
        $template->delete();
        return redirect()->route('intranet.templates.index')->with('success', 'Template supprimé.');
    }

    public function download(Template $template)
    {
        abort_if(! $template->fichier_modele, 404);
        return Storage::disk('public')->download(
            $template->fichier_modele,
            $template->nom_original ?? basename($template->fichier_modele)
        );
    }

    /**
     * Affiche le formulaire de génération (remplir les variables).
     */
    public function utiliser(Template $template)
    {
        $variables = $template->variables ?? $template->detecterVariables();
        return view('intranet.templates.utiliser', compact('template', 'variables'));
    }

    /**
     * Génère le document avec les variables remplies.
     */
    public function generer(Request $request, Template $template)
    {
        $valeurs = $request->input('vars', []);
        $html    = $template->genererDocument($valeurs);
        $template->increment('utilisations');

        return view('intranet.templates.resultat', compact('template', 'html'));
    }

    // ══════════════════════════════════════════════════════
    private function synchroniserPublication(Template $template, TemplateRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) { $cibles[] = ['type' => 'user', 'id' => (int) $userId]; }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) { $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId]; }
        $template->publier($request->input('visibilite', 'public'), $cibles, [
            'likes_actifs' => $request->boolean('likes_actifs'),
            'commentaires_actifs' => $request->boolean('commentaires_actifs'),
            'publie_le' => now(),
        ]);
    }
}
