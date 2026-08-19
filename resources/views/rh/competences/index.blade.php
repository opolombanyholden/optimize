@extends('layouts.app')

@section('title', 'Competences')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Competences</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Competences</h1>
        <p class="text-muted mb-0">Cartographie des competences des employes</p>
    </div>
    <a href="{{ route('rh.competences.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvelle competence
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.competences.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="q" class="form-label">Recherche</label>
                <input type="text" name="q" id="q" class="form-control" placeholder="Libelle..." value="{{ request('q') }}">
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
                <label for="niveau" class="form-label">Niveau</label>
                <select name="niveau" id="niveau" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous</option>
                    <option value="debutant" {{ request('niveau') == 'debutant' ? 'selected' : '' }}>Debutant</option>
                    <option value="intermediaire" {{ request('niveau') == 'intermediaire' ? 'selected' : '' }}>Intermediaire</option>
                    <option value="avance" {{ request('niveau') == 'avance' ? 'selected' : '' }}>Avance</option>
                    <option value="expert" {{ request('niveau') == 'expert' ? 'selected' : '' }}>Expert</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.competences.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-brain me-2 text-muted"></i>Liste des competences</h5>
        <span class="text-muted">{{ $competences->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($competences->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune competence trouvee</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employe</th>
                            <th>Competence</th>
                            <th>Niveau</th>
                            <th>Debut</th>
                            <th>Fin</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($competences as $competence)
                        <tr>
                            <td>{{ $competence->employee?->noms }} {{ $competence->employee?->prenoms }}</td>
                            <td>{{ $competence->label ?? '-' }}</td>
                            <td>
                                @if($competence->niveau)
                                    <span class="badge bg-info-subtle text-info-emphasis">{{ ucfirst($competence->niveau) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $competence->debut?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $competence->fin?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($competence->statut == 1)
                                    <span class="badge badge-status badge-valide">Actif</span>
                                @else
                                    <span class="badge badge-status badge-rejete">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.competences.show', $competence) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.competences.edit', $competence) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.competences.destroy', $competence) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
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
@if($competences->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $competences->withQueryString()->links() }}
</div>
@endif
@endsection
