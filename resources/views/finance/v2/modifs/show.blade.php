@extends('layouts.app')
@section('title', 'Modification #' . $modif->id)
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $modif->comment }}</h1>
        <p class="text-muted mb-0"><span class="badge bg-{{ $modif->status_couleur }}">{{ $modif->status_libelle }}</span></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.v2.modifs.index') }}" class="btn btn-outline-secondary">Retour</a>
        @if($modif->est_soumissible)
            <form action="{{ route('finance.v2.modifs.soumettre', $modif) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-info"><i class="fas fa-paper-plane me-1"></i> Soumettre</button>
            </form>
        @endif
        @if($modif->est_approuvable)
            <form action="{{ route('finance.v2.modifs.approuver', $modif) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-warning"><i class="fas fa-check me-1"></i> Approuver</button>
            </form>
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#mR"><i class="fas fa-times me-1"></i> Rejeter</button>
        @endif
        @if($modif->est_applicable)
            <form action="{{ route('finance.v2.modifs.appliquer', $modif) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Appliquer ? Le montant sera effectivement transféré.');">@csrf
                <button class="btn btn-success"><i class="fas fa-play me-1"></i> Appliquer</button>
            </form>
        @endif
        @if($modif->status === 3)
            <form action="{{ route('finance.v2.modifs.annuler', $modif) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Annuler ? Les soldes seront restaurés.');">@csrf
                <button class="btn btn-outline-warning"><i class="fas fa-undo me-1"></i> Annuler application</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card data-card">
            <div class="card-header"><strong>Détails</strong></div>
            <div class="card-body"><dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Date</dt><dd class="col-sm-8">{{ $modif->date?->format('d/m/Y') ?? '—' }}</dd>
                <dt class="col-sm-4 text-muted">Montant</dt>
                <dd class="col-sm-8"><strong>{{ number_format((float) $modif->montant, 0, ',', ' ') }} XAF</strong></dd>

                @if($modif->budgetEmission)
                <dt class="col-sm-4 text-danger">Émission (débit)</dt>
                <dd class="col-sm-8">
                    <strong>{{ $modif->budgetEmission->source?->code }}</strong>
                    / {{ $modif->budgetEmission->ligne?->code }} — {{ $modif->budgetEmission->ligne?->label ?? $modif->budgetEmission->ligne?->libelle }}
                    <br><small class="text-muted">Solde actuel : {{ number_format((float) $modif->budgetEmission->montant, 0, ',', ' ') }}</small>
                </dd>
                @endif

                <dt class="col-sm-4 text-success">Réception (crédit)</dt>
                <dd class="col-sm-8">
                    <strong>{{ $modif->budgetReception?->source?->code }}</strong>
                    / {{ $modif->budgetReception?->ligne?->code }} — {{ $modif->budgetReception?->ligne?->label ?? $modif->budgetReception?->ligne?->libelle }}
                    <br><small class="text-muted">Solde actuel : {{ number_format((float) ($modif->budgetReception?->montant ?? 0), 0, ',', ' ') }}</small>
                </dd>

                @if($modif->motif_rejet)
                    <dt class="col-sm-4 text-danger">Motif rejet</dt><dd class="col-sm-8 text-danger">{{ $modif->motif_rejet }}</dd>
                @endif
            </dl></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card data-card">
            <div class="card-header"><strong><i class="fas fa-route me-1"></i> Workflow</strong></div>
            <div class="card-body"><ul class="list-unstyled small mb-0">
                @if($modif->soumis_at)
                    <li class="mb-2"><i class="fas fa-paper-plane text-info me-1"></i> <strong>Soumise</strong> par {{ $modif->soumetteur?->name ?? '—' }}
                        <br><small class="text-muted">{{ $modif->soumis_at->translatedFormat('d M H:i') }}</small></li>
                @endif
                @if($modif->approuve_at)
                    <li class="mb-2">
                        @if($modif->status === 4)
                            <i class="fas fa-times text-danger me-1"></i> <strong>Rejetée</strong>
                        @else
                            <i class="fas fa-check text-warning me-1"></i> <strong>Approuvée</strong>
                        @endif
                        par {{ $modif->approbateur?->name ?? '—' }}
                        <br><small class="text-muted">{{ $modif->approuve_at->translatedFormat('d M H:i') }}</small>
                    </li>
                @endif
                @if($modif->applique_at)
                    <li class="mb-2"><i class="fas fa-play text-success me-1"></i> <strong>Appliquée</strong> par {{ $modif->applicateur?->name ?? '—' }}
                        <br><small class="text-muted">{{ $modif->applique_at->translatedFormat('d M H:i') }}</small></li>
                @endif
            </ul></div>
        </div>
    </div>
</div>

@if($modif->est_rejetable)
<div class="modal fade" id="mR" tabindex="-1"><div class="modal-dialog">
    <form action="{{ route('finance.v2.modifs.rejeter', $modif) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title">Rejeter</h5></div>
        <div class="modal-body">
            <label class="form-label">Motif <span class="text-danger">*</span></label>
            <textarea name="motif_rejet" class="form-control" rows="3" required maxlength="500"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-danger">Rejeter</button>
        </div>
    </form>
</div></div>
@endif
@endsection
