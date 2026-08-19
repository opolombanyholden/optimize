@extends('layouts.app')

@section('title', 'Recrutement - ' . $recrutement->label)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.recrutements.index') }}">Recrutements</a></li>
        <li class="breadcrumb-item active">{{ $recrutement->label }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ $recrutement->label }}</h1>
        <p class="text-muted mb-0">
            Campagne de recrutement
            @if($recrutement->debut) &mdash; Publiee le {{ $recrutement->debut->format('d/m/Y') }} @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.recrutements.edit', $recrutement) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <a href="{{ route('rh.recrutements.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Informations --}}
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2 text-muted"></i>Details du recrutement</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 30%;">Titre du poste</td>
                        <td><strong>{{ $recrutement->label }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de publication</td>
                        <td>{{ $recrutement->debut?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date limite</td>
                        <td>{{ $recrutement->fin?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($recrutement->statut == 0)
                                <span class="badge badge-status badge-actif">Ouvert</span>
                            @elseif($recrutement->statut == 1)
                                <span class="badge badge-status badge-inactif">Ferme</span>
                            @elseif($recrutement->statut == 2)
                                <span class="badge badge-status badge-rejete">Annule</span>
                            @endif
                        </td>
                    </tr>
                    @if($recrutement->introduction)
                    <tr>
                        <td class="text-muted">Resume</td>
                        <td>{{ $recrutement->introduction }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Description --}}
        @if($recrutement->description)
        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-align-left me-2 text-muted"></i>Description detaillee</h5>
            </div>
            <div class="card-body">
                <div style="white-space: pre-wrap;">{{ $recrutement->description }}</div>
            </div>
        </div>
        @endif
    </div>

    {{-- Statistiques --}}
    <div class="col-12 col-lg-4">
        <div class="card data-card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar me-2 text-muted"></i>Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Profils recherches</span>
                    <span class="badge bg-primary">{{ $recrutement->profils->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Postulants</span>
                    <span class="badge bg-success">{{ $recrutement->postulants->count() }}</span>
                </div>
                @if($recrutement->fin)
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Jours restants</span>
                    <span class="badge {{ $recrutement->fin->isPast() ? 'bg-danger' : 'bg-warning' }}">
                        {{ $recrutement->fin->isPast() ? 'Expire' : $recrutement->fin->diffInDays(now()) . ' jour(s)' }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        {{-- Postulants --}}
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-users me-2 text-muted"></i>Postulants</h5>
            </div>
            <div class="card-body p-0">
                @if($recrutement->postulants->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucun postulant</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recrutement->postulants as $postulant)
                                <tr>
                                    <td>{{ $postulant->label ?? $postulant->nom ?? '-' }}</td>
                                    <td>
                                        @if($postulant->statut == 0)
                                            <span class="badge badge-status badge-en-attente">En attente</span>
                                        @elseif($postulant->statut == 1)
                                            <span class="badge badge-status badge-valide">Retenu</span>
                                        @elseif($postulant->statut == 2)
                                            <span class="badge badge-status badge-rejete">Non retenu</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
