@extends('layouts.app')

@section('title', 'Masse salariale - ' . $global->periode_libelle)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.payements-globals.index') }}">Masse salariale</a></li>
        <li class="breadcrumb-item active">{{ $global->periode_libelle }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-chart-pie me-2 text-muted"></i>{{ $global->periode_libelle }}</h1>
        <p class="text-muted mb-0">
            @if($global->statut == 0)
                <span class="badge badge-status badge-en-attente">Brouillon</span>
            @elseif($global->statut == 1)
                <span class="badge badge-status badge-valide">Cloture</span>
                @if($global->cloturePar)
                    <span class="ms-2">par <strong>{{ $global->cloturePar->name }}</strong> le {{ $global->cloturee_at?->format('d/m/Y H:i') }}</span>
                @endif
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        @if($global->statut == 0)
            <form action="{{ route('rh.payements-globals.recalculer') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="annee" value="{{ $global->annee }}">
                <input type="hidden" name="mois" value="{{ $global->mois }}">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i>Recalculer
                </button>
            </form>
            <form action="{{ route('rh.payements-globals.cloturer', $global) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la cloture de cette periode ? Cette action est irreversible.');">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-lock me-1"></i>Cloturer
                </button>
            </form>
        @endif
        <a href="{{ route('rh.payements-globals.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

{{-- KPI cards --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card data-card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1"><i class="fas fa-file-invoice me-1"></i>Bulletins</p>
                <h3 class="mb-0">{{ $global->nombre_bulletins ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card data-card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1"><i class="fas fa-coins me-1"></i>Masse brute</p>
                <h4 class="mb-0">{{ number_format($global->masse_salariale_brute ?? 0, 0, ',', ' ') }} <small class="text-muted">XAF</small></h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card data-card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1"><i class="fas fa-wallet me-1"></i>Masse nette</p>
                <h4 class="mb-0">{{ number_format($global->masse_salariale_nette ?? 0, 0, ',', ' ') }} <small class="text-muted">XAF</small></h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card data-card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1"><i class="fas fa-money-bill-wave me-1"></i>Total paye</p>
                <h4 class="mb-0 text-success">{{ number_format($global->total_paye ?? 0, 0, ',', ' ') }} <small class="text-muted">XAF</small></h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Cotisations --}}
    <div class="col-12 col-lg-6">
        <div class="card data-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-hand-holding-usd me-2 text-muted"></i>Cotisations sociales</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <p class="text-muted small mb-1">Part salariale</p>
                        <h5 class="mb-0">{{ number_format($global->total_cotisations_salariales ?? 0, 0, ',', ' ') }} <small class="text-muted">XAF</small></h5>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Part patronale</p>
                        <h5 class="mb-0">{{ number_format($global->total_cotisations_patronales ?? 0, 0, ',', ' ') }} <small class="text-muted">XAF</small></h5>
                    </div>
                    <div class="col-12">
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total cotisations</span>
                            <strong>{{ number_format(($global->total_cotisations_salariales ?? 0) + ($global->total_cotisations_patronales ?? 0), 0, ',', ' ') }} XAF</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Impots et retenues --}}
    <div class="col-12 col-lg-6">
        <div class="card data-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-receipt me-2 text-muted"></i>Impots & retenues</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted">IRPP</td>
                        <td class="text-end"><strong>{{ number_format($global->total_irpp ?? 0, 0, ',', ' ') }} XAF</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Avances</td>
                        <td class="text-end"><strong>{{ number_format($global->total_avances ?? 0, 0, ',', ' ') }} XAF</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Autres retenues</td>
                        <td class="text-end"><strong>{{ number_format($global->total_retenues ?? 0, 0, ',', ' ') }} XAF</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Par departement --}}
    <div class="col-12">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-sitemap me-2 text-muted"></i>Repartition par departement</h5>
            </div>
            <div class="card-body p-0">
                @php $agregats = $global->agregats_par_departement ?? []; @endphp
                @if(empty($agregats))
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucun agregat par departement disponible</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Departement</th>
                                    <th class="text-end">Bulletins</th>
                                    <th class="text-end">Masse brute</th>
                                    <th class="text-end">Masse nette</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agregats as $row)
                                <tr>
                                    <td><strong>{{ $row['departement'] ?? '-' }}</strong></td>
                                    <td class="text-end">{{ $row['nb'] ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($row['brut'] ?? 0, 0, ',', ' ') }} XAF</td>
                                    <td class="text-end">{{ number_format($row['net'] ?? 0, 0, ',', ' ') }} XAF</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
