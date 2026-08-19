@extends('layouts.app')
@section('title', 'Livraisons fournisseur')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item active">Livraisons fournisseur</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-truck-loading text-muted me-2"></i> Livraisons fournisseur</h1>
        <p class="text-muted mb-0">Réceptions issues des commandes fournisseur approuvées.</p>
    </div>
    <a href="{{ route('appro.livraisons-fournisseur.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle livraison</a>
</div>

<div class="card data-card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small mb-1">Recherche</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="N° livraison, BL…"></div>
        <div class="col-md-3"><label class="form-label small mb-1">Type</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">Tous</option>
                @foreach($types as $k => $v)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><label class="form-label small mb-1">Depuis</label>
            <input type="date" name="depuis" value="{{ request('depuis') }}" class="form-control form-control-sm"></div>
        <div class="col-md-2"><label class="form-label small mb-1">Jusqu'à</label>
            <input type="date" name="jusqua" value="{{ request('jusqua') }}" class="form-control form-control-sm"></div>
        <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter"></i></button></div>
    </form>
</div></div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr>
            <th>N°</th><th>Date</th><th>Commande</th><th>Fournisseur</th>
            <th>BL fournisseur</th><th>Réception</th>
            <th class="text-center">Lignes</th><th>Type</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        @forelse($livraisons as $l)
            <tr>
                <td><code>{{ $l->numero }}</code></td>
                <td><small>{{ $l->date_livraison?->format('d/m/Y') }}</small></td>
                <td><a href="{{ route('appro.commandes.show', $l->commande) }}"><code class="small">{{ $l->commande?->numero_commande }}</code></a></td>
                <td><small>{{ $l->commande?->fournisseur?->raison_sociale ?? $l->commande?->fournisseur?->nom ?? '—' }}</small></td>
                <td><small>{{ $l->bon_livraison_ref ?? '—' }}</small></td>
                <td><small>{{ $l->emplacementReception?->libelle ?? '—' }}</small></td>
                <td class="text-center">{{ $l->lignes()->count() }}</td>
                <td><span class="badge bg-{{ $l->type_couleur }}">{{ \App\Models\LivraisonFournisseur::TYPES[$l->type] ?? $l->type }}</span></td>
                <td class="text-end"><a href="{{ route('appro.livraisons-fournisseur.show', $l) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">Aucune livraison enregistrée.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($livraisons->hasPages())<div class="card-footer">{{ $livraisons->links() }}</div>@endif
</div>
@endsection
