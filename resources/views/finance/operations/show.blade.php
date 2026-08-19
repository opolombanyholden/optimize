@extends('layouts.app')
@section('title', $operation->numero)
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.operations.index') }}">Opérations</a></li>
        <li class="breadcrumb-item active">{{ $operation->numero }}</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $operation->numero }}</h1>
        <p class="text-muted mb-0">
            {{ $operation->type_libelle }} ·
            <span class="badge bg-{{ $operation->statut_couleur }}">{{ $operation->statut_libelle }}</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.operations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>

        @can('update:operation')
            @if($operation->est_modifiable)
                <a href="{{ route('finance.operations.edit', $operation) }}" class="btn btn-secondary"><i class="fas fa-pen me-1"></i> Éditer</a>
            @endif
            @if($operation->est_soumissible)
                <form action="{{ route('finance.operations.soumettre', $operation) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-info"><i class="fas fa-paper-plane me-1"></i> Soumettre</button>
                </form>
            @endif
        @endcan

        @can('validate:operation')
            @if($operation->est_approuvable)
                <form action="{{ route('finance.operations.approuver', $operation) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-warning"><i class="fas fa-check me-1"></i> Approuver</button>
                </form>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#mRejet"><i class="fas fa-times me-1"></i> Rejeter</button>
            @endif
            @if($operation->est_executable)
                <form action="{{ route('finance.operations.executer', $operation) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Exécuter ? L\'engagement sera passé et les écritures comptables générées.');">@csrf
                    <button class="btn btn-success"><i class="fas fa-play me-1"></i> Exécuter</button>
                </form>
            @endif
            @if($operation->statut === 3)
                <form action="{{ route('finance.operations.annuler', $operation) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Annuler ? L\'engagement sera libéré et les écritures supprimées.');">@csrf
                    <button class="btn btn-outline-warning"><i class="fas fa-undo me-1"></i> Annuler</button>
                </form>
            @endif
        @endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card data-card">
            <div class="card-header"><strong>Détails</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Date opération</dt><dd class="col-sm-8">{{ $operation->date_operation?->format('d/m/Y') }}</dd>
                    <dt class="col-sm-4 text-muted">Objet</dt><dd class="col-sm-8">{{ $operation->objet }}</dd>
                    @if($operation->budgetLigne)
                        <dt class="col-sm-4 text-muted">Ligne budgétaire</dt>
                        <dd class="col-sm-8">
                            <strong>{{ $operation->budgetLigne->id_budgetligne }}</strong>
                            <br><small class="text-muted">
                                Budget total : {{ number_format($operation->budgetLigne->budget_total, 0, ',', ' ') }} ·
                                Engagé : {{ number_format((float) $operation->budgetLigne->engagement, 0, ',', ' ') }}
                            </small>
                        </dd>
                    @endif
                    @if($tiers)
                        <dt class="col-sm-4 text-muted">Tiers</dt>
                        <dd class="col-sm-8">{{ $tiers->nom ?? $tiers->raison_sociale }} <small class="text-muted">({{ $operation->tiers_type }})</small></dd>
                    @endif
                    @if($operation->facture)
                        <dt class="col-sm-4 text-muted">Facture</dt>
                        <dd class="col-sm-8"><a href="{{ route('finance.factures.show', $operation->facture) }}">{{ $operation->facture->numero }}</a></dd>
                    @endif
                    @if($operation->mode_reglement)
                        <dt class="col-sm-4 text-muted">Règlement</dt>
                        <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $operation->mode_reglement)) }}
                            @if($operation->reference_reglement) — <code>{{ $operation->reference_reglement }}</code> @endif
                        </dd>
                    @endif
                </dl>
            </div>
        </div>

        @if($operation->commentaire)
            <div class="alert alert-light border mt-3"><strong>Commentaire :</strong> {{ $operation->commentaire }}</div>
        @endif
        @if($operation->motif_rejet)
            <div class="alert alert-danger mt-3"><strong>Motif de rejet :</strong> {{ $operation->motif_rejet }}</div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card data-card">
            <div class="card-header bg-primary text-white"><strong>Montant</strong></div>
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ number_format((float) $operation->montant, 0, ',', ' ') }}</div>
                <small class="text-muted">XAF</small>
            </div>
        </div>

        <div class="card data-card mt-3">
            <div class="card-header"><strong><i class="fas fa-route me-1"></i> Workflow</strong></div>
            <div class="card-body">
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><i class="fas fa-circle text-secondary me-1"></i> <strong>Créée</strong> par {{ $operation->createur?->name ?? '—' }}<br><small class="text-muted">{{ $operation->created_at?->translatedFormat('d M H:i') }}</small></li>
                    @if($operation->soumis_at)<li class="mb-2"><i class="fas fa-paper-plane text-info me-1"></i> <strong>Soumise</strong> par {{ $operation->soumetteur?->name ?? '—' }}<br><small class="text-muted">{{ $operation->soumis_at->translatedFormat('d M H:i') }}</small></li>@endif
                    @if($operation->approuve_at)
                        @if($operation->statut === 4)
                            <li class="mb-2"><i class="fas fa-times text-danger me-1"></i> <strong>Rejetée</strong> par {{ $operation->approbateur?->name ?? '—' }}<br><small class="text-muted">{{ $operation->approuve_at->translatedFormat('d M H:i') }}</small></li>
                        @else
                            <li class="mb-2"><i class="fas fa-check text-warning me-1"></i> <strong>Approuvée</strong> par {{ $operation->approbateur?->name ?? '—' }}<br><small class="text-muted">{{ $operation->approuve_at->translatedFormat('d M H:i') }}</small></li>
                        @endif
                    @endif
                    @if($operation->execute_at)<li class="mb-2"><i class="fas fa-play text-success me-1"></i> <strong>Exécutée</strong> par {{ $operation->executeur?->name ?? '—' }}<br><small class="text-muted">{{ $operation->execute_at->translatedFormat('d M H:i') }}</small></li>@endif
                </ul>
            </div>
        </div>
    </div>
</div>

@if($operation->est_rejetable)
    <div class="modal fade" id="mRejet" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('finance.operations.rejeter', $operation) }}" method="POST" class="modal-content">@csrf
                <div class="modal-header"><h5 class="modal-title">Rejeter l'opération</h5></div>
                <div class="modal-body">
                    <label class="form-label">Motif <span class="text-danger">*</span></label>
                    <textarea name="motif_rejet" class="form-control" rows="3" required maxlength="500"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-danger">Rejeter</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
