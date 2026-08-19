@extends('layouts.app')

@section('title', 'Solde de conges - ' . ($solde->employee?->noms ?? ''))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.conges-soldes.index') }}">Soldes de conges</a></li>
        <li class="breadcrumb-item active">{{ $solde->employee?->noms }} {{ $solde->employee?->prenoms }} &mdash; {{ $solde->annee }}</li>
    </ol>
@endsection

@section('content')
@php
    $total = (float) ($solde->droit_annuel ?? 0) + (float) ($solde->report_n_moins_1 ?? 0);
    $pris  = (float) ($solde->pris_periode ?? 0);
    $pct   = $total > 0 ? min(100, round(($pris / $total) * 100)) : 0;
@endphp
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Solde {{ $solde->annee }} &mdash; {{ \App\Models\CongeSolde::TYPES[$solde->type_conge] ?? $solde->type_conge }}</h1>
        <p class="text-muted mb-0">{{ $solde->employee?->noms }} {{ $solde->employee?->prenoms }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.conges-soldes.edit', $solde) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <form action="{{ route('rh.conges-soldes.destroy', $solde) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.conges-soldes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-umbrella-beach me-2 text-muted"></i>Compteur de conges</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Employe</td>
                        <td>
                            @if($solde->employee_id)
                                <a href="{{ route('rh.employees.show', $solde->employee_id) }}">
                                    {{ $solde->employee?->noms }} {{ $solde->employee?->prenoms }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Annee</td>
                        <td><strong>{{ $solde->annee }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Type de conge</td>
                        <td>{{ \App\Models\CongeSolde::TYPES[$solde->type_conge] ?? $solde->type_conge }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Droit annuel</td>
                        <td>{{ $solde->droit_annuel }} jour(s)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Report N-1</td>
                        <td>{{ $solde->report_n_moins_1 }} jour(s)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Acquis sur la periode</td>
                        <td>{{ $solde->acquis_periode }} jour(s)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Pris sur la periode</td>
                        <td>{{ $solde->pris_periode }} jour(s)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">En attente</td>
                        <td>{{ $solde->en_attente }} jour(s)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Periode d'acquisition</td>
                        <td>
                            {{ $solde->date_debut_acquisition?->format('d/m/Y') ?? '-' }}
                            &rarr;
                            {{ $solde->date_fin_acquisition?->format('d/m/Y') ?? '-' }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie me-2 text-muted"></i>Solde disponible</h5>
            </div>
            <div class="card-body text-center">
                <div class="display-3 mb-2">{{ rtrim(rtrim(number_format((float)$solde->solde_disponible, 2, ',', ' '), '0'), ',') }}</div>
                <p class="text-muted mb-3">jour(s) disponible(s)</p>

                <div class="progress mb-2" style="height: 14px;">
                    <div class="progress-bar bg-{{ $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success') }}" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                        {{ $pct }}%
                    </div>
                </div>
                <small class="text-muted">{{ $pris }} jour(s) pris sur {{ $total }} jour(s)</small>
            </div>
        </div>
    </div>
</div>
@endsection
