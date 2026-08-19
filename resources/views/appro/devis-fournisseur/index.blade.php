@extends('layouts.app')
@section('title', 'Devis fournisseur')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item active">Devis fournisseur</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-file-signature text-muted me-2"></i> Devis fournisseur</h1>
        <p class="text-muted mb-0">Devis reçus des fournisseurs suite à consultation, comparés puis sélectionnés avec motivation.</p>
    </div>
    <a href="{{ route('appro.devis-fournisseur.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouveau devis</a>
</div>

<div class="card data-card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small mb-1">Recherche</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="N° devis…"></div>
        <div class="col-md-3"><label class="form-label small mb-1">Statut</label>
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
            <th>N°</th><th>Commande</th><th>Fournisseur</th>
            <th>Reçu le</th><th class="text-end">Montant TTC</th>
            <th>Statut</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        @forelse($devis as $d)
            <tr>
                <td><code>{{ $d->numero }}</code></td>
                <td><a href="{{ route('appro.commandes.show', $d->commande) }}"><code class="small">{{ $d->commande?->numero_commande }}</code></a></td>
                <td>{{ $d->fournisseur?->raison_sociale ?? $d->fournisseur?->nom ?? '—' }}</td>
                <td><small>{{ $d->date_reception?->format('d/m/Y') }}</small></td>
                <td class="text-end fw-semibold">{{ number_format((float) $d->montant_ttc, 0, ',', ' ') }}</td>
                <td><span class="badge bg-{{ $d->statut_couleur }}">{{ $d->statut_libelle }}</span></td>
                <td class="text-end"><a href="{{ route('appro.devis-fournisseur.show', $d) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucun devis enregistré.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($devis->hasPages())<div class="card-footer">{{ $devis->links() }}</div>@endif
</div>
@endsection
