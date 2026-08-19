@extends('layouts.app')
@section('title', $commande->numero_commande)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item"><a href="{{ route('appro.commandes.index') }}">Commandes</a></li>
    <li class="breadcrumb-item active">{{ $commande->numero_commande }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">
            <code>{{ $commande->numero_commande }}</code>
            @if($commande->objet)<span class="ms-2">— {{ $commande->objet }}</span>@endif
            <span class="badge bg-{{ $commande->statut_couleur }} ms-2">{{ $commande->statut_libelle }}</span>
        </h1>
        <p class="text-muted mb-0">
            <strong>Fournisseur :</strong> {{ $commande->fournisseur_libelle ?: '—' }}
            · <strong>Date :</strong> {{ $commande->date_commande?->format('d/m/Y') }}
            @if($commande->date_livraison_prevue) · <strong>Livraison prévue :</strong> {{ $commande->date_livraison_prevue->format('d/m/Y') }}@endif
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('update:commande')
            @if($commande->peutEtreModifiee())
                <a href="{{ route('appro.commandes.edit', $commande) }}" class="btn btn-outline-secondary"><i class="fas fa-pen me-1"></i> Modifier</a>
            @endif
            @if($commande->peutEtreSoumise())
                <form action="{{ route('appro.commandes.soumettre', $commande) }}" method="POST" class="d-inline" onsubmit="return confirm('Soumettre pour approbation ?');">
                    @csrf <button class="btn btn-info text-white"><i class="fas fa-paper-plane me-1"></i> Soumettre</button>
                </form>
            @endif
        @endcan
        @can('validate:commande')
            @if($commande->peutEtreApprouvee())
                <form action="{{ route('appro.commandes.approuver', $commande) }}" method="POST" class="d-inline" onsubmit="return confirm('Approuver cette commande ?');">
                    @csrf <button class="btn btn-primary"><i class="fas fa-check me-1"></i> Approuver</button>
                </form>
            @endif
        @endcan
        @can('update:commande')
            @if($commande->peutRecevoirLivraison())
                <a href="{{ route('appro.livraisons-fournisseur.create', ['commande_id' => $commande->id]) }}" class="btn btn-success">
                    <i class="fas fa-truck-loading me-1"></i> Enregistrer une livraison
                </a>
            @endif
            @if($commande->peutEtreAnnulee())
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal-annuler-cmd">
                    <i class="fas fa-ban me-1"></i> Annuler
                </button>
            @endif
        @endcan
        @can('create:facture')
            @if($commande->peutGenererFacture())
                <form action="{{ route('appro.commandes.generer-facture', $commande) }}" method="POST" class="d-inline">
                    @csrf <button class="btn btn-outline-primary"><i class="fas fa-file-invoice me-1"></i> Générer facture</button>
                </form>
            @endif
        @endcan
        <a href="{{ route('appro.commandes.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
    </div>
</div>

@if($commande->facture_id)
<div class="alert alert-info">
    <i class="fas fa-file-invoice me-1"></i>
    Une facture a été générée : <a href="{{ route('finance.factures.show', $commande->facture) }}"><strong>{{ $commande->facture?->numero }}</strong></a>
</div>
@endif

@if($commande->statut === \App\Models\CommandeFournisseur::STATUT_ANNULEE)
<div class="alert alert-warning">
    <strong>Commande annulée</strong> le {{ $commande->annule_at?->format('d/m/Y H:i') }} par {{ $commande->annuleur?->name }}
    <div class="mt-1"><strong>Motif :</strong> {{ $commande->motif_annulation }}</div>
</div>
@endif

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-list me-2"></i> Articles ({{ $commande->lignes->count() }})</h6></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Désignation</th>
                    <th>Produit référentiel</th>
                    <th class="text-end">Qté commandée</th>
                    <th class="text-end">Qté livrée</th>
                    <th class="text-end">Prix unit.</th>
                    <th class="text-end">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commande->lignes as $l)
                    <tr>
                        <td><strong>{{ $l->designation }}</strong>
                            @if($l->observation)<br><small class="text-muted">{{ $l->observation }}</small>@endif
                        </td>
                        <td>
                            @if($l->produit)
                                <small><code>{{ $l->produit->code }}</code></small>
                                <br><small class="text-muted">{{ $l->produit->designation }}</small>
                            @else
                                <em class="text-muted small">Article libre</em>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format((float) $l->quantite_commandee, 2, ',', ' ') }}</td>
                        <td class="text-end">
                            {{ number_format((float) $l->quantite_livree, 2, ',', ' ') }}
                            @if($l->est_entierement_livree)
                                <i class="fas fa-check-circle text-success ms-1"></i>
                            @elseif($l->quantite_livree > 0)
                                <span class="badge bg-warning text-dark ms-1">Partielle</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format((float) $l->prix_unitaire, 0, ',', ' ') }}</td>
                        <td class="text-end fw-semibold">{{ number_format((float) $l->montant, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="5" class="text-end">TOTAL HT</th>
                    <th class="text-end fs-6">{{ number_format((float) $commande->montant_ht, 0, ',', ' ') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ═════════ Devis fournisseur ═════════ --}}
<div class="card data-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-file-signature me-2"></i> Devis reçus ({{ $commande->devis()->count() }})</h6>
        <a href="{{ route('appro.devis-fournisseur.create', ['commande_id' => $commande->id]) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus me-1"></i> Enregistrer un devis
        </a>
    </div>
    <div class="table-responsive"><table class="table table-sm mb-0">
        <thead class="table-light"><tr>
            <th>N°</th><th>Objet</th><th>Reçu le</th>
            <th class="text-end">Montant HT</th>
            <th class="text-end">Montant TTC</th>
            <th>Délai</th>
            <th>Statut</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
            @forelse($commande->devis()->with(['fournisseur', 'facture'])->get() as $dv)
                <tr class="{{ $dv->statut === \App\Models\DevisFournisseur::STATUT_SELECTIONNE ? 'table-success' : ($dv->statut === \App\Models\DevisFournisseur::STATUT_REJETE ? 'table-secondary' : '') }}">
                    <td><code>{{ $dv->numero }}</code></td>
                    <td><small>{{ $commande->objet ?: '—' }}</small></td>
                    <td><small>{{ $dv->date_reception?->format('d/m/Y') }}</small></td>
                    <td class="text-end">{{ number_format((float) $dv->montant_ht, 0, ',', ' ') }}</td>
                    <td class="text-end fw-semibold">{{ number_format((float) $dv->montant_ttc, 0, ',', ' ') }}</td>
                    <td><small>{{ $dv->delai_livraison_jours !== null ? $dv->delai_livraison_jours . ' j' : '—' }}</small></td>
                    <td><span class="badge bg-{{ $dv->statut_couleur }}">{{ $dv->statut_libelle }}</span></td>
                    <td class="text-end"><a href="{{ route('appro.devis-fournisseur.show', $dv) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-3">Aucun devis enregistré. Ajoutez les devis reçus des fournisseurs consultés.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@if($commande->livraisons()->exists())
<div class="card data-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-truck-loading me-2"></i> Livraisons reçues ({{ $commande->livraisons()->count() }})</h6>
        @if($commande->peutRecevoirLivraison())
            <a href="{{ route('appro.livraisons-fournisseur.create', ['commande_id' => $commande->id]) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus me-1"></i> Nouvelle livraison
            </a>
        @endif
    </div>
    <div class="table-responsive"><table class="table table-sm mb-0">
        <thead class="table-light"><tr>
            <th>N° livraison</th><th>Date</th><th>Type</th><th>Réception</th>
            <th>BL fournisseur</th><th class="text-center">Lignes</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
            @foreach($commande->livraisons()->with(['emplacementReception', 'lignes'])->get() as $liv)
                <tr>
                    <td><code>{{ $liv->numero }}</code></td>
                    <td><small>{{ $liv->date_livraison?->format('d/m/Y') }}</small></td>
                    <td><span class="badge bg-{{ $liv->type_couleur }}">{{ \App\Models\LivraisonFournisseur::TYPES[$liv->type] ?? $liv->type }}</span></td>
                    <td><small>{{ $liv->emplacementReception?->libelle ?? '—' }}</small></td>
                    <td><small>{{ $liv->bon_livraison_ref ?? '—' }}</small></td>
                    <td class="text-center">{{ $liv->lignes->count() }}</td>
                    <td class="text-end">
                        <a href="{{ route('appro.livraisons-fournisseur.show', $liv) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table></div>
</div>
@endif

@if($commande->mouvements->isNotEmpty())
<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-box me-2"></i> Mouvements de stock générés</h6></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Produit</th><th>Type</th><th class="text-end">Quantité</th><th class="text-end">Stock après</th></tr>
            </thead>
            <tbody>
                @foreach($commande->mouvements as $m)
                    <tr>
                        <td><small>{{ $m->created_at?->format('d/m/Y H:i') }}</small></td>
                        <td>{{ $m->produit?->designation }}</td>
                        <td><span class="badge bg-success">{{ \App\Models\ProduitMouvement::TYPES[$m->type] ?? $m->type }}</span></td>
                        <td class="text-end">+{{ number_format((float) $m->quantite, 2, ',', ' ') }}</td>
                        <td class="text-end fw-semibold">{{ number_format((float) $m->stock_apres, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ═════════ MODALES ═════════ --}}
@can('update:commande')
@if($commande->peutEtreAnnulee())
<div class="modal fade" id="modal-annuler-cmd" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('appro.commandes.annuler', $commande) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-ban text-warning me-2"></i> Annuler la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    La commande <strong>{{ $commande->numero_commande }}</strong> sera annulée. Cette action est irréversible.
                </div>
                <label class="form-label">Motif d'annulation <span class="text-danger">*</span></label>
                <textarea name="motif_annulation" class="form-control" rows="4" required minlength="10" maxlength="500"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button class="btn btn-warning"><i class="fas fa-ban me-1"></i> Confirmer l'annulation</button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan
@endsection
