<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Contact;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\Objectif;
use App\Models\Intranet\Opportunite;
use App\Models\Intranet\Priorite;
use App\Models\Intranet\Projet;
use App\Models\Intranet\Statut;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjetController extends Controller
{
    use \App\Traits\Intranet\AppliqueScopesVisibilite;

    public function index(Request $request)
    {
        // Compat rétro : ?mes_projets=1 → ?scopes[]=mes
        if ($request->boolean('mes_projets') && !$request->filled('scopes')) {
            $request->merge(['scopes' => ['mes']]);
        }

        $query = Projet::with(['statut', 'priorite', 'chefProjet', 'auteur'])
            ->withCount(['taches', 'membres'])
            ->recherche($request->input('q'))
            ->when($request->filled('statut'),    fn($q) => $q->where('statut_id', $request->statut))
            ->when($request->filled('categorie'), fn($q) => $q->where('categorie', $request->categorie));

        // Filtre unifié Mes / Groupes / Publiques
        $this->appliqueScopesVisibilite($query, $request, [
            'mes_columns'   => ['chef_projet_id', 'sponsor_id', 'created_by'],
            'mes_relations' => ['membres'],
        ]);

        $projets = $query->orderByDesc('created_at')->paginate(12)->withQueryString();

        $categories = Projet::whereNotNull('categorie')->distinct()->orderBy('categorie')->pluck('categorie');

        $stats = [
            'total'    => Projet::count(),
            'en_cours' => Projet::query()->enCours()->count(),
            'termines' => Projet::whereHas('statut', fn($s) => $s->where('libelle', 'Terminé'))->count(),
        ];

        return view('intranet.projets.index', compact('projets', 'categories', 'stats'));
    }

    public function create()
    {
        return view('intranet.projets.create', $this->formData(new Projet(['avancement' => 0, 'devise' => 'XAF'])));
    }

    public function store(Request $request)
    {
        $request->validate($this->projetRules());

        $projet = DB::transaction(function () use ($request) {
            $data = $request->only([
                'nom', 'description', 'code_projet', 'categorie',
                'statut_id', 'priorite_id', 'chef_projet_id', 'sponsor_id',
                'date_debut', 'date_fin', 'budget_approuve', 'devise', 'objectifs',
                'objectif_id', 'organisation_id', 'contact_id', 'opportunite_id',
            ]);
            $data['created_by'] = auth()->id();
            $projet = Projet::create($data);

            if ($request->filled('membres')) {
                $projet->membres()->sync($request->input('membres'));
            }

            // Pièces jointes
            if ($request->hasFile('pieces_jointes')) {
                $projet->attacherFichiers($request->file('pieces_jointes'), 'projets');
            }

            return $projet;
        });

        return redirect()->route('intranet.projets.show', $projet)->with('success', 'Projet créé.');
    }

    private function projetRules(?int $projetId = null): array
    {
        return [
            'nom'             => 'required|string|max:255',
            'description'     => 'nullable|string',
            'code_projet'     => 'nullable|string|max:50',
            'categorie'       => 'nullable|string|max:80',
            'statut_id'       => 'nullable|exists:intranet_statuts,id',
            'priorite_id'     => 'nullable|exists:intranet_priorites,id',
            'chef_projet_id'  => 'nullable|exists:users,id',
            'sponsor_id'      => 'nullable|exists:users,id',
            'date_debut'      => 'nullable|date',
            'date_fin'        => 'nullable|date|after_or_equal:date_debut',
            'budget_approuve' => 'nullable|numeric|min:0',
            'devise'          => 'nullable|string|max:10',
            'objectifs'       => 'nullable|string',
            'avancement'      => 'nullable|integer|min:0|max:100',
            'membres'         => 'nullable|array',
            'membres.*'       => 'integer|exists:users,id',
            'objectif_id'     => 'nullable|exists:intranet_objectifs,id',
            'organisation_id' => 'nullable|exists:intranet_contact_organisations,id',
            'contact_id'      => 'nullable|exists:intranet_contacts,id',
            'opportunite_id'  => 'nullable|exists:intranet_opportunites,id',
            'pieces_jointes'  => 'nullable|array',
            'pieces_jointes.*'=> 'file|max:51200',
        ];
    }

    public function show(Projet $projet)
    {
        $projet->load([
            'statut', 'priorite', 'auteur', 'chefProjet', 'sponsor',
            'membres', 'taches.statut', 'taches.priorite', 'taches.responsable',
            'phases', 'commentaires.user',
        ]);

        return view('intranet.projets.show', compact('projet'));
    }

    public function edit(Projet $projet)
    {
        $projet->load('membres');
        return view('intranet.projets.edit', $this->formData($projet));
    }

    public function update(Request $request, Projet $projet)
    {
        $request->validate($this->projetRules($projet->id));

        DB::transaction(function () use ($request, $projet) {
            $projet->update($request->only([
                'nom', 'description', 'code_projet', 'categorie',
                'statut_id', 'priorite_id', 'chef_projet_id', 'sponsor_id',
                'date_debut', 'date_fin', 'budget_approuve', 'devise',
                'objectifs', 'avancement',
                'objectif_id', 'organisation_id', 'contact_id', 'opportunite_id',
            ]));
            if ($request->has('membres')) {
                $projet->membres()->sync($request->input('membres', []));
            }
            if ($request->hasFile('pieces_jointes')) {
                $projet->attacherFichiers($request->file('pieces_jointes'), 'projets');
            }
        });

        return redirect()->route('intranet.projets.show', $projet)->with('success', 'Projet mis à jour.');
    }

    public function destroy(Projet $projet)
    {
        $projet->delete();
        return redirect()->route('intranet.projets.index')->with('success', 'Projet supprimé.');
    }

    private function formData(Projet $projet): array
    {
        return [
            'projet'         => $projet,
            'statuts'        => Statut::orderBy('id')->get(),
            'priorites'      => Priorite::orderBy('id')->get(),
            'utilisateurs'   => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'membresIds'     => $projet->exists ? $projet->membres->pluck('id')->all() : [],
            'objectifs'      => Objectif::actif()->orderBy('titre')->get(['id', 'titre']),
            'organisations'  => ContactOrganisation::orderBy('nom')->get(['id', 'nom']),
            'contacts'       => Contact::orderBy('nom')->get(['id', 'nom', 'prenoms']),
            'opportunites'   => Opportunite::orderBy('titre')->get(['id', 'titre']),
        ];
    }
}
