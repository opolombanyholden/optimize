@extends('layouts.app')

@section('title', 'Soldes de conges')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Soldes de conges</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Soldes de conges</h1>
        <p class="text-muted mb-0">Suivi des compteurs de conges par employe et par annee</p>
    </div>
    <a href="{{ route('rh.conges-soldes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouveau solde
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.conges-soldes.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="annee" class="form-label">Annee</label>
                <select name="annee" id="annee" class="form-select" onchange="this.form.submit()">
                    @for($a = 2024; $a <= 2027; $a++)
                        <option value="{{ $a }}" {{ $annee == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="employee_id" class="form-label">Employe</label>
                <select name="employee_id" id="employee_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les employes</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="type_conge" class="form-label">Type de conge</label>
                <select name="type_conge" id="type_conge" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les types</option>
                    @foreach(\App\Models\CongeSolde::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ request('type_conge') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.conges-soldes.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-umbrella-beach me-2 text-muted"></i>Soldes - Annee {{ $annee }}</h5>
        <span class="text-muted">{{ $soldes->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($soldes->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucun solde de conge trouve</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Employe</th>
                            <th>Type</th>
                            <th class="text-end">Droit annuel</th>
                            <th class="text-end">Report N-1</th>
                            <th class="text-end">Pris</th>
                            <th class="text-end">En attente</th>
                            <th class="text-end">Disponible</th>
                            <th style="min-width: 180px;">Progression</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($soldes as $solde)
                        @php
                            $total = (float) ($solde->droit_annuel ?? 0) + (float) ($solde->report_n_moins_1 ?? 0);
                            $pris  = (float) ($solde->pris_periode ?? 0);
                            $pct   = $total > 0 ? min(100, round(($pris / $total) * 100)) : 0;
                        @endphp
                        <tr>
                            <td>{{ $solde->employee?->noms }} {{ $solde->employee?->prenoms }}</td>
                            <td>{{ \App\Models\CongeSolde::TYPES[$solde->type_conge] ?? $solde->type_conge }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format((float)$solde->droit_annuel, 2, ',', ' '), '0'), ',') }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format((float)$solde->report_n_moins_1, 2, ',', ' '), '0'), ',') }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format((float)$solde->pris_periode, 2, ',', ' '), '0'), ',') }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format((float)$solde->en_attente, 2, ',', ' '), '0'), ',') }}</td>
                            <td class="text-end"><strong>{{ rtrim(rtrim(number_format((float)$solde->solde_disponible, 2, ',', ' '), '0'), ',') }} j</strong></td>
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success') }}" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">{{ $pris }} / {{ $total }} jours ({{ $pct }}%)</small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.conges-soldes.show', $solde) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.conges-soldes.edit', $solde) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.conges-soldes.destroy', $solde) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Pagination --}}
@if($soldes->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $soldes->withQueryString()->links() }}
</div>
@endif
@endsection
