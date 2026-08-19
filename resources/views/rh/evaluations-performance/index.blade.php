@extends('layouts.app')

@section('title', 'Evaluations de performance')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Evaluations de performance</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Evaluations de performance</h1>
        <p class="text-muted mb-0">Cycles d'evaluation, entretiens annuels et plans de developpement</p>
    </div>
    <a href="{{ route('rh.evaluations-performance.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvelle evaluation
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.evaluations-performance.index') }}" class="row g-3 align-items-end">
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
                <label for="periode" class="form-label">Periode</label>
                <input type="text" name="periode" id="periode" class="form-control" placeholder="Ex: 2026-S1" value="{{ request('periode') }}">
            </div>
            <div class="col-12 col-md-3">
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Models\EvaluationPerformance::STATUTS as $key => $label)
                        <option value="{{ $key }}" {{ request('statut') !== null && request('statut') !== '' && request('statut') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.evaluations-performance.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-star-half-stroke me-2 text-muted"></i>Liste des evaluations</h5>
        <span class="text-muted">{{ $evaluations->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($evaluations->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune evaluation trouvee</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employe</th>
                            <th>Evaluateur</th>
                            <th>Periode</th>
                            <th>Date evaluation</th>
                            <th>Note globale</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evaluations as $evaluation)
                        <tr>
                            <td>{{ $evaluation->employee?->noms }} {{ $evaluation->employee?->prenoms }}</td>
                            <td>{{ $evaluation->evaluateur?->name ?? '-' }}</td>
                            <td>{{ $evaluation->periode ?? '-' }}</td>
                            <td>{{ $evaluation->date_evaluation?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($evaluation->note_globale)
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $evaluation->note_globale ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($evaluation->statut == 0)
                                    <span class="badge bg-secondary">Brouillon</span>
                                @elseif($evaluation->statut == 1)
                                    <span class="badge badge-status badge-en-attente">Soumis</span>
                                @elseif($evaluation->statut == 2)
                                    <span class="badge badge-status badge-valide">Valide</span>
                                @elseif($evaluation->statut == 3)
                                    <span class="badge bg-dark">Cloture</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.evaluations-performance.show', $evaluation) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.evaluations-performance.edit', $evaluation) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.evaluations-performance.destroy', $evaluation) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
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
@if($evaluations->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $evaluations->withQueryString()->links() }}
</div>
@endif
@endsection
