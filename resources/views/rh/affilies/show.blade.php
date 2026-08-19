@extends('layouts.app')

@section('title', 'Ayant-droit - ' . ($affilie->noms ?? ''))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.affilies.index') }}">Ayants-droit</a></li>
        <li class="breadcrumb-item active">{{ $affilie->noms }} {{ $affilie->prenoms }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ $affilie->noms }} {{ $affilie->prenoms }}</h1>
        <p class="text-muted mb-0">{{ $affilie->liens ?? '-' }} de {{ $affilie->employee?->noms }} {{ $affilie->employee?->prenoms }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.affilies.edit', $affilie) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <form action="{{ route('rh.affilies.destroy', $affilie) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.affilies.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-user-group me-2 text-muted"></i>Informations de l'ayant-droit</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Employe</td>
                        <td>
                            @if($affilie->employee_id)
                                <a href="{{ route('rh.employees.show', $affilie->employee_id) }}">
                                    {{ $affilie->employee?->noms }} {{ $affilie->employee?->prenoms }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lien</td>
                        <td>{{ $affilie->liens ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Noms</td>
                        <td>{{ $affilie->noms ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Prenoms</td>
                        <td>{{ $affilie->prenoms ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de naissance</td>
                        <td>{{ $affilie->date_naissance?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Contact principal</td>
                        <td>{{ $affilie->contact1 ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Contact secondaire</td>
                        <td>{{ $affilie->contact2 ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td>{{ $affilie->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($affilie->statut == 1)
                                <span class="badge badge-status badge-valide">Actif</span>
                            @else
                                <span class="badge badge-status badge-rejete">Inactif</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card data-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-align-left me-2 text-muted"></i>Note</h5>
            </div>
            <div class="card-body">
                @if($affilie->label)
                    <p class="mb-0">{{ $affilie->label }}</p>
                @else
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucune note renseignee</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
