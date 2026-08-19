@extends('layouts.app')

@section('title', 'Lignes budg&eacute;taires')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item active">Lignes budg&eacute;taires</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Lignes budg&eacute;taires</h1>
        <p class="text-muted mb-0">G&eacute;rez les lignes de budget associ&eacute;es aux exercices.</p>
    </div>
    <a href="{{ route('finance.budgets.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouvelle ligne
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('finance.budgets.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="R&eacute;f&eacute;rence, libell&eacute;..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-4">
                <label for="exercice_id" class="form-label">Exercice</label>
                <select name="exercice_id" id="exercice_id" class="form-select">
                    <option value="">Tous les exercices</option>
                    @foreach($exercices as $exercice)
                        <option value="{{ $exercice->id }}" {{ request('exercice_id') == $exercice->id ? 'selected' : '' }}>
                            {{ $exercice->libelle }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('finance.budgets.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>R&eacute;initialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-file-invoice-dollar me-2 text-muted"></i>Liste des lignes budg&eacute;taires</h5>
        <span class="text-muted small">{{ $budgets->total() }} r&eacute;sultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($budgets->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune ligne budg&eacute;taire trouv&eacute;e</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Exercice</th>
                            <th>Ligne</th>
                            <th>Montant initial</th>
                            <th>Montant r&eacute;vis&eacute;</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($budgets as $budget)
                        <tr>
                            <td>{{ $budget->exercice->libelle ?? '-' }}</td>
                            <td><strong>{{ $budget->ligne->libelle ?? $budget->ligne_id }}</strong></td>
                            <td>{{ number_format($budget->montant_initial ?? 0, 0, ',', ' ') }} F</td>
                            <td>{{ number_format($budget->montant_revise ?? 0, 0, ',', ' ') }} F</td>
                            <td>
                                @if($budget->statut == 1)
                                    <span class="badge badge-status badge-actif">Actif</span>
                                @elseif($budget->statut == 2)
                                    <span class="badge badge-status badge-inactif">Cl&ocirc;tur&eacute;</span>
                                @else
                                    <span class="badge badge-status badge-brouillon">Brouillon</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('finance.budgets.show', $budget) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('finance.budgets.edit', $budget) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('finance.budgets.destroy', $budget) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette ligne budg&eacute;taire ?')">
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
<div class="d-flex justify-content-center mt-4">
    {{ $budgets->links() }}
</div>
@endsection
