@extends('layouts.app')

@section('title', 'Competence - ' . ($competence->label ?? ''))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.competences.index') }}">Competences</a></li>
        <li class="breadcrumb-item active">{{ $competence->label }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ $competence->label }}</h1>
        <p class="text-muted mb-0">Competence de {{ $competence->employee?->noms }} {{ $competence->employee?->prenoms }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.competences.edit', $competence) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <form action="{{ route('rh.competences.destroy', $competence) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.competences.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-brain me-2 text-muted"></i>Details de la competence</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Employe</td>
                        <td>
                            @if($competence->employee_id)
                                <a href="{{ route('rh.employees.show', $competence->employee_id) }}">
                                    {{ $competence->employee?->noms }} {{ $competence->employee?->prenoms }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Libelle</td>
                        <td>{{ $competence->label ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Niveau</td>
                        <td>
                            @if($competence->niveau)
                                <span class="badge bg-info-subtle text-info-emphasis">{{ ucfirst($competence->niveau) }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de debut</td>
                        <td>{{ $competence->debut?->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de fin</td>
                        <td>{{ $competence->fin?->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($competence->statut == 1)
                                <span class="badge badge-status badge-valide">Actif</span>
                            @else
                                <span class="badge badge-status badge-rejete">Inactif</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($competence->description)
        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-align-left me-2 text-muted"></i>Description detaillee</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $competence->description }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-12 col-lg-4">
        <div class="card data-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2 text-muted"></i>Introduction</h5>
            </div>
            <div class="card-body">
                @if($competence->introduction)
                    <p class="mb-0">{{ $competence->introduction }}</p>
                @else
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucune introduction renseignee</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
