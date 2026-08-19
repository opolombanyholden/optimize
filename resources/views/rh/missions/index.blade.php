@extends('layouts.app')

@section('title', 'Missions')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Missions</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Missions</h1>
        <p class="text-muted mb-0">Suivi des missions, deplacements et frais</p>
    </div>
    <a href="{{ route('rh.missions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvelle mission
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.missions.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="q" class="form-label">Recherche</label>
                <input type="text" name="q" id="q" class="form-control" placeholder="Libelle, lieu..." value="{{ request('q') }}">
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
                <a href="{{ route('rh.missions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-route me-2 text-muted"></i>Liste des missions</h5>
        <span class="text-muted">{{ $missions->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($missions->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune mission trouvee</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employe</th>
                            <th>Mission</th>
                            <th>Lieu</th>
                            <th>Debut</th>
                            <th>Fin</th>
                            <th class="text-end">Budget</th>
                            <th class="text-end">Frais reels</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($missions as $mission)
                        <tr>
                            <td>{{ $mission->employee?->noms }} {{ $mission->employee?->prenoms }}</td>
                            <td>{{ $mission->label ?? '-' }}</td>
                            <td>{{ $mission->lieu ?? '-' }}</td>
                            <td>{{ $mission->debut?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $mission->fin?->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-end">
                                @if($mission->budget !== null)
                                    {{ number_format($mission->budget, 0, ',', ' ') }} XAF
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                @if($mission->frais_reels !== null)
                                    {{ number_format($mission->frais_reels, 0, ',', ' ') }} XAF
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($mission->statut == 0)
                                    <span class="badge badge-status badge-en-attente">Planifiee</span>
                                @elseif($mission->statut == 1)
                                    <span class="badge badge-status badge-en-attente">En cours</span>
                                @elseif($mission->statut == 2)
                                    <span class="badge badge-status badge-valide">Terminee</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.missions.show', $mission) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.missions.edit', $mission) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.missions.destroy', $mission) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
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
@if($missions->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $missions->withQueryString()->links() }}
</div>
@endif
@endsection
