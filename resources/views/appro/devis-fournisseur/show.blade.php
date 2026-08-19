@extends('layouts.app')
@section('title', $devis->numero)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item"><a href="{{ route('appro.devis-fournisseur.index') }}">Devis fournisseur</a></li>
    <li class="breadcrumb-item active">{{ $devis->numero }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">
            <code>{{ $devis->numero }}</code>
            <span class="badge bg-{{ $devis->statut_couleur }} ms-2">{{ $devis->statut_libelle }}</span>
        </h1>
        <p class="text-muted mb-0">
            Commande : <a href="{{ route('appro.commandes.show', $devis->commande) }}"><code class="small">{{ $devis->commande?->numero_commande }}</code></a>
            · Fournisseur : <strong>{{ $devis->fournisseur?->raison_sociale ?? $devis->fournisseur?->nom ?? '—' }}</strong>
            · Reçu le : {{ $devis->date_reception?->format('d/m/Y') }}
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($devis->peutEtreSelectionne())
            <a href="{{ route('appro.devis-fournisseur.edit', $devis) }}" class="btn btn-outline-secondary"><i class="fas fa-pen me-1"></i> Modifier</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-selection"><i class="fas fa-check me-1"></i> Sélectionner</button>
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal-rejet"><i class="fas fa-times me-1"></i> Rejeter</button>
        @endif
        <a href="{{ route('appro.commandes.show', $devis->commande) }}" class="btn btn-outline-secondary"><i class="fas fa-file-invoice me-1"></i> Commande</a>
    </div>
</div>

@if($devis->statut === \App\Models\DevisFournisseur::STATUT_SELECTIONNE)
    <div class="alert alert-success">
        <strong><i class="fas fa-check-circle me-1"></i> Devis sélectionné</strong>
        le {{ $devis->decide_at?->format('d/m/Y H:i') }} par {{ $devis->decideur?->name }}<br>
        <em>Motivation :</em> {{ $devis->motivation }}
        @if($devis->facture)
            <hr class="my-2">
            <i class="fas fa-file-invoice-dollar me-1"></i> Facture créée : <a href="{{ route('finance.factures.show', $devis->facture) }}"><code>{{ $devis->facture->numero }}</code></a>
        @endif
    </div>
@elseif($devis->statut === \App\Models\DevisFournisseur::STATUT_REJETE)
    <div class="alert alert-warning">
        <strong>Devis rejeté</strong> le {{ $devis->decide_at?->format('d/m/Y H:i') }} — {{ $devis->motivation }}
    </div>
@endif

<div class="row g-3">
    <div class="col-md-8">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-coins me-2"></i> Montants</h6></div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Montant HT</small>
                        <div class="h4 mb-0">{{ number_format((float) $devis->montant_ht, 2, ',', ' ') }}</div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">TVA</small>
                        <div class="h4 mb-0 text-muted">{{ number_format((float) $devis->montant_tva, 2, ',', ' ') }}</div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Montant TTC</small>
                        <div class="h4 mb-0 text-primary">{{ number_format((float) $devis->montant_ttc, 2, ',', ' ') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($devis->conditions)
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0">Conditions</h6></div>
                <div class="card-body"><p class="mb-0">{!! nl2br(e($devis->conditions)) !!}</p></div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informations</h6></div>
            <div class="card-body"><dl class="row mb-0 small">
                <dt class="col-6">Validité</dt><dd class="col-6 text-end">{{ $devis->date_validite?->format('d/m/Y') ?? '—' }}</dd>
                <dt class="col-6">Délai livraison</dt><dd class="col-6 text-end">{{ $devis->delai_livraison_jours !== null ? $devis->delai_livraison_jours . ' j' : '—' }}</dd>
                <dt class="col-6">Mode règlement</dt><dd class="col-6 text-end">{{ \App\Models\CommandeFournisseur::MODES_REGLEMENT[$devis->mode_reglement] ?? '—' }}</dd>
            </dl></div>
        </div>

        @if($devis->piecesJointes->isNotEmpty())
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Pièces jointes</h6></div>
                <div class="card-body">
                    @include('mg._partials.pieces-jointes', ['pieces' => $devis->piecesJointes])
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modales --}}
@if($devis->peutEtreSelectionne())
<div class="modal fade" id="modal-selection" tabindex="-1"><div class="modal-dialog">
    <form action="{{ route('appro.devis-fournisseur.selectionner', $devis) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-check text-success me-2"></i> Sélectionner ce devis</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="alert alert-info small">Les autres devis reçus pour la même commande seront automatiquement rejetés.</div>
            <label class="form-label">Motivation de la sélection <span class="text-danger">*</span></label>
            <textarea name="motivation" class="form-control" rows="4" required minlength="5" maxlength="2000" placeholder="Justifier le choix : meilleur rapport qualité/prix, délai le plus court, seul fournisseur agréé…"></textarea>
            <div class="form-check mt-3">
                <input type="hidden" name="creer_facture" value="0">
                <input type="checkbox" class="form-check-input" name="creer_facture" id="creer_facture" value="1" checked>
                <label for="creer_facture" class="form-check-label">Créer immédiatement une facture pré-remplie (à ordonnancer et payer ensuite)</label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-success"><i class="fas fa-check me-1"></i> Sélectionner</button>
        </div>
    </form>
</div></div>

<div class="modal fade" id="modal-rejet" tabindex="-1"><div class="modal-dialog">
    <form action="{{ route('appro.devis-fournisseur.rejeter', $devis) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-times text-danger me-2"></i> Rejeter ce devis</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="form-label">Motivation du rejet <span class="text-danger">*</span></label>
            <textarea name="motivation" class="form-control" rows="4" required minlength="3" maxlength="2000"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-danger">Rejeter</button>
        </div>
    </form>
</div></div>
@endif
@endsection
