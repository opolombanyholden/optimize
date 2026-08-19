@extends('layouts.app')

@section('title', 'D&eacute;tail ligne budg&eacute;taire')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.budgets.index') }}">Lignes budg&eacute;taires</a></li>
        <li class="breadcrumb-item active">D&eacute;tail</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Ligne budg&eacute;taire</h1>
        <p class="text-muted mb-0">D&eacute;tail de la ligne de budget.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.budgets.edit', $budget) }}" class="btn btn-outline-secondary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <a href="{{ route('finance.budgets.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-primary-soft">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Montant initial</div>
                    <div class="fw-bold fs-5">{{ number_format($budget->montant_initial ?? 0, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-success-soft">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Montant r&eacute;vis&eacute;</div>
                    <div class="fw-bold fs-5">{{ number_format($budget->montant_revise ?? 0, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-info-soft">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Exercice</div>
                    <div class="fw-bold">{{ $budget->exercice->libelle ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-warning-soft">
                    <i class="fas fa-tag"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Statut</div>
                    <div class="fw-bold">
                        @if($budget->statut == 1)
                            <span class="badge badge-status badge-actif">Actif</span>
                        @elseif($budget->statut == 2)
                            <span class="badge badge-status badge-inactif">Cl&ocirc;tur&eacute;</span>
                        @else
                            <span class="badge badge-status badge-brouillon">Brouillon</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- D&eacute;tails --}}
<div class="card data-card">
    <div class="card-header">
        <h5><i class="fas fa-info-circle me-2 text-muted"></i>Informations d&eacute;taill&eacute;es</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Exercice</span>
                    <strong>{{ $budget->exercice->libelle ?? '-' }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Ligne</span>
                    <strong>{{ $budget->ligne->libelle ?? $budget->ligne_id }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Montant initial</span>
                    <strong>{{ number_format($budget->montant_initial ?? 0, 0, ',', ' ') }} F</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Montant r&eacute;vis&eacute;</span>
                    <strong>{{ number_format($budget->montant_revise ?? 0, 0, ',', ' ') }} F</strong>
                </div>
            </div>
            @if($budget->commentaire)
            <div class="col-12">
                <div class="mb-3">
                    <span class="text-muted small d-block">Commentaire</span>
                    <p class="mb-0">{{ $budget->commentaire }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
