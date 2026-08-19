@extends('layouts.app')
@section('title', 'Journal des mouvements de stock')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div><h1 class="h3 mb-1"><i class="fas fa-arrow-right-arrow-left text-muted me-2"></i> Mouvements de stock</h1>
        <p class="text-muted mb-0">Journal complet des entrées, sorties, ajustements et transferts.</p></div>
    <a href="{{ route('appro.stock.dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
</div>

<div class="card data-card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small mb-1">Article</label>
            <select name="produit" class="form-select form-select-sm">
                <option value="">Tous</option>
                @foreach($produits as $p)<option value="{{ $p->id }}" @selected(request('produit') == $p->id)>{{ $p->designation }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><label class="form-label small mb-1">Emplacement</label>
            <select name="emplacement" class="form-select form-select-sm">
                <option value="">Tous</option>
                @foreach($emplacements as $e)<option value="{{ $e->id }}" @selected(request('emplacement') == $e->id)>{{ $e->libelle }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><label class="form-label small mb-1">Type</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">Tous</option>
                @foreach($types as $k => $v)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><label class="form-label small mb-1">Depuis</label>
            <input type="date" name="depuis" value="{{ request('depuis') }}" class="form-control form-control-sm"></div>
        <div class="col-md-2"><label class="form-label small mb-1">Jusqu'à</label>
            <input type="date" name="jusqua" value="{{ request('jusqua') }}" class="form-control form-control-sm"></div>
        <div class="col-md-1"><button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter"></i></button></div>
    </form>
</div></div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr>
            <th>Date</th><th>Type</th><th>Article</th><th class="text-end">Quantité</th>
            <th>Emplacement</th><th class="text-end">Stock après</th><th>Réf.</th><th>Par</th>
        </tr></thead>
        <tbody>
        @forelse($mouvements as $m)
            <tr>
                <td><small>{{ $m->created_at?->format('d/m/Y H:i') }}</small></td>
                <td><span class="badge bg-{{ $m->type_couleur }}">{{ $types[$m->type] ?? $m->type }}</span></td>
                <td><code class="small">{{ $m->produit?->code }}</code> {{ $m->produit?->designation }}</td>
                <td class="text-end fw-semibold">{{ $m->type === 'sortie' ? '-' : '+' }}{{ number_format((float) $m->quantite, 3, ',', ' ') }}</td>
                <td><small>
                    @if($m->type === 'transfert')
                        {{ $m->emplacementSource?->libelle }} → {{ $m->emplacement?->libelle }}
                    @elseif($m->type === 'sortie')
                        {{ $m->emplacementSource?->libelle ?? '—' }}
                    @else
                        {{ $m->emplacement?->libelle ?? '—' }}
                    @endif
                </small></td>
                <td class="text-end"><small>{{ number_format((float) $m->stock_apres, 2, ',', ' ') }}</small></td>
                <td><small>{{ $m->reference ?? '—' }}</small></td>
                <td><small>{{ $m->user?->name ?? '—' }}</small></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Aucun mouvement.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($mouvements->hasPages())<div class="card-footer">{{ $mouvements->links() }}</div>@endif
</div>
@endsection
