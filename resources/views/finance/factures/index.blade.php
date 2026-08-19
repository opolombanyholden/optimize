@extends('layouts.app')
@section('title', 'Factures')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item active">Factures</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-file-invoice me-2 text-muted"></i>Factures</h1>
        <p class="text-muted mb-0">Factures fournisseurs (dépenses) et factures clients (recettes).</p>
    </div>
    @can('create:facture')
        <div class="btn-group">
            <a href="{{ route('finance.factures.create', ['sens' => 'depense']) }}" class="btn btn-outline-danger">
                <i class="fas fa-arrow-down me-1"></i> Facture fournisseur
            </a>
            <a href="{{ route('finance.factures.create', ['sens' => 'recette']) }}" class="btn btn-outline-success">
                <i class="fas fa-arrow-up me-1"></i> Facture client
            </a>
        </div>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Recherche</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Numéro, objet…">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sens</label>
                <select name="sens" class="form-select">
                    <option value="">Tous</option>
                    <option value="depense" @selected(request('sens') === 'depense')>Dépense</option>
                    <option value="recette" @selected(request('sens') === 'recette')>Recette</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    @foreach(\App\Models\Facture::STATUTS as $k => $v)
                        <option value="{{ $k }}" @selected(request('statut') !== null && request('statut') !== '' && (int) request('statut') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Du</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Au</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-1">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Date</th>
                    <th>Sens</th>
                    <th>Tiers</th>
                    <th>Objet</th>
                    <th class="text-end">Montant TTC</th>
                    <th class="text-end">Reste</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($factures as $f)
                <tr>
                    <td><code>{{ $f->numero }}</code></td>
                    <td><small>{{ $f->date_emission?->format('d/m/Y') }}</small></td>
                    <td>
                        @if($f->sens === 'depense')
                            <span class="badge bg-danger"><i class="fas fa-arrow-down me-1"></i>Dépense</span>
                        @else
                            <span class="badge bg-success"><i class="fas fa-arrow-up me-1"></i>Recette</span>
                        @endif
                    </td>
                    <td>
                        @php $t = $f->tiersResolu(); @endphp
                        @if($t)
                            <small>{{ $t->nom ?? $t->raison_sociale ?? '—' }}</small>
                        @else
                            <small class="text-muted">—</small>
                        @endif
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($f->objet, 35) }}</td>
                    <td class="text-end">{{ number_format((float) $f->montant_ttc, 0, ',', ' ') }}</td>
                    <td class="text-end">
                        @if($f->montant_reste_a > 0)
                            <strong class="text-warning">{{ number_format($f->montant_reste_a, 0, ',', ' ') }}</strong>
                        @else
                            <i class="fas fa-check text-success"></i>
                        @endif
                    </td>
                    <td><span class="badge bg-{{ $f->statut_couleur }}">{{ $f->statut_libelle }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('finance.factures.show', $f) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">Aucune facture.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($factures->hasPages())<div class="card-footer">{{ $factures->links() }}</div>@endif
</div>
@endsection
