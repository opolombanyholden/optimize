<?php

namespace App\Http\Controllers\Appro;

use App\Http\Controllers\Controller;
use App\Models\CommandeInterne;
use App\Models\CommandeInterneLigne;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommandeInterneController extends Controller
{
    public function index(Request $request)
    {
        $q = CommandeInterne::query()->with(['demandeur', 'superieur', 'lignes']);

        // Onglet — mes demandes vs à valider vs à traiter appro
        // Le super-admin bypass les filtres utilisateur : il voit toutes les commandes internes,
        // sur tous les onglets. Les filtres de statut spécifiques à l'onglet restent appliqués.
        $u = $request->user();
        $onglet = $request->get('onglet', $u->hasRole('super-admin') ? 'toutes' : 'mes');
        $isSuperAdmin = $u->hasRole('super-admin');

        if ($onglet === 'mes' && !$isSuperAdmin) {
            $q->where('demandeur_id', $u->id);
        } elseif ($onglet === 'a_valider') {
            $q->where('statut', CommandeInterne::STATUT_EN_ATTENTE_N1);
            if (!$isSuperAdmin) $q->where('superieur_id', $u->id);
        } elseif ($onglet === 'appro') {
            $q->whereIn('statut', [
                CommandeInterne::STATUT_VALIDEE_N1,
                CommandeInterne::STATUT_TRANSMISE_APPRO,
                CommandeInterne::STATUT_LIVREE_PARTIELLE,
            ]);
        }
        // 'toutes' = pas de filtre onglet

        $q->when($request->q, fn($qb, $s) => $qb->where('objet', 'ilike', "%{$s}%")->orWhere('numero', 'ilike', "%{$s}%"))
          ->when($request->statut !== null && $request->statut !== '', fn($qb) => $qb->where('statut', (int) $request->statut));

        return view('appro.commandes-internes.index', [
            'commandes' => $q->orderByDesc('date_demande')->paginate(15)->withQueryString(),
            'statuts' => CommandeInterne::STATUTS,
            'onglet' => $onglet,
        ]);
    }

    public function create(Request $request)
    {
        $cmd = new CommandeInterne(['date_demande' => now()->toDateString()]);
        return view('appro.commandes-internes.create', $this->formData($cmd, $request->user()));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        DB::transaction(function () use ($data, $request) {
            $cmd = CommandeInterne::create([
                'numero' => CommandeInterne::genererNumero(),
                'objet' => $data['objet'],
                'justification' => $data['justification'] ?? null,
                'demandeur_id' => $request->user()->id,
                'superieur_id' => $data['superieur_id'] ?? null,
                'date_demande' => $data['date_demande'] ?? now()->toDateString(),
                'date_besoin' => $data['date_besoin'] ?? null,
                'statut' => CommandeInterne::STATUT_BROUILLON,
            ]);
            $this->syncLignes($cmd, $data['lignes'] ?? []);
            $cmd->recalculerMontant();
            if ($request->hasFile('pieces_jointes')) {
                $cmd->attacherFichiers($request->file('pieces_jointes'), 'appro/commandes-internes');
            }
            $this->createdCmd = $cmd;
        });
        return redirect()->route('appro.commandes-internes.show', $this->createdCmd)->with('success', 'Demande créée en brouillon.');
    }

    private CommandeInterne $createdCmd;

    public function show(CommandeInterne $commande)
    {
        $commande->load(['lignes.produit', 'livraisons.lignes.commandeLigne.produit', 'livraisons.livreur', 'demandeur', 'superieur', 'valideurAppro', 'commandeFournisseur', 'piecesJointes']);
        return view('appro.commandes-internes.show', ['commande' => $commande]);
    }

    public function edit(CommandeInterne $commande)
    {
        if (!$commande->peutEtreModifiee()) {
            return redirect()->route('appro.commandes-internes.show', $commande)->with('error', 'Cette demande n\'est plus modifiable.');
        }
        return view('appro.commandes-internes.edit', $this->formData($commande, request()->user()));
    }

    public function update(Request $request, CommandeInterne $commande)
    {
        if (!$commande->peutEtreModifiee()) return back()->with('error', 'Non modifiable.');
        $data = $this->validateData($request);
        DB::transaction(function () use ($commande, $data, $request) {
            $commande->update([
                'objet' => $data['objet'],
                'justification' => $data['justification'] ?? null,
                'superieur_id' => $data['superieur_id'] ?? null,
                'date_besoin' => $data['date_besoin'] ?? null,
            ]);
            $this->syncLignes($commande, $data['lignes'] ?? [], true);
            $commande->recalculerMontant();
            if ($request->hasFile('pieces_jointes')) {
                $commande->attacherFichiers($request->file('pieces_jointes'), 'appro/commandes-internes');
            }
        });
        return redirect()->route('appro.commandes-internes.show', $commande)->with('success', 'Demande mise à jour.');
    }

    public function destroy(CommandeInterne $commande)
    {
        if (!$commande->peutEtreModifiee()) return back()->with('error', 'Non supprimable.');
        $commande->delete();
        return redirect()->route('appro.commandes-internes.index')->with('success', 'Demande supprimée.');
    }

    // ═════════ Workflow ═════════

    public function soumettreN1(CommandeInterne $commande)
    {
        if (!$commande->peutEtreSoumise()) return back()->with('error', 'Non soumissible : ajoutez au moins une ligne.');
        $commande->soumettreN1();
        $msg = $commande->superieur_id
            ? 'Demande transmise à votre N+1.'
            : 'Demande transmise directement à Appro (aucun N+1 désigné). L\'équipe Appro décidera de traiter ou d\'assigner un valideur.';
        return back()->with('success', $msg);
    }

    /**
     * Après-coup : Appro assigne un N+1 valideur à une commande soumise sans N+1.
     * Notifie automatiquement le demandeur.
     */
    public function assignerN1(Request $request, CommandeInterne $commande)
    {
        if (!$commande->peutEtreAssignee()) return back()->with('error', 'Cette commande ne peut plus recevoir de N+1.');
        $data = $request->validate([
            'superieur_id' => 'required|exists:users,id',
            'commentaire_appro' => 'nullable|string|max:1000',
        ]);
        $commande->assignerN1((int) $data['superieur_id'], $data['commentaire_appro'] ?? null, $request->user()->id);
        $n1 = \App\Models\User::find($data['superieur_id']);
        return back()->with('success', 'Demande transmise à ' . trim(($n1?->name ?? '') . ' ' . ($n1?->prenoms ?? '')) . ' pour validation. Le demandeur a été notifié.');
    }

    public function validerN1(CommandeInterne $commande)
    {
        if (!$commande->peutEtreValideeN1()) return back()->with('error', 'Statut incompatible.');
        if ($commande->superieur_id !== request()->user()->id) {
            return back()->with('error', 'Vous n\'êtes pas le N+1 désigné.');
        }
        $commande->validerN1();
        return back()->with('success', 'Demande validée. En attente de transmission Appro.');
    }

    public function refuserN1(Request $request, CommandeInterne $commande)
    {
        if (!$commande->peutEtreValideeN1()) return back()->with('error', 'Statut incompatible.');
        if ($commande->superieur_id !== $request->user()->id) return back()->with('error', 'Non autorisé.');
        $data = $request->validate(['motif_refus_n1' => 'required|string|min:5|max:1000']);
        $commande->refuserN1($data['motif_refus_n1']);
        return back()->with('success', 'Demande refusée.');
    }

    public function transmettreAppro(Request $request, CommandeInterne $commande)
    {
        if (!$commande->peutEtreTransmise()) return back()->with('error', 'Statut incompatible.');
        $commande->transmettreAppro($request->user()->id);
        return back()->with('success', 'Demande prise en charge par Appro.');
    }

    public function livrer(Request $request, CommandeInterne $commande)
    {
        if (!$commande->peutEtreLivree()) return back()->with('error', 'Statut incompatible.');
        $data = $request->validate([
            'quantites' => 'required|array',
            'quantites.*' => 'nullable|numeric|min:0',
            'emplacements_source' => 'nullable|array',
            'emplacements_source.*' => 'nullable|exists:emplacements,id',
            'commentaire' => 'nullable|string|max:1000',
            'recu_par' => 'nullable|exists:users,id',
        ]);
        $sources = array_filter($data['emplacements_source'] ?? []);

        // Contrôle métier : refuse toute livraison d'un article dont le stock à l'emplacement
        // source est insuffisant (ou en rupture). Cumul par (produit, emplacement) au cas où
        // plusieurs lignes viseraient le même couple.
        $besoins = [];
        foreach ($commande->lignes as $ligne) {
            $qte = (float) ($data['quantites'][$ligne->id] ?? 0);
            if ($qte <= 0 || !$ligne->produit_id) continue;
            $empId = (int) ($sources[$ligne->id] ?? $ligne->emplacement_source_id ?? 0);
            if (!$empId) continue;
            $besoins[$ligne->produit_id . '_' . $empId] = [
                'produit_id'     => $ligne->produit_id,
                'emplacement_id' => $empId,
                'designation'    => $ligne->designation,
                'qte'            => ($besoins[$ligne->produit_id . '_' . $empId]['qte'] ?? 0) + $qte,
            ];
        }
        foreach ($besoins as $b) {
            $produit = \App\Models\Produit::find($b['produit_id']);
            if (!$produit || !$produit->est_stockable) continue;
            $dispo = $produit->stockDans($b['emplacement_id']);
            if ($dispo <= 0) {
                $emp = \App\Models\Emplacement::find($b['emplacement_id']);
                return back()->with('error', "Livraison impossible : « {$b['designation']} » est en rupture de stock à l'emplacement « {$emp?->libelle} ». Réapprovisionnez d'abord ou choisissez un autre emplacement.");
            }
            if ($b['qte'] > $dispo) {
                $emp = \App\Models\Emplacement::find($b['emplacement_id']);
                return back()->with('error', "Livraison impossible : stock insuffisant pour « {$b['designation']} » à « {$emp?->libelle} » (disponible : {$dispo}, demandé : {$b['qte']}).");
            }
        }

        $commande->livrer(
            $data['quantites'],
            $request->user()->id,
            $data['recu_par'] ?? null,
            $data['commentaire'] ?? null,
            $sources,
        );
        return back()->with('success', 'Livraison enregistrée.');
    }

    public function annuler(Request $request, CommandeInterne $commande)
    {
        if (!$commande->peutEtreAnnulee()) return back()->with('error', 'Statut incompatible.');
        $data = $request->validate(['motif_annulation' => 'required|string|min:5|max:1000']);
        $commande->annuler($data['motif_annulation']);
        return back()->with('success', 'Demande annulée.');
    }

    // ═════════ Helpers ═════════

    private function formData(CommandeInterne $cmd, User $user): array
    {
        return [
            'commande' => $cmd,
            'produits' => Produit::orderBy('designation')->get(['id', 'code', 'designation', 'unite_mesure', 'prix_unitaire', 'type_article']),
            'utilisateurs' => User::actif()
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get(['id', 'name', 'prenoms', 'email']),
        ];
    }

    private function syncLignes(CommandeInterne $cmd, array $lignesInput, bool $replace = false): void
    {
        if ($replace) $cmd->lignes()->delete();
        // Chaque ligne référence obligatoirement un article du catalogue : la désignation
        // et l'unité sont dérivées du Produit sélectionné (pas d'article libre).
        $produitIds = collect($lignesInput)->pluck('produit_id')->filter()->unique();
        $produits = Produit::whereIn('id', $produitIds)->get()->keyBy('id');
        foreach ($lignesInput as $l) {
            $p = $produits->get($l['produit_id'] ?? null);
            if (!$p) continue;
            $cmd->lignes()->create([
                'produit_id'          => $p->id,
                'designation'         => $p->designation,
                'quantite_demandee'   => (float) ($l['quantite_demandee'] ?? 1),
                'unite'               => $p->unite_mesure,
                // Prix estimé dérivé du prix unitaire catalogue — indicatif pour le budget
                'prix_unitaire_estime' => (float) ($p->prix_unitaire ?? 0),
                'commentaire'         => $l['commentaire'] ?? null,
            ]);
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'objet' => 'required|string|max:255',
            'justification' => 'nullable|string|max:5000',
            'superieur_id' => 'nullable|exists:users,id',
            'date_demande' => 'nullable|date',
            'date_besoin' => 'nullable|date',
            'lignes' => 'nullable|array',
            'lignes.*.produit_id' => 'required|exists:produits,id',
            'lignes.*.quantite_demandee' => 'required|numeric|min:0.001',
            'lignes.*.commentaire' => 'nullable|string|max:500',
            'pieces_jointes.*' => 'nullable|file|max:20480',
        ], [
            'lignes.*.produit_id.required' => 'Chaque ligne doit référencer un article du catalogue.',
            'lignes.*.produit_id.exists'   => 'Article catalogue introuvable.',
        ]);
    }
}
