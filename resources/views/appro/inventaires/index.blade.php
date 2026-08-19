@extends('layouts.app')
@section('title', 'Inventaires')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div><h1 class="h3 mb-1"><i class="fas fa-clipboard-check text-muted me-2"></i> Inventaires</h1>
        <p class="text-muted mb-0">Comptages physiques et ajustements de stock.</p></div>
    <a href="{{ route('appro.inventaires.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvel inventaire</a>
</div>

<div class="card data-card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4"><label class="form-label small mb-1">Statut</label>
            <select name="statut" class="form-select form-select-sm">
                <option value="">Tous</option>
                @foreach($statuts as $k => $v)<option value="{{ $k }}" @selected(request('statut') !== null && request('statut') !== '' && (int) request('statut') === $k)>{{ $v }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter"></i></button></div>
    </form>
</div></div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr>
            <th>N°</th><th>Libellé</th><th>Périmètre</th><th>Date prévue</th><th>Responsable</th><th>Statut</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        @forelse($inventaires as $i)
            <tr>
                <td><code>{{ $i->numero }}</code></td>
                <td>{{ $i->libelle }}</td>
                <td><small>{{ $i->emplacement?->chemin ?? 'Tous emplacements' }}</small></td>
                <td><small>{{ $i->date_prevue?->format('d/m/Y') }}</small></td>
                <td><small>{{ $i->responsable?->name ?? '—' }}</small></td>
                <td><span class="badge bg-{{ $i->statut_couleur }}">{{ $i->statut_libelle }}</span></td>
                <td class="text-end"><a href="{{ route('appro.inventaires.show', $i) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucun inventaire.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($inventaires->hasPages())<div class="card-footer">{{ $inventaires->links() }}</div>@endif
</div>
@endsection
