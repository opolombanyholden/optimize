@extends('layouts.app')

@section('title', 'Absences')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Absences</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Absences</h1>
        <p class="text-muted mb-0">Gestion des absences et conges du personnel</p>
    </div>
    <a href="{{ route('rh.absences.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvelle absence
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.absences.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="type_absence" class="form-label">Type d'absence</label>
                <select name="type_absence" id="type_absence" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="Conge annuel" {{ request('type_absence') == 'Conge annuel' ? 'selected' : '' }}>Conge annuel</option>
                    <option value="Maladie" {{ request('type_absence') == 'Maladie' ? 'selected' : '' }}>Maladie</option>
                    <option value="Maternite" {{ request('type_absence') == 'Maternite' ? 'selected' : '' }}>Maternite</option>
                    <option value="Formation" {{ request('type_absence') == 'Formation' ? 'selected' : '' }}>Formation</option>
                    <option value="Autre" {{ request('type_absence') == 'Autre' ? 'selected' : '' }}>Autre</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>En attente</option>
                    <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Approuve</option>
                    <option value="2" {{ request('statut') === '2' ? 'selected' : '' }}>Rejete</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.absences.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-calendar-times me-2 text-muted"></i>Liste des absences</h5>
        <span class="text-muted">{{ $absences->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($absences->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune absence trouvee</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employe</th>
                            <th>Type absence</th>
                            <th>Date debut</th>
                            <th>Date fin</th>
                            <th>Jours</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absences as $absence)
                        <tr>
                            <td>{{ $absence->employee?->noms }} {{ $absence->employee?->prenoms }}</td>
                            <td>{{ $absence->type_abscence ?? '-' }}</td>
                            <td>{{ $absence->debut?->format('d/m/Y') }}</td>
                            <td>{{ $absence->fin?->format('d/m/Y') }}</td>
                            <td>{{ $absence->duree_jours ?? '-' }}</td>
                            <td>
                                @if($absence->statut == 0)
                                    <span class="badge badge-status badge-en-attente">En attente</span>
                                @elseif($absence->statut == 1)
                                    <span class="badge badge-status badge-valide">Approuve</span>
                                @elseif($absence->statut == 2)
                                    <span class="badge badge-status badge-rejete">Rejete</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.absences.show', $absence) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.absences.edit', $absence) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.absences.destroy', $absence) }}" method="POST" class="d-inline" onsubmit="return confirm('Etes-vous sur de vouloir supprimer cette absence ?');">
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
@if($absences->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $absences->withQueryString()->links() }}
</div>
@endif
@endsection
