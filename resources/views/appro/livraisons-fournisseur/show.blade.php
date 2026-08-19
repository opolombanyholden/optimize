@extends('layouts.app')
@section('title', $livraison->numero)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item"><a href="{{ route('appro.livraisons-fournisseur.index') }}">Livraisons fournisseur</a></li>
    <li class="breadcrumb-item active">{{ $livraison->numero }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1">
            <code>{{ $livraison->numero }}</code>
            <span class="badge bg-{{ $livraison->type_couleur }} ms-2">{{ \App\Models\LivraisonFournisseur::TYPES[$livraison->type] ?? $livraison->type }}</span>
        </h1>
        <p class="text-muted mb-0">
            Reçue le <strong>{{ $livraison->date_livraison?->format('d/m/Y') }}</strong>
            · par {{ $livraison->receptionneur?->name ?? '—' }}
            @if($livraison->emplacementReception) · Point de réception : <strong>{{ $livraison->emplacementReception->chemin }}</strong>@endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('appro.commandes.show', $livraison->commande) }}" class="btn btn-outline-secondary">
            <i class="fas fa-file-invoice me-1"></i> Commande {{ $livraison->commande?->numero_commande }}
        </a>
        <a href="{{ route('appro.livraisons-fournisseur.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-boxes-stacked me-2"></i> Articles reçus ({{ $livraison->lignes->count() }})</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead class="table-light"><tr>
                    <th>Article</th><th>Emplacement</th><th class="text-end">Quantité</th><th>Observation</th>
                </tr></thead>
                <tbody>
                    @foreach($livraison->lignes as $l)
                        <tr>
                            <td>
                                <strong>{{ $l->commandeLigne?->designation ?? '—' }}</strong>
                                @if($l->produit)<br><code class="small text-muted">{{ $l->produit->code }}</code>@endif
                            </td>
                            <td><small>{{ $l->emplacement?->chemin ?? $livraison->emplacementReception?->chemin ?? '—' }}</small></td>
                            <td class="text-end fw-semibold">{{ number_format((float) $l->quantite, 3, ',', ' ') }}</td>
                            <td><small>{{ $l->observation ?: '—' }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light"><tr>
                    <th colspan="2" class="text-end">TOTAL lignes</th>
                    <th class="text-end">{{ number_format($livraison->lignes->sum('quantite'), 3, ',', ' ') }}</th>
                    <th></th>
                </tr></tfoot>
            </table></div>
        </div>

        @if($livraison->commentaire)
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-comment me-2"></i> Commentaire</h6></div>
                <div class="card-body"><p class="mb-0">{!! nl2br(e($livraison->commentaire)) !!}</p></div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informations</h6></div>
            <div class="card-body"><dl class="row mb-0 small">
                <dt class="col-6">N° BL fournisseur</dt><dd class="col-6 text-end">{{ $livraison->bon_livraison_ref ?? '—' }}</dd>
                <dt class="col-6">Type</dt><dd class="col-6 text-end">{{ \App\Models\LivraisonFournisseur::TYPES[$livraison->type] ?? '—' }}</dd>
                <dt class="col-6">Commande</dt><dd class="col-6 text-end"><a href="{{ route('appro.commandes.show', $livraison->commande) }}">{{ $livraison->commande?->numero_commande }}</a></dd>
                <dt class="col-6">Fournisseur</dt><dd class="col-6 text-end">{{ $livraison->commande?->fournisseur?->raison_sociale ?? $livraison->commande?->fournisseur?->nom ?? '—' }}</dd>
                <dt class="col-6">Enregistrée le</dt><dd class="col-6 text-end">{{ $livraison->created_at?->format('d/m/Y H:i') }}</dd>
            </dl></div>
        </div>

        @if($livraison->piecesJointes->isNotEmpty())
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Bons de livraison / pièces jointes</h6></div>
                <div class="card-body">
                    @include('mg._partials.pieces-jointes', ['pieces' => $livraison->piecesJointes])
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
