@extends('layouts.app')

@section('title', 'Qualifications / Diplomes')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Qualifications</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Qualifications / Diplomes</h1>
        <p class="text-muted mb-0">Suivi des qualifications et diplomes des employes</p>
    </div>
    <a href="{{ route('rh.qualifications.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvelle qualification
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.qualifications.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
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
            <div class="col-12 col-md-2">
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous</option>
                    <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Actif</option>
                    <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.qualifications.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-graduation-cap me-2 text-muted"></i>Liste des qualifications</h5>
        <span class="text-muted">{{ $qualifications->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($qualifications->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune qualification trouvee</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employe</th>
                            <th>Libelle</th>
                            <th>Niveau</th>
                            <th>Organisme</th>
                            <th>Debut</th>
                            <th>Fin</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($qualifications as $qualification)
                        <tr>
                            <td>{{ $qualification->employee?->noms }} {{ $qualification->employee?->prenoms }}</td>
                            <td>{{ $qualification->label ?? '-' }}</td>
                            <td>@if($qualification->niveau)<span class="badge bg-primary">{{ $qualification->niveau }}</span>@else —@endif</td>
                            <td>{{ $qualification->organisme ?? '-' }}</td>
                            <td>{{ $qualification->debut?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $qualification->fin?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($qualification->statut == 1)
                                    <span class="badge badge-status badge-valide">Actif</span>
                                @else
                                    <span class="badge badge-status badge-rejete">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.qualifications.show', $qualification) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.qualifications.edit', $qualification) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.qualifications.destroy', $qualification) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
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
@if($qualifications->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $qualifications->withQueryString()->links() }}
</div>
@endif
@endsection
