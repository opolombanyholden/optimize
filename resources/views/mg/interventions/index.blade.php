@extends('layouts.app')
@section('title', 'Interventions')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Moyens Généraux</li>
    <li class="breadcrumb-item active">Interventions</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-tools text-muted me-2"></i> Interventions</h1>
        <p class="text-muted mb-0">Planifiée → En cours → Terminée.</p>
    </div>
    @can('create:intervention')
    <a href="{{ route('mg.interventions.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle</a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Libellé…">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Statut</label>
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($statuts as $k => $v)<option value="{{ $k }}" @selected(request('statut') !== null && request('statut') !== '' && (int) request('statut') === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Type</label>
                <select name="type_intervention" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($types as $k => $v)<option value="{{ $k }}" @selected(request('type_intervention') === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Technicien</label>
                <select name="technicien" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($techniciens as $u)<option value="{{ $u->id }}" @selected(request('technicien') == $u->id)>{{ $u->name }}</option>@endforeach
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
                    <th>Libellé</th>
                    <th>Immobilisation</th>
                    <th>Type</th>
                    <th>Technicien</th>
                    <th>Planifiée</th>
                    <th class="text-end">Coût</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($interventions as $i)
                <tr>
                    <td>
                        <strong>{{ $i->label }}</strong>
                        @if($i->dysfonctionnement)<br><small class="text-muted">↳ {{ Str::limit($i->dysfonctionnement->label, 40) }}</small>@endif
                    </td>
                    <td>{{ $i->immobilisation?->designation ?? '—' }}</td>
                    <td><small>{{ $types[$i->type_intervention] ?? '—' }}</small></td>
                    <td>{{ $i->technicien?->name ?? '—' }}</td>
                    <td><small>{{ $i->date_planifiee?->format('d/m/Y H:i') ?? '—' }}</small></td>
                    <td class="text-end">{{ $i->cout !== null ? number_format((float) $i->cout, 0, ',', ' ') : '—' }}</td>
                    <td><span class="badge bg-{{ $i->statut_couleur }}">{{ $i->statut_libelle }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('mg.interventions.show', $i) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Aucune intervention.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($interventions->hasPages())<div class="card-footer">{{ $interventions->links() }}</div>@endif
</div>
@endsection
