@extends('layouts.app')
@section('title', 'Catalogue biens & services')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Référentiels</li>
    <li class="breadcrumb-item active">Catalogue</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-book text-muted me-2"></i> Catalogue biens & services</h1>
        <p class="text-muted mb-0">
            <span class="badge bg-danger me-1">{{ $ruptures }}</span> ruptures ·
            <span class="badge bg-warning text-dark ms-1">{{ $alertes }}</span> alertes stock
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('referentiel.familles.index') }}" class="btn btn-outline-secondary"><i class="fas fa-sitemap me-1"></i> Familles</a>
        <a href="{{ route('referentiel.catalogue.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvel article</a>
    </div>
</div>

<div class="card data-card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1">Recherche</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Code, désignation…">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Famille</label>
            <select name="famille" class="form-select form-select-sm">
                <option value="">Toutes</option>
                @foreach($familles as $f)
                    <option value="{{ $f->id }}" @selected(request('famille') == $f->id)>{{ $f->libelle }}</option>
                    @foreach($f->enfants as $e)
                        <option value="{{ $e->id }}" @selected(request('famille') == $e->id)>— {{ $e->libelle }}</option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Type</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">Tous</option>
                @foreach($types as $k => $v)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Filtre</label>
            <select name="alerte" class="form-select form-select-sm">
                <option value="">—</option>
                <option value="oui" @selected(request('alerte') === 'oui')>Uniquement alertes stock</option>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter"></i> Filtrer</button></div>
    </form>
</div></div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr>
            <th>Code</th><th>Désignation</th><th>Famille</th><th>Type</th>
            <th class="text-end">Prix unit.</th><th class="text-end">Stock</th><th>État</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        @forelse($articles as $a)
            <tr>
                <td><code class="small">{{ $a->code ?: '—' }}</code></td>
                <td><strong>{{ $a->designation }}</strong></td>
                <td><small>{{ $a->famille?->chemin ?? '—' }}</small></td>
                <td><span class="badge bg-{{ $a->type_article === 'service' ? 'info' : 'secondary' }}">{{ $types[$a->type_article] ?? '—' }}</span></td>
                <td class="text-end">{{ number_format((float) $a->prix_unitaire, 0, ',', ' ') }}</td>
                <td class="text-end">{{ $a->est_stockable ? number_format((float) $a->stock_actuel, 2, ',', ' ') . ' ' . ($a->unite_mesure ?? '') : '—' }}</td>
                <td>
                    @if($a->etat_stock === 'non_applicable')
                        <span class="text-muted small">n/a</span>
                    @else
                        <span class="badge bg-{{ $a->etat_stock_couleur }}">{{ ucfirst($a->etat_stock) }}</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('referentiel.catalogue.show', $a) }}" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('referentiel.catalogue.edit', $a) }}" class="btn btn-sm btn-outline-secondary" title="Modifier"><i class="fas fa-pen"></i></a>
                    <form action="{{ route('referentiel.catalogue.destroy', $a) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?');">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Aucun article dans le catalogue.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($articles->hasPages())<div class="card-footer">{{ $articles->links() }}</div>@endif
</div>
@endsection
