@extends('layouts.app')

@section('title', 'Evenements de carriere')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Evenements de carriere</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Evenements de carriere</h1>
        <p class="text-muted mb-0">Historique des changements de poste, promotions et evenements RH</p>
    </div>
    <a href="{{ route('rh.evenements-carriere.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvel evenement
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.evenements-carriere.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="q" class="form-label">Recherche</label>
                <input type="text" name="q" id="q" class="form-control" placeholder="Libelle, poste..." value="{{ request('q') }}">
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
                <label for="typesevenementscarriere_id" class="form-label">Type d'evenement</label>
                <select name="typesevenementscarriere_id" id="typesevenementscarriere_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les types</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ request('typesevenementscarriere_id') == $type->id ? 'selected' : '' }}>{{ $type->libelle ?? $type->label ?? $type->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.evenements-carriere.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-arrow-trend-up me-2 text-muted"></i>Liste des evenements</h5>
        <span class="text-muted">{{ $evenements->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($evenements->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucun evenement de carriere trouve</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employe</th>
                            <th>Type</th>
                            <th>Libelle</th>
                            <th>Ancien poste</th>
                            <th>Nouveau poste</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evenements as $evenement)
                        <tr>
                            <td>{{ $evenement->date_effet?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $evenement->employee?->noms }} {{ $evenement->employee?->prenoms }}</td>
                            <td>{{ $evenement->type?->libelle ?? $evenement->type?->label ?? $evenement->type?->nom ?? '-' }}</td>
                            <td>{{ $evenement->libelle ?? '-' }}</td>
                            <td>{{ $evenement->ancien_poste ?? '-' }}</td>
                            <td>{{ $evenement->nouveau_poste ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('rh.evenements-carriere.show', $evenement) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.evenements-carriere.edit', $evenement) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.evenements-carriere.destroy', $evenement) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
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
@if($evenements->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $evenements->withQueryString()->links() }}
</div>
@endif
@endsection
