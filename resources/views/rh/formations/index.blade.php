@extends('layouts.app')

@section('title', 'Formations')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Formations</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Formations</h1>
        <p class="text-muted mb-0">Plan de formation et suivi des sessions</p>
    </div>
    <a href="{{ route('rh.formations.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvelle formation
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.formations.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="q" class="form-label">Recherche</label>
                <input type="text" name="q" id="q" class="form-control" placeholder="Libelle, organisme..." value="{{ request('q') }}">
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
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les statuts</option>
                    <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>Planifiee</option>
                    <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>En cours</option>
                    <option value="2" {{ request('statut') === '2' ? 'selected' : '' }}>Terminee</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.formations.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-chalkboard-user me-2 text-muted"></i>Liste des formations</h5>
        <span class="text-muted">{{ $formations->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($formations->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune formation trouvee</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employe</th>
                            <th>Formation</th>
                            <th>Organisme</th>
                            <th>Debut</th>
                            <th>Fin</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($formations as $formation)
                        <tr>
                            <td>{{ $formation->employee?->noms }} {{ $formation->employee?->prenoms }}</td>
                            <td>{{ $formation->label ?? '-' }}</td>
                            <td>{{ $formation->organisme ?? '-' }}</td>
                            <td>{{ $formation->debut?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $formation->fin?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($formation->statut == 0)
                                    <span class="badge badge-status badge-en-attente">Planifiee</span>
                                @elseif($formation->statut == 1)
                                    <span class="badge badge-status badge-en-attente">En cours</span>
                                @elseif($formation->statut == 2)
                                    <span class="badge badge-status badge-valide">Terminee</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.formations.show', $formation) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.formations.edit', $formation) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.formations.destroy', $formation) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
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
@if($formations->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $formations->withQueryString()->links() }}
</div>
@endif
@endsection
