@extends('layouts.app')
@section('title', 'Dysfonctionnements')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Moyens Généraux</li>
    <li class="breadcrumb-item active">Dysfonctionnements</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-exclamation-triangle text-muted me-2"></i> Dysfonctionnements</h1>
        <p class="text-muted mb-0">Signaler → Prendre en charge → Résoudre → Fermer.</p>
    </div>
    @can('create:dysfonctionnement')
    <a href="{{ route('mg.dysfonctionnements.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Signaler</a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Intitulé…">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Statut</label>
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($statuts as $k => $v)<option value="{{ $k }}" @selected(request('statut') !== null && request('statut') !== '' && (int) request('statut') === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Priorité</label>
                <select name="priorite" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    @foreach($priorites as $k => $v)<option value="{{ $k }}" @selected(request('priorite') === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Immobilisation</label>
                <select name="immobilisation" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    @foreach($immobilisations as $imm)<option value="{{ $imm->id }}" @selected(request('immobilisation') == $imm->id)>{{ $imm->designation }}</option>@endforeach
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
                    <th>Intitulé</th>
                    <th>Immobilisation</th>
                    <th>Priorité</th>
                    <th>Signalement</th>
                    <th>Statut</th>
                    <th class="text-center">Interventions</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($dysfonctionnements as $d)
                <tr>
                    <td>
                        <strong>{{ $d->label }}</strong>
                        @if($d->type)<br><small class="text-muted">{{ $d->type->libelle }}</small>@endif
                    </td>
                    <td>{{ $d->immobilisation?->designation ?? '—' }}</td>
                    <td><span class="badge bg-{{ $d->priorite === 'critique' ? 'danger' : ($d->priorite === 'haute' ? 'warning text-dark' : 'secondary') }}">{{ $priorites[$d->priorite] ?? '—' }}</span></td>
                    <td><small>{{ $d->date_signalement?->format('d/m/Y') }}</small></td>
                    <td><span class="badge bg-{{ $d->statut_couleur }}">{{ $d->statut_libelle }}</span></td>
                    <td class="text-center">{{ $d->interventions_count ?? $d->interventions->count() }}</td>
                    <td class="text-end">
                        <a href="{{ route('mg.dysfonctionnements.show', $d) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun dysfonctionnement.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($dysfonctionnements->hasPages())<div class="card-footer">{{ $dysfonctionnements->links() }}</div>@endif
</div>
@endsection
