@extends('layouts.app')

@section('title', 'Ayants-droit')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Ayants-droit</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Ayants-droit</h1>
        <p class="text-muted mb-0">Gestion des ayants-droit (conjoints, enfants, parents) des employes</p>
    </div>
    <a href="{{ route('rh.affilies.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvel ayant-droit
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.affilies.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="q" class="form-label">Recherche</label>
                <input type="text" name="q" id="q" class="form-control" placeholder="Nom, prenom, email..." value="{{ request('q') }}">
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
            <div class="col-12 col-md-2">
                <label for="liens" class="form-label">Lien</label>
                <select name="liens" id="liens" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous</option>
                    <option value="Conjoint" {{ request('liens') == 'Conjoint' ? 'selected' : '' }}>Conjoint</option>
                    <option value="Enfant" {{ request('liens') == 'Enfant' ? 'selected' : '' }}>Enfant</option>
                    <option value="Parent" {{ request('liens') == 'Parent' ? 'selected' : '' }}>Parent</option>
                    <option value="Autre" {{ request('liens') == 'Autre' ? 'selected' : '' }}>Autre</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.affilies.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-user-group me-2 text-muted"></i>Liste des ayants-droit</h5>
        <span class="text-muted">{{ $affilies->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($affilies->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucun ayant-droit trouve</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employe</th>
                            <th>Ayant-droit</th>
                            <th>Lien</th>
                            <th>Date naissance</th>
                            <th>Contact</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($affilies as $affilie)
                        <tr>
                            <td>{{ $affilie->employee?->noms }} {{ $affilie->employee?->prenoms }}</td>
                            <td>{{ $affilie->noms }} {{ $affilie->prenoms }}</td>
                            <td>{{ $affilie->liens ?? '-' }}</td>
                            <td>{{ $affilie->date_naissance?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $affilie->contact1 ?? '-' }}</td>
                            <td>
                                @if($affilie->statut == 1)
                                    <span class="badge badge-status badge-valide">Actif</span>
                                @else
                                    <span class="badge badge-status badge-rejete">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.affilies.show', $affilie) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.affilies.edit', $affilie) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.affilies.destroy', $affilie) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
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
@if($affilies->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $affilies->withQueryString()->links() }}
</div>
@endif
@endsection
