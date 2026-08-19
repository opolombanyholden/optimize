@extends('layouts.app')

@section('title', 'Evaluation - ' . ($evaluation->periode ?? ''))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.evaluations-performance.index') }}">Evaluations de performance</a></li>
        <li class="breadcrumb-item active">{{ $evaluation->periode }} &mdash; {{ $evaluation->employee?->noms }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Evaluation {{ $evaluation->periode }}</h1>
        <p class="text-muted mb-0">{{ $evaluation->employee?->noms }} {{ $evaluation->employee?->prenoms }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.evaluations-performance.edit', $evaluation) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <form action="{{ route('rh.evaluations-performance.destroy', $evaluation) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.evaluations-performance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Identite --}}
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-id-card me-2 text-muted"></i>Identite</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Employe</td>
                        <td>
                            @if($evaluation->employee_id)
                                <a href="{{ route('rh.employees.show', $evaluation->employee_id) }}">
                                    {{ $evaluation->employee?->noms }} {{ $evaluation->employee?->prenoms }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Evaluateur</td>
                        <td>{{ $evaluation->evaluateur?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Periode</td>
                        <td><strong>{{ $evaluation->periode }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date d'evaluation</td>
                        <td>{{ $evaluation->date_evaluation?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date d'entretien</td>
                        <td>{{ $evaluation->date_entretien?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($evaluation->statut == 0)
                                <span class="badge bg-secondary">Brouillon</span>
                            @elseif($evaluation->statut == 1)
                                <span class="badge badge-status badge-en-attente">Soumis a l'employe</span>
                            @elseif($evaluation->statut == 2)
                                <span class="badge badge-status badge-valide">Valide par employe</span>
                            @elseif($evaluation->statut == 3)
                                <span class="badge bg-dark">Cloture</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Bilan qualitatif --}}
        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-clipboard me-2 text-muted"></i>Bilan qualitatif</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-muted text-uppercase small">Points forts</h6>
                    @if($evaluation->points_forts)
                        <p class="mb-0">{{ $evaluation->points_forts }}</p>
                    @else
                        <p class="text-muted fst-italic mb-0">Non renseigne</p>
                    @endif
                </div>
                <hr>
                <div class="mb-3">
                    <h6 class="text-muted text-uppercase small">Axes d'amelioration</h6>
                    @if($evaluation->axes_amelioration)
                        <p class="mb-0">{{ $evaluation->axes_amelioration }}</p>
                    @else
                        <p class="text-muted fst-italic mb-0">Non renseigne</p>
                    @endif
                </div>
                <hr>
                <div class="mb-3">
                    <h6 class="text-muted text-uppercase small">Objectifs pour la periode suivante</h6>
                    @if($evaluation->objectifs_periode_suivante)
                        <p class="mb-0">{{ $evaluation->objectifs_periode_suivante }}</p>
                    @else
                        <p class="text-muted fst-italic mb-0">Non renseigne</p>
                    @endif
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <h6 class="text-muted text-uppercase small">Commentaire de l'employe</h6>
                        @if($evaluation->commentaire_employe)
                            <p class="mb-0">{{ $evaluation->commentaire_employe }}</p>
                        @else
                            <p class="text-muted fst-italic mb-0">Aucun commentaire</p>
                        @endif
                    </div>
                    <div class="col-12 col-md-6">
                        <h6 class="text-muted text-uppercase small">Commentaire du manager</h6>
                        @if($evaluation->commentaire_manager)
                            <p class="mb-0">{{ $evaluation->commentaire_manager }}</p>
                        @else
                            <p class="text-muted fst-italic mb-0">Aucun commentaire</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- PDI --}}
        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-graduation-cap me-2 text-muted"></i>Plan de developpement individuel</h5>
            </div>
            <div class="card-body">
                @if($evaluation->plan_developpement_individuel)
                    <p class="mb-0">{{ $evaluation->plan_developpement_individuel }}</p>
                @else
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucun PDI defini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        {{-- Notation visuelle --}}
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-star me-2 text-muted"></i>Notation globale</h5>
            </div>
            <div class="card-body text-center">
                @if($evaluation->note_globale)
                    <div class="display-4 mb-3">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $evaluation->note_globale ? 'text-warning' : 'text-muted' }}"></i>
                        @endfor
                    </div>
                    <p class="text-muted mb-0"><strong>{{ $evaluation->note_globale }}/5</strong></p>
                @else
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <p>Note non attribuee</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Signatures --}}
        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-signature me-2 text-muted"></i>Signatures</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>
                            <i class="fas {{ $evaluation->signature_employe ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} me-2"></i>
                            Signature employe
                        </span>
                        @if($evaluation->signature_employe)
                            <span class="badge bg-success">Signe</span>
                        @else
                            <span class="badge bg-secondary">En attente</span>
                        @endif
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>
                            <i class="fas {{ $evaluation->signature_manager ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} me-2"></i>
                            Signature manager
                        </span>
                        @if($evaluation->signature_manager)
                            <span class="badge bg-success">Signe</span>
                        @else
                            <span class="badge bg-secondary">En attente</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
