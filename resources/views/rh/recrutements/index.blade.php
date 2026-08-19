@extends('layouts.app')

@section('title', 'Recrutements')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Recrutements</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Recrutements</h1>
        <p class="text-muted mb-0">Gestion des campagnes de recrutement</p>
    </div>
    <a href="{{ route('rh.recrutements.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouveau recrutement
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.recrutements.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Titre du poste..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-4">
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>Ouvert</option>
                    <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Ferme</option>
                    <option value="2" {{ request('statut') === '2' ? 'selected' : '' }}>Annule</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.recrutements.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-user-plus me-2 text-muted"></i>Campagnes de recrutement</h5>
        <span class="text-muted">{{ $recrutements->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($recrutements->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucun recrutement trouve</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Poste</th>
                            <th>Date publication</th>
                            <th>Date limite</th>
                            <th>Nb postulants</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recrutements as $recrutement)
                        <tr>
                            <td><strong>{{ $recrutement->label }}</strong></td>
                            <td>{{ $recrutement->debut?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $recrutement->fin?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $recrutement->postulants_count ?? 0 }}</span>
                            </td>
                            <td>
                                @if($recrutement->statut == 0)
                                    <span class="badge badge-status badge-actif">Ouvert</span>
                                @elseif($recrutement->statut == 1)
                                    <span class="badge badge-status badge-inactif">Ferme</span>
                                @elseif($recrutement->statut == 2)
                                    <span class="badge badge-status badge-rejete">Annule</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.recrutements.show', $recrutement) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.recrutements.edit', $recrutement) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.recrutements.destroy', $recrutement) }}" method="POST" class="d-inline" onsubmit="return confirm('Etes-vous sur de vouloir supprimer ce recrutement ?');">
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
@if($recrutements->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $recrutements->withQueryString()->links() }}
</div>
@endif
@endsection
