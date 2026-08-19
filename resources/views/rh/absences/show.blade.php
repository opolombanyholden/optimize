@extends('layouts.app')

@section('title', 'Absence - ' . $absence->label)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.absences.index') }}">Absences</a></li>
        <li class="breadcrumb-item active">{{ $absence->label }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ $absence->label }}</h1>
        <p class="text-muted mb-0">Demande d'absence de {{ $absence->employee?->noms }} {{ $absence->employee?->prenoms }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.absences.edit', $absence) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <a href="{{ route('rh.absences.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Informations de l'absence --}}
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-calendar-times me-2 text-muted"></i>Details de l'absence</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Employe</td>
                        <td>
                            <a href="{{ route('rh.employees.show', $absence->employee_id) }}">
                                {{ $absence->employee?->noms }} {{ $absence->employee?->prenoms }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Type d'absence</td>
                        <td>{{ $absence->type_abscence ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date debut</td>
                        <td>{{ $absence->debut?->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date fin</td>
                        <td>{{ $absence->fin?->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Duree</td>
                        <td><strong>{{ $absence->duree_jours ?? '-' }} jour(s)</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($absence->statut == 0)
                                <span class="badge badge-status badge-en-attente">En attente</span>
                            @elseif($absence->statut == 1)
                                <span class="badge badge-status badge-valide">Approuve</span>
                            @elseif($absence->statut == 2)
                                <span class="badge badge-status badge-rejete">Rejete</span>
                            @endif
                        </td>
                    </tr>
                    @if($absence->valide_par)
                    <tr>
                        <td class="text-muted">Valide par</td>
                        <td>{{ $absence->valideur?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de validation</td>
                        <td>{{ $absence->date_validation?->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Motif --}}
    <div class="col-12 col-lg-4">
        <div class="card data-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-align-left me-2 text-muted"></i>Motif</h5>
            </div>
            <div class="card-body">
                @if($absence->description)
                    <p class="mb-0">{{ $absence->description }}</p>
                @else
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucun motif renseigne</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
