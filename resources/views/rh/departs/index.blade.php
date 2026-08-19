@extends('layouts.app')

@section('title', 'Departs / Sorties')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Departs / Sorties</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Departs / Sorties</h1>
        <p class="text-muted mb-0">Gestion des sorties d'effectif (demission, retraite, fin de contrat...)</p>
    </div>
    <a href="{{ route('rh.departs.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouveau depart
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.departs.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="employee_id" class="form-label">Employe</label>
                <select name="employee_id" id="employee_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les employes</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="type_depart" class="form-label">Type de depart</label>
                <select name="type_depart" id="type_depart" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les types</option>
                    @foreach(\App\Models\Depart::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ request('type_depart') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.departs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-door-open me-2 text-muted"></i>Liste des departs</h5>
        <span class="text-muted">{{ $departs->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($departs->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucun depart trouve</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employe</th>
                            <th>Type</th>
                            <th>Date notification</th>
                            <th>Date d'effet</th>
                            <th class="text-end">Indemnite</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departs as $depart)
                        <tr>
                            <td>{{ $depart->employee?->noms }} {{ $depart->employee?->prenoms }}</td>
                            <td>{{ \App\Models\Depart::TYPES[$depart->type_depart] ?? $depart->type_depart }}</td>
                            <td>{{ $depart->date_notification?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $depart->date_effet?->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-end">
                                @if($depart->indemnite_depart !== null)
                                    {{ number_format($depart->indemnite_depart, 0, ',', ' ') }} XAF
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($depart->statut == 0)
                                    <span class="badge badge-status badge-en-attente">Annonce</span>
                                @elseif($depart->statut == 1)
                                    <span class="badge badge-status badge-en-attente">En cours</span>
                                @elseif($depart->statut == 2)
                                    <span class="badge bg-success">Finalise</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.departs.show', $depart) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.departs.edit', $depart) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.departs.destroy', $depart) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
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
@if($departs->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $departs->withQueryString()->links() }}
</div>
@endif
@endsection
