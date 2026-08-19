@extends('layouts.app')

@section('title', 'D&eacute;tail &eacute;criture')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.grand-livre.index') }}">Grand Livre</a></li>
        <li class="breadcrumb-item active">{{ $ecriture->reference }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>&Eacute;criture {{ $ecriture->reference }}</h1>
        <p class="text-muted mb-0">D&eacute;tail de l'&eacute;criture comptable.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.grand-livre.edit', $ecriture) }}" class="btn btn-outline-secondary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <a href="{{ route('finance.grand-livre.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-danger-soft">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">D&eacute;bit</div>
                    <div class="fw-bold fs-5">{{ number_format($ecriture->montant_debit ?? 0, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-success-soft">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Cr&eacute;dit</div>
                    <div class="fw-bold fs-5">{{ number_format($ecriture->montant_credit ?? 0, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-primary-soft">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Solde</div>
                    @php $solde = ($ecriture->montant_debit ?? 0) - ($ecriture->montant_credit ?? 0); @endphp
                    <div class="fw-bold fs-5 {{ $solde >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($solde, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-info-soft">
                    <i class="fas fa-calendar"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Date</div>
                    <div class="fw-bold">{{ $ecriture->date_ecriture ? \Carbon\Carbon::parse($ecriture->date_ecriture)->format('d/m/Y') : '-' }}</div>
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
                    <span class="text-muted small d-block">R&eacute;f&eacute;rence</span>
                    <strong>{{ $ecriture->reference ?? '-' }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Exercice</span>
                    <strong>{{ $ecriture->exercice->libelle ?? '-' }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Libell&eacute;</span>
                    <strong>{{ $ecriture->libelle ?? '-' }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Date de l'&eacute;criture</span>
                    <strong>{{ $ecriture->date_ecriture ? \Carbon\Carbon::parse($ecriture->date_ecriture)->format('d/m/Y') : '-' }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Montant d&eacute;bit</span>
                    <strong>{{ number_format($ecriture->montant_debit ?? 0, 0, ',', ' ') }} F</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Montant cr&eacute;dit</span>
                    <strong>{{ number_format($ecriture->montant_credit ?? 0, 0, ',', ' ') }} F</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
