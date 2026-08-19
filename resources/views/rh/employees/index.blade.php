@extends('layouts.app')

@section('title', 'Employes')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Employes</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Employes</h1>
        <p class="text-muted mb-0">Gestion du personnel de l'organisation</p>
    </div>
    <a href="{{ route('rh.employees.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvel employe
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.employees.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Nom, prenom, matricule..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-3">
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="1" {{ request('statut') == '1' ? 'selected' : '' }}>Actif</option>
                    <option value="2" {{ request('statut') == '2' ? 'selected' : '' }}>Inactif</option>
                    <option value="3" {{ request('statut') == '3' ? 'selected' : '' }}>Suspendu</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.employees.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-users me-2 text-muted"></i>Liste des employes</h5>
        <span class="text-muted">{{ $employees->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($employees->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucun employe trouve</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prenom</th>
                            <th>Poste</th>
                            <th>Departement</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td><strong>{{ $employee->matricule ?? '-' }}</strong></td>
                            <td>{{ $employee->noms }}</td>
                            <td>{{ $employee->prenoms }}</td>
                            <td>{{ $employee->poste ?? '-' }}</td>
                            <td>{{ $employee->departement ?? '-' }}</td>
                            <td>
                                @if($employee->statut == 1)
                                    <span class="badge badge-status badge-actif">Actif</span>
                                @elseif($employee->statut == 2)
                                    <span class="badge badge-status badge-inactif">Inactif</span>
                                @elseif($employee->statut == 3)
                                    <span class="badge badge-status badge-en-attente">Suspendu</span>
                                @else
                                    <span class="badge badge-status badge-brouillon">Non defini</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.employees.show', $employee) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.employees.edit', $employee) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Etes-vous sur de vouloir supprimer cet employe ?');">
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
@if($employees->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $employees->withQueryString()->links() }}
</div>
@endif
@endsection
