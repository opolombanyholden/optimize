@extends('layouts.app')

@section('title', 'Evenement - ' . ($evenement->libelle ?? ''))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.evenements-carriere.index') }}">Evenements de carriere</a></li>
        <li class="breadcrumb-item active">{{ $evenement->libelle }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ $evenement->libelle }}</h1>
        <p class="text-muted mb-0">Evenement de carriere pour {{ $evenement->employee?->noms }} {{ $evenement->employee?->prenoms }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.evenements-carriere.edit', $evenement) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <form action="{{ route('rh.evenements-carriere.destroy', $evenement) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.evenements-carriere.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-arrow-trend-up me-2 text-muted"></i>Details de l'evenement</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Employe</td>
                        <td>
                            @if($evenement->employee_id)
                                <a href="{{ route('rh.employees.show', $evenement->employee_id) }}">
                                    {{ $evenement->employee?->noms }} {{ $evenement->employee?->prenoms }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Type d'evenement</td>
                        <td>{{ $evenement->type?->libelle ?? $evenement->type?->label ?? $evenement->type?->nom ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Libelle</td>
                        <td>{{ $evenement->libelle ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date d'effet</td>
                        <td><strong>{{ $evenement->date_effet?->format('d/m/Y') ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Ancien poste</td>
                        <td>{{ $evenement->ancien_poste ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nouveau poste</td>
                        <td>{{ $evenement->nouveau_poste ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card data-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-align-left me-2 text-muted"></i>Description</h5>
            </div>
            <div class="card-body">
                @if($evenement->description)
                    <p class="mb-0">{{ $evenement->description }}</p>
                @else
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucune description renseignee</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
