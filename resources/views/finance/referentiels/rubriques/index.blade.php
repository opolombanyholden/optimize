@extends('layouts.app')
@section('title', 'Rubriques d\'opérations')
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item">Référentiels</li>
        <li class="breadcrumb-item active">Rubriques</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-tags me-2 text-muted"></i>Rubriques d'opérations</h1>
        <p class="text-muted mb-0">Référentiel des rubriques utilisables comme détail dans les opérations.</p>
    </div>
    @can('create:rubrique_operation')
        <a href="{{ route('finance.referentiels.rubriques.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle rubrique</a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Recherche</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Code, libellé…"></div>
            <div class="col-md-3"><label class="form-label">Ligne</label>
                <select name="ligne_id" class="form-select">
                    <option value="">Toutes</option>
                    @foreach($lignes as $l)
                        <option value="{{ $l->id }}" @selected(request('ligne_id') == $l->id)>{{ $l->id_codeanalytique }} — {{ \Illuminate\Support\Str::limit($l->libelle, 40) }}</option>
                    @endforeach
                </select></div>
            <div class="col-md-2"><label class="form-label">Sens</label>
                <select name="sens" class="form-select">
                    <option value="">Tous</option>
                    @foreach(\App\Models\RubriqueOperation::SENS as $k => $v)
                        <option value="{{ $k }}" @selected(request('sens') === $k)>{{ $v }}</option>
                    @endforeach
                </select></div>
            <div class="col-md-2"><label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="1" @selected(request('statut') === '1')>Actif</option>
                    <option value="0" @selected(request('statut') === '0')>Archivé</option>
                </select></div>
            <div class="col-md-1"><button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i></button></div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th><th>Libellé</th><th>Ligne</th><th>Sens</th><th>Catégorie</th><th>Statut</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rubriques as $r)
                <tr>
                    <td><code>{{ $r->code }}</code></td>
                    <td><strong>{{ $r->libelle }}</strong></td>
                    <td>
                        @if($r->ligne)
                            <small><code>{{ $r->ligne->id_codeanalytique }}</code> {{ \Illuminate\Support\Str::limit($r->ligne->libelle, 25) }}</small>
                        @else
                            <small class="text-muted">—</small>
                        @endif
                    </td>
                    <td>
                        @if($r->sens === 'depense')<span class="badge bg-danger">Dépense</span>
                        @elseif($r->sens === 'recette')<span class="badge bg-success">Recette</span>
                        @else<span class="badge bg-info">Mixte</span>@endif
                    </td>
                    <td><small>{{ $r->categorie ?? '—' }}</small></td>
                    <td>
                        @if($r->statut)<span class="badge bg-success">Actif</span>
                        @else<span class="badge bg-secondary">Archivé</span>@endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('finance.referentiels.rubriques.show', $r) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        @can('update:rubrique_operation')<a href="{{ route('finance.referentiels.rubriques.edit', $r) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i></a>@endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucune rubrique.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($rubriques->hasPages())<div class="card-footer">{{ $rubriques->links() }}</div>@endif
</div>
@endsection
