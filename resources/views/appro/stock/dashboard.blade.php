@extends('layouts.app')
@section('title', 'Tableau de bord — Stock')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div><h1 class="h3 mb-1"><i class="fas fa-boxes-stacked text-muted me-2"></i> Gestion des stocks</h1>
        <p class="text-muted mb-0">Vue d'ensemble : alertes, mouvements, inventaires.</p></div>
    <div class="d-flex gap-2">
        <a href="{{ route('appro.stock.emplacements') }}" class="btn btn-outline-secondary"><i class="fas fa-warehouse me-1"></i> Par emplacement</a>
        <a href="{{ route('appro.stock.mouvements') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right-arrow-left me-1"></i> Mouvements</a>
        <a href="{{ route('appro.inventaires.index') }}" class="btn btn-primary"><i class="fas fa-clipboard-check me-1"></i> Inventaires</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">Ruptures</small>
        <h3 class="mb-0 text-danger">{{ $nbRuptures }}</h3></div></div>
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">Alertes</small>
        <h3 class="mb-0 text-warning">{{ $nbAlertes }}</h3></div></div>
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">Valeur du stock (XAF)</small>
        <h3 class="mb-0">{{ number_format((float) $valeurStock, 0, ',', ' ') }}</h3></div></div>
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">Emplacements actifs</small>
        <h3 class="mb-0">{{ $nbEmplacements }}</h3></div></div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card data-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-triangle-exclamation text-danger me-2"></i> Ruptures & alertes</h6>
            </div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Article</th><th>Famille</th><th class="text-end">Stock</th><th>État</th></tr></thead>
                <tbody>
                    @foreach($ruptures as $p)
                        <tr>
                            <td><code class="small">{{ $p->code }}</code> {{ $p->designation }}</td>
                            <td><small>{{ $p->famille?->libelle ?? '—' }}</small></td>
                            <td class="text-end fw-bold text-danger">{{ number_format((float) $p->stock_actuel, 2, ',', ' ') }}</td>
                            <td><span class="badge bg-danger">Rupture</span></td>
                        </tr>
                    @endforeach
                    @foreach($alertes->reject(fn($a) => $a->etat_stock === 'rupture')->take(10) as $p)
                        <tr>
                            <td><code class="small">{{ $p->code }}</code> {{ $p->designation }}</td>
                            <td><small>{{ $p->famille?->libelle ?? '—' }}</small></td>
                            <td class="text-end fw-bold text-warning">{{ number_format((float) $p->stock_actuel, 2, ',', ' ') }}</td>
                            <td><span class="badge bg-warning text-dark">Alerte</span></td>
                        </tr>
                    @endforeach
                    @if($ruptures->isEmpty() && $alertes->isEmpty())
                        <tr><td colspan="4" class="text-center text-muted py-3">Aucune alerte 🎉</td></tr>
                    @endif
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card data-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-history me-2"></i> Derniers mouvements</h6>
                <a href="{{ route('appro.stock.mouvements') }}" class="small">Tout voir →</a>
            </div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Article</th><th>Type</th><th class="text-end">Qté</th><th>Emplacement</th></tr></thead>
                <tbody>
                    @foreach($derniers as $m)
                        <tr>
                            <td><small>{{ $m->created_at?->format('d/m H:i') }}</small></td>
                            <td><small>{{ $m->produit?->designation }}</small></td>
                            <td><span class="badge bg-{{ $m->type_couleur }}">{{ \App\Models\ProduitMouvement::TYPES[$m->type] ?? $m->type }}</span></td>
                            <td class="text-end fw-semibold">{{ $m->type === 'sortie' ? '-' : '+' }}{{ number_format((float) $m->quantite, 2, ',', ' ') }}</td>
                            <td><small>{{ $m->emplacement?->libelle ?? '—' }}</small></td>
                        </tr>
                    @endforeach
                    @if($derniers->isEmpty())
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucun mouvement.</td></tr>
                    @endif
                </tbody>
            </table></div>
        </div>
    </div>
</div>
@endsection
