@extends('layouts.app')

@section('title', 'Depart - ' . ($depart->employee?->noms ?? ''))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.departs.index') }}">Departs / Sorties</a></li>
        <li class="breadcrumb-item active">{{ $depart->employee?->noms }} {{ $depart->employee?->prenoms }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Depart : {{ $depart->employee?->noms }} {{ $depart->employee?->prenoms }}</h1>
        <p class="text-muted mb-0">{{ \App\Models\Depart::TYPES[$depart->type_depart] ?? $depart->type_depart }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.departs.edit', $depart) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <form action="{{ route('rh.departs.destroy', $depart) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.departs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

@if($depart->statut == 2)
<div class="alert alert-success d-flex align-items-center mb-4">
    <i class="fas fa-check-circle fa-2x me-3"></i>
    <div>
        <strong>Depart finalise</strong><br>
        <small>L'employe est marque comme parti dans le systeme.</small>
    </div>
</div>
@endif

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-door-open me-2 text-muted"></i>Details du depart</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Employe</td>
                        <td>
                            @if($depart->employee_id)
                                <a href="{{ route('rh.employees.show', $depart->employee_id) }}">
                                    {{ $depart->employee?->noms }} {{ $depart->employee?->prenoms }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Type de depart</td>
                        <td>{{ \App\Models\Depart::TYPES[$depart->type_depart] ?? $depart->type_depart }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Motif</td>
                        <td>{{ $depart->motif ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de notification</td>
                        <td>{{ $depart->date_notification?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date d'effet</td>
                        <td><strong>{{ $depart->date_effet?->format('d/m/Y') ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date solde de tout compte</td>
                        <td>{{ $depart->date_solde_tout_compte?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Indemnite de depart</td>
                        <td>
                            @if($depart->indemnite_depart !== null)
                                <strong>{{ number_format($depart->indemnite_depart, 0, ',', ' ') }} XAF</strong>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Solde de conges payes</td>
                        <td>{{ $depart->solde_conges_paye !== null ? $depart->solde_conges_paye . ' jour(s)' : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Traite par</td>
                        <td>{{ $depart->traitant?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($depart->statut == 0)
                                <span class="badge badge-status badge-en-attente">Annonce</span>
                            @elseif($depart->statut == 1)
                                <span class="badge badge-status badge-en-attente">En cours</span>
                            @elseif($depart->statut == 2)
                                <span class="badge bg-success">Finalise</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($depart->description)
        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-align-left me-2 text-muted"></i>Description / Commentaires</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $depart->description }}</p>
            </div>
        </div>
        @endif

        @if($depart->notes_entretien_sortie)
        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-comments me-2 text-muted"></i>Notes de l'entretien de sortie</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $depart->notes_entretien_sortie }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-12 col-lg-4">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-clipboard-check me-2 text-muted"></i>Checklist de sortie</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>
                            <i class="fas {{ $depart->preavis_effectue ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} me-2"></i>
                            Preavis effectue
                        </span>
                        @if($depart->preavis_effectue)
                            <span class="badge bg-success">OK</span>
                        @else
                            <span class="badge bg-secondary">En attente</span>
                        @endif
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>
                            <i class="fas {{ $depart->entretien_sortie_effectue ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} me-2"></i>
                            Entretien de sortie
                        </span>
                        @if($depart->entretien_sortie_effectue)
                            <span class="badge bg-success">OK</span>
                        @else
                            <span class="badge bg-secondary">En attente</span>
                        @endif
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>
                            <i class="fas {{ $depart->certificat_travail_url ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} me-2"></i>
                            Certificat de travail
                        </span>
                        @if($depart->certificat_travail_url)
                            <a href="{{ $depart->certificat_travail_url }}" target="_blank" class="badge bg-success text-decoration-none">Voir</a>
                        @else
                            <span class="badge bg-secondary">A delivrer</span>
                        @endif
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>
                            <i class="fas {{ $depart->attestation_pole_emploi_url ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} me-2"></i>
                            Attestation
                        </span>
                        @if($depart->attestation_pole_emploi_url)
                            <a href="{{ $depart->attestation_pole_emploi_url }}" target="_blank" class="badge bg-success text-decoration-none">Voir</a>
                        @else
                            <span class="badge bg-secondary">A delivrer</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
