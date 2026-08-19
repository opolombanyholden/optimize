@extends('layouts.app')

@section('title', 'Sanction - ' . ($sanction->motif ?? ''))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.sanctions.index') }}">Sanctions disciplinaires</a></li>
        <li class="breadcrumb-item active">{{ \Illuminate\Support\Str::limit($sanction->motif, 40) }}</li>
    </ol>
@endsection

@section('content')
@php
    $niveauCouleurs = [
        'mineure' => 'info',
        'modere'  => 'warning',
        'grave'   => 'danger',
        'severe'  => 'dark',
    ];
    $couleur = $niveauCouleurs[$sanction->niveau_gravite] ?? 'secondary';
@endphp
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ \App\Models\Sanction::TYPES[$sanction->type] ?? $sanction->type }}</h1>
        <p class="text-muted mb-0">Sanction infligee a {{ $sanction->employee?->noms }} {{ $sanction->employee?->prenoms }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.sanctions.edit', $sanction) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <form action="{{ route('rh.sanctions.destroy', $sanction) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.sanctions.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-gavel me-2 text-muted"></i>Details de la sanction</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Employe</td>
                        <td>
                            @if($sanction->employee_id)
                                <a href="{{ route('rh.employees.show', $sanction->employee_id) }}">
                                    {{ $sanction->employee?->noms }} {{ $sanction->employee?->prenoms }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Type</td>
                        <td>{{ \App\Models\Sanction::TYPES[$sanction->type] ?? $sanction->type }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Motif</td>
                        <td>{{ $sanction->motif }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date des faits</td>
                        <td>{{ $sanction->date_fait?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de notification</td>
                        <td>{{ $sanction->date_notification?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de fin (mise a pied)</td>
                        <td>{{ $sanction->date_fin?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Niveau de gravite</td>
                        <td>
                            <span class="badge bg-{{ $couleur }}">
                                {{ \App\Models\Sanction::NIVEAUX[$sanction->niveau_gravite] ?? $sanction->niveau_gravite }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Decideur</td>
                        <td>{{ $sanction->decideur?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($sanction->statut == 0)
                                <span class="badge badge-status badge-rejete">Annulee</span>
                            @elseif($sanction->statut == 1)
                                <span class="badge badge-status badge-valide">Active</span>
                            @elseif($sanction->statut == 2)
                                <span class="badge badge-status badge-en-attente">Archivee</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($sanction->description)
        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-align-left me-2 text-muted"></i>Description detaillee</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $sanction->description }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-12 col-lg-4">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-comment-dots me-2 text-muted"></i>Reaction de l'employe</h5>
            </div>
            <div class="card-body">
                @if($sanction->reaction_employe)
                    <p class="mb-0">{{ $sanction->reaction_employe }}</p>
                @else
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucune reaction enregistree</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
