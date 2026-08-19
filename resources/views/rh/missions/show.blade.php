@extends('layouts.app')

@section('title', 'Mission - ' . ($mission->label ?? ''))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.missions.index') }}">Missions</a></li>
        <li class="breadcrumb-item active">{{ $mission->label }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ $mission->label }}</h1>
        <p class="text-muted mb-0">Mission de {{ $mission->employee?->noms }} {{ $mission->employee?->prenoms }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.missions.edit', $mission) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <form action="{{ route('rh.missions.destroy', $mission) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.missions.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-route me-2 text-muted"></i>Details de la mission</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Employe</td>
                        <td>
                            @if($mission->employee_id)
                                <a href="{{ route('rh.employees.show', $mission->employee_id) }}">
                                    {{ $mission->employee?->noms }} {{ $mission->employee?->prenoms }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Libelle</td>
                        <td>{{ $mission->label ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lieu</td>
                        <td>{{ $mission->lieu ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de debut</td>
                        <td>{{ $mission->debut?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de fin</td>
                        <td>{{ $mission->fin?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($mission->statut == 0)
                                <span class="badge badge-status badge-en-attente">Planifiee</span>
                            @elseif($mission->statut == 1)
                                <span class="badge badge-status badge-en-attente">En cours</span>
                            @elseif($mission->statut == 2)
                                <span class="badge badge-status badge-valide">Terminee</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($mission->description)
        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-align-left me-2 text-muted"></i>Description detaillee</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $mission->description }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-12 col-lg-4">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-money-bill-wave me-2 text-muted"></i>Budget</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Previsionnel</td>
                        <td class="text-end"><strong>
                            @if($mission->budget !== null)
                                {{ number_format($mission->budget, 0, ',', ' ') }} XAF
                            @else
                                -
                            @endif
                        </strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Reel</td>
                        <td class="text-end"><strong>
                            @if($mission->frais_reels !== null)
                                {{ number_format($mission->frais_reels, 0, ',', ' ') }} XAF
                            @else
                                -
                            @endif
                        </strong></td>
                    </tr>
                    @if($mission->budget !== null && $mission->frais_reels !== null)
                    <tr>
                        <td class="text-muted">Ecart</td>
                        <td class="text-end">
                            @php $ecart = $mission->frais_reels - $mission->budget; @endphp
                            <strong class="{{ $ecart > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($ecart, 0, ',', ' ') }} XAF
                            </strong>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2 text-muted"></i>Introduction</h5>
            </div>
            <div class="card-body">
                @if($mission->introduction)
                    <p class="mb-0">{{ $mission->introduction }}</p>
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
