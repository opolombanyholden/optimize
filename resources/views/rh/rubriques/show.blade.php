@extends('layouts.app')

@section('title', 'Rubrique - ' . ($rubrique->libelle ?? ''))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.rubriques.index') }}">Rubriques de paie</a></li>
        <li class="breadcrumb-item active">{{ $rubrique->libelle }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ $rubrique->libelle }}</h1>
        <p class="text-muted mb-0">Code <code>{{ $rubrique->code }}</code></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.rubriques.edit', $rubrique) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <form action="{{ route('rh.rubriques.destroy', $rubrique) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.rubriques.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-file-invoice-dollar me-2 text-muted"></i>Parametres de la rubrique</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Code</td>
                        <td><code>{{ $rubrique->code }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Libelle</td>
                        <td>{{ $rubrique->libelle }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Type</td>
                        <td>
                            @if($rubrique->type == 'gain')
                                <span class="badge bg-success-subtle text-success-emphasis">Gain</span>
                            @elseif($rubrique->type == 'retenue')
                                <span class="badge bg-danger-subtle text-danger-emphasis">Retenue</span>
                            @elseif($rubrique->type == 'cotisation')
                                <span class="badge bg-info-subtle text-info-emphasis">Cotisation</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Base de calcul</td>
                        <td>{{ ucfirst($rubrique->base_calcul ?? '-') }}</td>
                    </tr>
                    @if($rubrique->base_calcul == 'pourcentage')
                    <tr>
                        <td class="text-muted">Taux</td>
                        <td><strong>{{ $rubrique->taux }} %</strong></td>
                    </tr>
                    @elseif($rubrique->base_calcul == 'fixe')
                    <tr>
                        <td class="text-muted">Montant fixe</td>
                        <td><strong>{{ number_format($rubrique->montant_fixe ?? 0, 0, ',', ' ') }} XAF</strong></td>
                    </tr>
                    @elseif($rubrique->base_calcul == 'formule')
                    <tr>
                        <td class="text-muted">Formule</td>
                        <td><pre class="mb-0"><code>{{ $rubrique->formule }}</code></pre></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Ordre d'affichage</td>
                        <td>{{ $rubrique->ordre_affichage ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($rubrique->statut == 1)
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
                <h5><i class="fas fa-flag me-2 text-muted"></i>Options</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        @if($rubrique->imposable)
                            <i class="fas fa-check-circle text-success me-2"></i>
                        @else
                            <i class="fas fa-times-circle text-muted me-2"></i>
                        @endif
                        Imposable
                    </li>
                    <li>
                        @if($rubrique->cotisable)
                            <i class="fas fa-check-circle text-success me-2"></i>
                        @else
                            <i class="fas fa-times-circle text-muted me-2"></i>
                        @endif
                        Cotisable
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
