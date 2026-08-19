@extends('layouts.app')

@section('title', 'D&eacute;tail compte')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.comptes.index') }}">Comptes</a></li>
        <li class="breadcrumb-item active">{{ $compte->numero_compte }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ $compte->numero_compte }} - {{ $compte->libelle }}</h1>
        <p class="text-muted mb-0">D&eacute;tail du compte comptable.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.comptes.edit', $compte) }}" class="btn btn-outline-secondary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <a href="{{ route('finance.comptes.index') }}" class="btn btn-outline-primary">
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
                    <i class="fas fa-hashtag"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">N&deg; Compte</div>
                    <div class="fw-bold fs-5">{{ $compte->numero_compte }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-success-soft">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Solde initial</div>
                    <div class="fw-bold fs-5">{{ number_format($compte->solde_initial ?? 0, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-info-soft">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Type</div>
                    <div class="fw-bold">{{ ucfirst($compte->type_compte ?? '-') }}</div>
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
                    <span class="text-muted small d-block">N&deg; Compte</span>
                    <strong>{{ $compte->numero_compte }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Libell&eacute;</span>
                    <strong>{{ $compte->libelle }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Type de compte</span>
                    <strong>{{ ucfirst($compte->type_compte ?? '-') }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Solde initial</span>
                    <strong>{{ number_format($compte->solde_initial ?? 0, 0, ',', ' ') }} F</strong>
                </div>
            </div>
            @if($compte->description)
            <div class="col-12">
                <div class="mb-3">
                    <span class="text-muted small d-block">Description</span>
                    <p class="mb-0">{{ $compte->description }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
