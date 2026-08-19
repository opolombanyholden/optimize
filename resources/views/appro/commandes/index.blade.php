@extends('layouts.app')
@section('title', 'Commandes fournisseurs')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item active">Commandes</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4 d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-file-invoice text-muted me-2"></i> Commandes fournisseurs</h1>
        <p class="text-muted mb-0">Workflow : brouillon → soumise → approuvée → livrée.</p>
    </div>
    @can('create:commande')
    <a href="{{ route('appro.commandes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouvelle commande
    </a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="N° commande…">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Statut</label>
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($statuts as $k => $v)
                        <option value="{{ $k }}" @selected(request('statut') !== null && request('statut') !== '' && (int) request('statut') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Fournisseur</label>
                <select name="fournisseur" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($fournisseurs as $f)
                        <option value="{{ $f->id }}" @selected(request('fournisseur') == $f->id)>{{ $f->raison_sociale ?: $f->nom }}</option>
                    @endforeach
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
                    <th>N° commande</th>
                    <th>Fournisseur</th>
                    <th>Date</th>
                    <th class="text-center">Articles</th>
                    <th class="text-end">Montant HT</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($commandes as $c)
                <tr>
                    <td><code>{{ $c->numero_commande }}</code></td>
                    <td>
                        <strong>{{ $c->fournisseur_libelle ?: '—' }}</strong>
                    </td>
                    <td><small>{{ $c->date_commande?->format('d/m/Y') }}</small></td>
                    <td class="text-center">{{ $c->lignes->count() }}</td>
                    <td class="text-end fw-bold">{{ number_format((float) $c->montant_ht, 0, ',', ' ') }}</td>
                    <td><span class="badge bg-{{ $c->statut_couleur }}">{{ $c->statut_libelle }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('appro.commandes.show', $c) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        @if($c->peutEtreModifiee())
                            <a href="{{ route('appro.commandes.edit', $c) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i></a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucune commande. Créez la première.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($commandes->hasPages())<div class="card-footer">{{ $commandes->links() }}</div>@endif
</div>
@endsection
