@extends('layouts.app')

@section('title', 'Organisations')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Administration</li>
        <li class="breadcrumb-item active">Organisations</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Organisations</h1>
        <p class="text-muted mb-0">Structure organisationnelle de l'entreprise</p>
    </div>
    @can('create:organisation')
    <a href="{{ route('systeme.organisations.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvelle organisation
    </a>
    @endcan
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('systeme.organisations.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Nom de l'organisation..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-3">
                <label for="type" class="form-label">Type</label>
                <select name="type" id="type" class="form-select">
                    <option value="">Tous les types</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>{{ $type->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Actif</option>
                    <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('systeme.organisations.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-sitemap me-2 text-muted"></i>Liste des organisations</h5>
        <span class="text-muted">{{ $organisations->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($organisations->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune organisation trouvee</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Designation</th>
                            <th>Type</th>
                            <th>Organisation parente</th>
                            <th>Responsable</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($organisations as $organisation)
                        <tr>
                            <td>{{ $organisation->id }}</td>
                            <td><strong>{{ $organisation->label }}</strong></td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $organisation->type->label ?? '-' }}</span>
                            </td>
                            <td>{{ $organisation->parent->label ?? '-' }}</td>
                            <td>{{ $organisation->chef->name ?? '-' }}</td>
                            <td>
                                @if($organisation->statut == 1)
                                    <span class="badge badge-status badge-actif">Actif</span>
                                @else
                                    <span class="badge badge-status badge-rejete">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('systeme.organisations.show', $organisation) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('update:organisation')
                                <a href="{{ route('systeme.organisations.edit', $organisation) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                @endcan
                                @can('delete:organisation')
                                <form action="{{ route('systeme.organisations.destroy', $organisation) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette organisation ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
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
@if($organisations->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $organisations->links() }}
</div>
@endif
@endsection
