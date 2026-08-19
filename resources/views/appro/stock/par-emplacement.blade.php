@extends('layouts.app')
@section('title', 'Stock par emplacement')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div><h1 class="h3 mb-1"><i class="fas fa-warehouse text-muted me-2"></i> Stock par emplacement</h1></div>
    <a href="{{ route('appro.stock.dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
</div>

<div class="card data-card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4"><label class="form-label small mb-1">Article</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Code, désignation…"></div>
        <div class="col-md-4"><label class="form-label small mb-1">Emplacement</label>
            <select name="emplacement" class="form-select form-select-sm">
                <option value="">Tous</option>
                @foreach($emplacements as $e)
                    <option value="{{ $e->id }}" @selected(request('emplacement') == $e->id)>{{ $e->chemin }}</option>
                    @foreach($e->enfants as $ee)
                        <option value="{{ $ee->id }}" @selected(request('emplacement') == $ee->id)>— {{ $ee->libelle }}</option>
                    @endforeach
                @endforeach
            </select></div>
        <div class="col-md-2"><label class="form-label small mb-1">Filtre</label>
            <select name="non_nul" class="form-select form-select-sm">
                <option value="">Tous</option>
                <option value="oui" @selected(request('non_nul') === 'oui')>Non vides</option>
            </select></div>
        <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter"></i></button></div>
    </form>
</div></div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr>
            <th>Emplacement</th><th>Article</th><th>Famille</th><th class="text-end">Quantité</th><th>Unité</th>
        </tr></thead>
        <tbody>
        @forelse($stocks as $s)
            <tr>
                <td><i class="fas fa-warehouse text-secondary me-1"></i><strong>{{ $s->emplacement?->chemin }}</strong></td>
                <td><code class="small">{{ $s->produit?->code }}</code> {{ $s->produit?->designation }}</td>
                <td><small>{{ $s->produit?->famille?->libelle ?? '—' }}</small></td>
                <td class="text-end fw-semibold">{{ number_format((float) $s->quantite, 3, ',', ' ') }}</td>
                <td><small>{{ $s->produit?->unite_mesure ?? '' }}</small></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Aucun stock enregistré.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($stocks->hasPages())<div class="card-footer">{{ $stocks->links() }}</div>@endif
</div>
@endsection
