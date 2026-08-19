@extends('layouts.app')
@section('title', 'Immobilisations')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Moyens Généraux</li>
    <li class="breadcrumb-item active">Immobilisations</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-building text-muted me-2"></i> Immobilisations</h1>
        <p class="text-muted mb-0">Inventaire des biens immobilisés et suivi des amortissements.</p>
    </div>
    @can('create:immobilisation')
    <a href="{{ route('mg.immobilisations.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle</a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Code, désignation, localisation…">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Catégorie</label>
                <select name="categorie" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    @foreach($categories as $k => $v)<option value="{{ $k }}" @selected(request('categorie') === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">État</label>
                <select name="etat" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($etats as $k => $v)<option value="{{ $k }}" @selected(request('etat') === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Entité</label>
                <select name="entite" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    @foreach($entites as $e)<option value="{{ $e->id }}" @selected(request('entite') == $e->id)>{{ $e->libelle }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter"></i> Filtrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Désignation</th>
                    <th>Catégorie</th>
                    <th>Localisation</th>
                    <th class="text-end">Valeur acq.</th>
                    <th class="text-end">VNC</th>
                    <th>État</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($immobilisations as $i)
                <tr>
                    <td><code class="small">{{ $i->code ?: '—' }}</code></td>
                    <td><strong>{{ $i->designation }}</strong></td>
                    <td><small>{{ $categories[$i->categorie] ?? $i->categorie ?? '—' }}</small></td>
                    <td><small>{{ $i->localisation ?: '—' }}</small></td>
                    <td class="text-end">{{ number_format((float) $i->valeur_acquisition, 0, ',', ' ') }}</td>
                    <td class="text-end fw-semibold">{{ number_format((float) $i->valeur_nette_comptable, 0, ',', ' ') }}</td>
                    <td><span class="badge bg-secondary">{{ $etats[$i->etat] ?? $i->etat ?? '—' }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('mg.immobilisations.show', $i) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        @can('update:immobilisation')
                            <a href="{{ route('mg.immobilisations.edit', $i) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i></a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Aucune immobilisation.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($immobilisations->hasPages())<div class="card-footer">{{ $immobilisations->links() }}</div>@endif
</div>
@endsection
