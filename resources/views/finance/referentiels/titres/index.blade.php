@extends('layouts.app')
@section('title', 'Titres / Familles')
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item">Référentiels</li>
        <li class="breadcrumb-item active">Titres</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-sitemap me-2 text-muted"></i>Titres (familles de lignes)</h1>
        <p class="text-muted mb-0">Familles/types de lignes budgétaires — référentiel.</p>
    </div>
    @can('create:titre')
        <a href="{{ route('finance.referentiels.titres.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouveau titre</a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6"><label class="form-label">Recherche</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Imputation, libellé…"></div>
            <div class="col-md-4"><label class="form-label">Type</label>
                <select name="type_ligne" class="form-select">
                    <option value="">Tous</option>
                    <option value="depense" @selected(request('type_ligne') === 'depense')>Dépense</option>
                    <option value="recette" @selected(request('type_ligne') === 'recette')>Recette</option>
                    <option value="mixte" @selected(request('type_ligne') === 'mixte')>Mixte</option>
                </select></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filtrer</button></div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Imputation</th><th>Libellé</th><th>Type</th><th>Seuil</th><th class="text-end">Nb lignes</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($titres as $t)
                <tr>
                    <td><code>{{ $t->imputation }}</code></td>
                    <td>{{ $t->libelle }}</td>
                    <td><span class="badge bg-secondary">{{ ucfirst($t->type_ligne ?? '—') }}</span></td>
                    <td><small>{{ number_format((float) $t->seuil, 0, ',', ' ') }}</small></td>
                    <td class="text-end"><span class="badge bg-info">{{ $t->lignes_count }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('finance.referentiels.titres.show', $t) }}" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fas fa-eye"></i></a>
                        @can('update:titre')
                            <a href="{{ route('finance.referentiels.titres.edit', $t) }}" class="btn btn-sm btn-outline-secondary" title="Modifier"><i class="fas fa-pen"></i></a>
                        @endcan
                        @can('delete:titre')
                            <form action="{{ route('finance.referentiels.titres.destroy', $t) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer le titre « {{ addslashes($t->libelle) }} » ? Cette action est irréversible.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                        @if($t->lignes_count > 0) disabled title="Impossible : {{ $t->lignes_count }} ligne(s) rattachée(s)"@endif>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun titre.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($titres->hasPages())<div class="card-footer">{{ $titres->links() }}</div>@endif
</div>
@endsection
