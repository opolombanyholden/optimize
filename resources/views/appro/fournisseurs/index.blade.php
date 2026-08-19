@extends('layouts.app')

@section('title', 'Fournisseurs')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Achats</li>
        <li class="breadcrumb-item active">Fournisseurs</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Fournisseurs</h1>
        <p class="text-muted mb-0">Gestion des fournisseurs</p>
    </div>
    <a href="{{ route('appro.fournisseurs.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouveau fournisseur
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('appro.fournisseurs.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Raison sociale, NIF, email..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-4">
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Actif</option>
                    <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('appro.fournisseurs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-body p-0">
        @if($fournisseurs->isEmpty())
            <div class="empty-state">
                <i class="fas fa-truck"></i>
                <p>Aucun fournisseur enregistre</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Raison sociale</th>
                            <th>NIF</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fournisseurs as $fournisseur)
                        <tr>
                            <td><strong>{{ $fournisseur->raison_sociale }}</strong></td>
                            <td>{{ $fournisseur->nif ?? '-' }}</td>
                            <td>{{ $fournisseur->contact ?? '-' }}</td>
                            <td>{{ $fournisseur->email ?? '-' }}</td>
                            <td>
                                <span class="badge badge-status {{ $fournisseur->statut ? 'badge-actif' : 'badge-inactif' }}">
                                    {{ $fournisseur->statut ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('appro.fournisseurs.show', $fournisseur) }}" class="btn btn-sm btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('appro.fournisseurs.edit', $fournisseur) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('appro.fournisseurs.destroy', $fournisseur) }}" method="POST" onsubmit="return confirm('Supprimer ce fournisseur ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if($fournisseurs->hasPages())
    <div class="card-footer d-flex justify-content-center">
        {{ $fournisseurs->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
