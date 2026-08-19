@extends('layouts.app')
@section('title', 'Lignes (codes analytiques)')
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item">Référentiels</li>
        <li class="breadcrumb-item active">Lignes</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-list me-2 text-muted"></i>Lignes (codes analytiques)</h1>
        <p class="text-muted mb-0">Référentiel des codes analytiques rattachés aux titres.</p>
    </div>
    @can('create:ligne')
        <a href="{{ route('finance.referentiels.lignes.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle ligne</a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5"><label class="form-label">Recherche</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Code, libellé…"></div>
            <div class="col-md-4"><label class="form-label">Titre</label>
                <select name="titre_id" class="form-select">
                    <option value="">Tous</option>
                    @foreach($titres as $t)
                        <option value="{{ $t->id }}" @selected(request('titre_id') == $t->id)>{{ $t->imputation }} — {{ $t->libelle }}</option>
                    @endforeach
                </select></div>
            <div class="col-md-3"><button class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filtrer</button></div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Code analytique</th><th>Libellé</th><th>Titre</th><th>Nature</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($lignes as $l)
                <tr>
                    <td><code>{{ $l->id_codeanalytique }}</code></td>
                    <td>{{ $l->libelle }}</td>
                    <td><small>{{ $l->titre?->libelle ?? '—' }}</small></td>
                    <td><small>{{ $l->nature ?? '—' }}</small></td>
                    <td class="text-end">
                        <a href="{{ route('finance.referentiels.lignes.show', $l) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        @can('update:ligne')<a href="{{ route('finance.referentiels.lignes.edit', $l) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i></a>@endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Aucune ligne.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($lignes->hasPages())<div class="card-footer">{{ $lignes->links() }}</div>@endif
</div>
@endsection
