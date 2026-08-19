@extends('layouts.app')
@section('title', 'Dépenses & Recettes')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item active">Dépenses & Recettes</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-exchange-alt text-muted me-2"></i> Dépenses & Recettes</h1>
        <p class="text-muted mb-0">Ordres de recette et ordonnances de paiement — matérialisés au Grand Livre à l'exécution.</p>
    </div>
    @can('create:operation')
    <a href="{{ route('finance.ordres.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouvel ordre
    </a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Numéro, libellé…">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Sens</label>
                <select name="sens" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="depense" @selected(request('sens') === 'depense')>Dépense</option>
                    <option value="recette" @selected(request('sens') === 'recette')>Recette</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Statut</label>
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($statuts as $k => $v)
                        <option value="{{ $k }}" @selected(request('statut') !== null && request('statut') !== '' && (int) request('statut') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Exercice</label>
                <select name="exercice" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($exercices as $e)
                        <option value="{{ $e->id }}" @selected(request('exercice') == $e->id)>{{ $e->libelle ?? $e->exercice }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Modèle</label>
                <select name="modele" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($modeles as $m)
                        <option value="{{ $m->id }}" @selected(request('modele') == $m->id)>{{ $m->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>N° ordre</th>
                    <th>Modèle</th>
                    <th>Sens</th>
                    <th>Ligne budgétaire</th>
                    <th>Libellé</th>
                    <th class="text-end">Montant</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($ordres as $o)
                <tr>
                    <td><code>{{ $o->numero_ordre }}</code>
                        <br><small class="text-muted">{{ $o->created_at?->format('d/m/Y') }}</small>
                    </td>
                    <td><small>{{ $o->modele?->libelle }}</small></td>
                    <td>
                        @if($o->sens === 'depense')
                            <span class="badge bg-danger"><i class="fas fa-arrow-down me-1"></i>Dépense</span>
                        @else
                            <span class="badge bg-success"><i class="fas fa-arrow-up me-1"></i>Recette</span>
                        @endif
                    </td>
                    <td>
                        @if($o->budgetLigne)
                            <small><code>{{ $o->budgetLigne->id_budgetligne }}</code></small>
                            <br><small class="text-muted">{{ $o->budgetLigne->ligne?->libelle ?? $o->budgetLigne->commentaire }}</small>
                        @else
                            <em class="text-muted small">—</em>
                        @endif
                    </td>
                    <td><small>{{ \Illuminate\Support\Str::limit($o->libelle, 40) ?: '—' }}</small></td>
                    <td class="text-end fw-bold">{{ number_format((float) $o->montant, 0, ',', ' ') }}</td>
                    <td><span class="badge bg-{{ $o->statut_couleur }}">{{ $o->statut_libelle }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('finance.ordres.show', $o) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('update:operation')
                        @if($o->peutEtreModifie())
                            <a href="{{ route('finance.ordres.edit', $o) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                <i class="fas fa-pen"></i>
                            </a>
                        @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Aucun ordre. Créez le premier.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($ordres->hasPages())
        <div class="card-footer">{{ $ordres->links() }}</div>
    @endif
</div>
@endsection
