@extends('layouts.app')

@section('title', 'Exercices')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item active">Exercices</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Exercices budg&eacute;taires</h1>
        <p class="text-muted mb-0">G&eacute;rez vos exercices budg&eacute;taires et suivez leur &eacute;volution.</p>
    </div>
    <a href="{{ route('finance.exercices.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouvel exercice
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('finance.exercices.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Libell&eacute;, ann&eacute;e..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-4">
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="1" {{ request('statut') == '1' ? 'selected' : '' }}>Actif</option>
                    <option value="2" {{ request('statut') == '2' ? 'selected' : '' }}>Cl&ocirc;tur&eacute;</option>
                    <option value="3" {{ request('statut') == '3' ? 'selected' : '' }}>Brouillon</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('finance.exercices.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>R&eacute;initialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-calendar-alt me-2 text-muted"></i>Liste des exercices</h5>
        <span class="text-muted small">{{ $exercices->total() }} r&eacute;sultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($exercices->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucun exercice trouv&eacute;</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Libell&eacute;</th>
                            <th>Ann&eacute;e</th>
                            <th>Budget initial</th>
                            <th>Dotation</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exercices as $exercice)
                        <tr>
                            <td><strong>{{ $exercice->libelle }}</strong></td>
                            <td>{{ $exercice->exercice }}</td>
                            <td>{{ number_format($exercice->budgetglobalinitial ?? 0, 0, ',', ' ') }} F</td>
                            <td>{{ number_format($exercice->dotationglobale ?? 0, 0, ',', ' ') }} F</td>
                            <td>
                                <span class="badge bg-{{ $exercice->statut_couleur }}">{{ $exercice->statut_libelle }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('finance.exercices.show', $exercice) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($exercice->peutEtreModifie())
                                    <a href="{{ route('finance.exercices.edit', $exercice) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                @elseif(auth()->user()->hasRole('super-admin'))
                                    <a href="{{ route('finance.exercices.edit', $exercice) }}" class="btn btn-sm btn-outline-warning"
                                       title="[Super-admin] Forcer la modification ({{ $exercice->statut_libelle }})">
                                        <i class="fas fa-shield-halved"></i>
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary" disabled
                                            title="Non modifiable : {{ $exercice->statut_libelle }}">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                @endif

                                @if($exercice->peutEtreSupprime())
                                    <form action="{{ route('finance.exercices.destroy', $exercice) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet exercice ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @elseif(auth()->user()->hasRole('super-admin'))
                                    <form action="{{ route('finance.exercices.destroy', $exercice) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('[SUPER-ADMIN] Forcer la suppression d\'un exercice {{ $exercice->statut_libelle }} ET de toutes ses lignes budgétaires ? Cette opération est irréversible.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="[Super-admin] Forcer la suppression">
                                            <i class="fas fa-shield-halved"></i>
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-outline-danger" disabled
                                            title="Suppression interdite : {{ $exercice->statut_libelle }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif

                                @if($exercice->peut_cloturer)
                                    <form action="{{ route('finance.exercices.cloturer', $exercice) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Clôturer définitivement cet exercice ?')">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" title="Clôturer">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    </form>
                                @endif

                                @if($exercice->peutEtreAnnule())
                                    <button type="button" class="btn btn-sm btn-outline-warning" title="Annuler"
                                            data-bs-toggle="modal" data-bs-target="#modal-annuler-{{ $exercice->id }}">
                                        <i class="fas fa-ban"></i>
                                    </button>
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

{{-- Pagination --}}
<div class="d-flex justify-content-center mt-4">
    {{ $exercices->links() }}
</div>

{{-- Modales d'annulation --}}
@foreach($exercices as $exercice)
    @if($exercice->peutEtreAnnule())
    <div class="modal fade" id="modal-annuler-{{ $exercice->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('finance.exercices.annuler', $exercice) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-ban text-warning me-2"></i> Annuler l'exercice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>{{ $exercice->libelle }}</strong> passera au statut <strong>Annulé</strong>.
                        Cette action est irréversible. L'exercice conservera sa trace pour l'audit.
                    </div>
                    <label class="form-label">Motif d'annulation <span class="text-danger">*</span></label>
                    <textarea name="motif_annulation" class="form-control" rows="4" required minlength="10" maxlength="500"
                              placeholder="Justifier l'annulation de l'exercice (min. 10 caractères)…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-ban me-1"></i> Confirmer l'annulation</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach
@endsection
