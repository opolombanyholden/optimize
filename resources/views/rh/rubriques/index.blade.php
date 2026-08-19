@extends('layouts.app')

@section('title', 'Rubriques de paie')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Rubriques de paie</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Rubriques de paie</h1>
        <p class="text-muted mb-0">Parametrage des rubriques (gains, retenues, cotisations)</p>
    </div>
    <a href="{{ route('rh.rubriques.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvelle rubrique
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.rubriques.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="q" class="form-label">Recherche</label>
                <input type="text" name="q" id="q" class="form-control" placeholder="Code, libelle..." value="{{ request('q') }}">
            </div>
            <div class="col-12 col-md-3">
                <label for="type" class="form-label">Type</label>
                <select name="type" id="type" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les types</option>
                    <option value="gain" {{ request('type') == 'gain' ? 'selected' : '' }}>Gain</option>
                    <option value="retenue" {{ request('type') == 'retenue' ? 'selected' : '' }}>Retenue</option>
                    <option value="cotisation" {{ request('type') == 'cotisation' ? 'selected' : '' }}>Cotisation</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous</option>
                    <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Actif</option>
                    <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.rubriques.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-file-invoice-dollar me-2 text-muted"></i>Liste des rubriques</h5>
        <span class="text-muted">{{ $rubriques->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($rubriques->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune rubrique trouvee</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ordre</th>
                            <th>Code</th>
                            <th>Libelle</th>
                            <th>Type</th>
                            <th>Base de calcul</th>
                            <th>Valeur</th>
                            <th>Flags</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rubriques as $rubrique)
                        <tr>
                            <td>{{ $rubrique->ordre_affichage ?? '-' }}</td>
                            <td><code>{{ $rubrique->code }}</code></td>
                            <td>{{ $rubrique->libelle }}</td>
                            <td>
                                @if($rubrique->type == 'gain')
                                    <span class="badge bg-success-subtle text-success-emphasis">Gain</span>
                                @elseif($rubrique->type == 'retenue')
                                    <span class="badge bg-danger-subtle text-danger-emphasis">Retenue</span>
                                @elseif($rubrique->type == 'cotisation')
                                    <span class="badge bg-info-subtle text-info-emphasis">Cotisation</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($rubrique->base_calcul ?? '-') }}</td>
                            <td>
                                @if($rubrique->base_calcul == 'pourcentage')
                                    {{ $rubrique->taux }} %
                                @elseif($rubrique->base_calcul == 'fixe')
                                    {{ number_format($rubrique->montant_fixe ?? 0, 0, ',', ' ') }} XAF
                                @elseif($rubrique->base_calcul == 'formule')
                                    <small class="text-muted">Formule</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($rubrique->imposable)
                                    <span class="badge bg-warning-subtle text-warning-emphasis" title="Imposable">I</span>
                                @endif
                                @if($rubrique->cotisable)
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis" title="Cotisable">C</span>
                                @endif
                            </td>
                            <td>
                                @if($rubrique->statut == 1)
                                    <span class="badge badge-status badge-valide">Actif</span>
                                @else
                                    <span class="badge badge-status badge-rejete">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.rubriques.show', $rubrique) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.rubriques.edit', $rubrique) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.rubriques.destroy', $rubrique) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Pagination --}}
@if($rubriques->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $rubriques->withQueryString()->links() }}
</div>
@endif
@endsection
