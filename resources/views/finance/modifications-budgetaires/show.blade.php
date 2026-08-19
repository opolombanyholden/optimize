@extends('layouts.app')

@section('title', 'Modification budgétaire #' . $mod->id)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.modifications-budgetaires.index') }}">Modifications budgétaires</a></li>
        <li class="breadcrumb-item active">#{{ $mod->id }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $mod->objetmodification }}</h1>
        <p class="text-muted mb-0">
            {{ $mod->type_libelle }} ·
            <span class="badge bg-{{ $mod->statut_couleur }}">{{ $mod->statut_libelle }}</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.modifications-budgetaires.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>

        @can('update:budget')
            @if($mod->est_modifiable)
                <a href="{{ route('finance.modifications-budgetaires.edit', $mod) }}" class="btn btn-secondary"><i class="fas fa-pen me-1"></i> Éditer</a>
            @endif
            @if($mod->est_soumissible)
                <form action="{{ route('finance.modifications-budgetaires.soumettre', $mod) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-info"><i class="fas fa-paper-plane me-1"></i> Soumettre</button>
                </form>
            @endif
        @endcan

        @can('validate:budget')
            @if($mod->est_approuvable)
                <form action="{{ route('finance.modifications-budgetaires.approuver', $mod) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-warning"><i class="fas fa-check me-1"></i> Approuver</button>
                </form>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRejet">
                    <i class="fas fa-times me-1"></i> Rejeter
                </button>
            @endif
            @if($mod->est_applicable)
                <form action="{{ route('finance.modifications-budgetaires.appliquer', $mod) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Appliquer la modification ? Les soldes des lignes seront ajustés.');">
                    @csrf
                    <button class="btn btn-success"><i class="fas fa-play me-1"></i> Appliquer</button>
                </form>
            @endif
            @if($mod->statut === 3)
                <form action="{{ route('finance.modifications-budgetaires.annuler', $mod) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Annuler l\'application ? Les soldes seront restaurés.');">
                    @csrf
                    <button class="btn btn-outline-warning"><i class="fas fa-undo me-1"></i> Annuler l\'application</button>
                </form>
            @endif
        @endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card data-card">
            <div class="card-header"><strong><i class="fas fa-info-circle me-1"></i> Détails</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Exercice</dt>
                    <dd class="col-sm-8">{{ $mod->exercice?->libelle ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Type</dt>
                    <dd class="col-sm-8">{{ $mod->type_libelle }}</dd>

                    <dt class="col-sm-4 text-muted">Montant</dt>
                    <dd class="col-sm-8"><strong>{{ number_format((float) $mod->montant_modification, 0, ',', ' ') }} XAF</strong></dd>

                    @if($mod->ligneSource)
                        <dt class="col-sm-4 text-danger">
                            <i class="fas fa-arrow-up-from-bracket me-1"></i> Ligne émettrice
                        </dt>
                        <dd class="col-sm-8">
                            @if($mod->ligneSource->ligne?->titre?->imputation)
                                <span class="text-muted small">[{{ $mod->ligneSource->ligne->titre->imputation }}]</span>
                            @endif
                            <strong>{{ $mod->ligneSource->ligne?->libelle ?? $mod->ligneSource->commentaire ?? '—' }}</strong>
                            <br><code class="small">{{ $mod->ligneSource->id_budgetligne }}</code>
                            <br><small class="text-muted">
                                Budget : {{ number_format($mod->ligneSource->budget_total, 0, ',', ' ') }} XAF ·
                                Solde dispo : {{ number_format($mod->ligneSource->solde_disponible, 0, ',', ' ') }} XAF
                            </small>
                        </dd>
                    @endif

                    <dt class="col-sm-4 text-success">
                        <i class="fas fa-arrow-down-to-bracket me-1"></i> Ligne réceptrice
                    </dt>
                    <dd class="col-sm-8">
                        @if($mod->ligneDestination)
                            @if($mod->ligneDestination->ligne?->titre?->imputation)
                                <span class="text-muted small">[{{ $mod->ligneDestination->ligne->titre->imputation }}]</span>
                            @endif
                            <strong>{{ $mod->ligneDestination->ligne?->libelle ?? $mod->ligneDestination->commentaire ?? '—' }}</strong>
                            <br><code class="small">{{ $mod->ligneDestination->id_budgetligne }}</code>
                            <br><small class="text-muted">Budget : {{ number_format($mod->ligneDestination->budget_total, 0, ',', ' ') }} XAF</small>
                        @else
                            <em class="text-muted">—</em>
                        @endif
                    </dd>

                    @if($mod->commentaire)
                        <dt class="col-sm-4 text-muted">Commentaire</dt>
                        <dd class="col-sm-8">{{ $mod->commentaire }}</dd>
                    @endif

                    @if($mod->motif_rejet)
                        <dt class="col-sm-4 text-danger">Motif du rejet</dt>
                        <dd class="col-sm-8 text-danger">{{ $mod->motif_rejet }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card data-card">
            <div class="card-header"><strong><i class="fas fa-route me-1"></i> Workflow</strong></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-circle text-secondary me-1"></i>
                        <strong>Créée</strong> par {{ $mod->user?->name ?? '—' }}
                        <br><small class="text-muted">{{ $mod->created_at?->translatedFormat('d M Y H:i') }}</small>
                    </li>
                    @if($mod->soumis_at)
                        <li class="mb-2">
                            <i class="fas fa-paper-plane text-info me-1"></i>
                            <strong>Soumise</strong> par {{ $mod->soumetteur?->name ?? '—' }}
                            <br><small class="text-muted">{{ $mod->soumis_at->translatedFormat('d M Y H:i') }}</small>
                        </li>
                    @endif
                    @if($mod->approuve_at)
                        @if($mod->statut === 4)
                            <li class="mb-2">
                                <i class="fas fa-times text-danger me-1"></i>
                                <strong>Rejetée</strong> par {{ $mod->approbateur?->name ?? '—' }}
                                <br><small class="text-muted">{{ $mod->approuve_at->translatedFormat('d M Y H:i') }}</small>
                            </li>
                        @else
                            <li class="mb-2">
                                <i class="fas fa-check text-warning me-1"></i>
                                <strong>Approuvée</strong> par {{ $mod->approbateur?->name ?? '—' }}
                                <br><small class="text-muted">{{ $mod->approuve_at->translatedFormat('d M Y H:i') }}</small>
                            </li>
                        @endif
                    @endif
                    @if($mod->applique_at)
                        <li class="mb-2">
                            <i class="fas fa-play text-success me-1"></i>
                            <strong>Appliquée</strong> par {{ $mod->applicateur?->name ?? '—' }}
                            <br><small class="text-muted">{{ $mod->applique_at->translatedFormat('d M Y H:i') }}</small>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Modal rejet --}}
@if($mod->est_rejetable)
<div class="modal fade" id="modalRejet" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('finance.modifications-budgetaires.rejeter', $mod) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Rejeter la modification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-0">
                    <label class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                    <textarea name="motif_rejet" rows="3" class="form-control" required maxlength="500"></textarea>
                </div>
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
