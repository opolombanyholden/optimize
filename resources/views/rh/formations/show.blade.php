@extends('layouts.app')

@section('title', 'Formation - ' . ($formation->label ?? ''))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.formations.index') }}">Formations</a></li>
        <li class="breadcrumb-item active">{{ $formation->label }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ $formation->label }}</h1>
        <p class="text-muted mb-0">Formation de {{ $formation->employee?->noms }} {{ $formation->employee?->prenoms }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.formations.edit', $formation) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <form action="{{ route('rh.formations.destroy', $formation) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.formations.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-chalkboard-user me-2 text-muted"></i>Details de la formation</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Employe</td>
                        <td>
                            @if($formation->employee_id)
                                <a href="{{ route('rh.employees.show', $formation->employee_id) }}">
                                    {{ $formation->employee?->noms }} {{ $formation->employee?->prenoms }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Libelle</td>
                        <td>{{ $formation->label ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Organisme</td>
                        <td>{{ $formation->organisme ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de debut</td>
                        <td>{{ $formation->debut?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de fin</td>
                        <td>{{ $formation->fin?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($formation->statut == 0)
                                <span class="badge badge-status badge-en-attente">Planifiee</span>
                            @elseif($formation->statut == 1)
                                <span class="badge badge-status badge-en-attente">En cours</span>
                            @elseif($formation->statut == 2)
                                <span class="badge badge-status badge-valide">Terminee</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($formation->description)
        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-align-left me-2 text-muted"></i>Description detaillee</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $formation->description }}</p>
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
                @if($formation->introduction)
                    <p class="mb-0">{{ $formation->introduction }}</p>
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
