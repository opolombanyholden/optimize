@extends('layouts.app')
@section('title', 'Comptes de trésorerie')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item active">Comptes</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-wallet me-2 text-muted"></i> Comptes de trésorerie</h1>
        <p class="text-muted mb-0">Bancaires, caisses, wallets électroniques — tout le cash de l'entreprise.</p>
    </div>
    @can('create:compte')
    <a href="{{ route('finance.comptes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouveau compte
    </a>
    @endcan
</div>

{{-- ═════════ KPI trésorerie ═════════ --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card data-card border-start border-3 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small text-uppercase"><i class="fas fa-building-columns me-1"></i> Bancaire</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($stats['total_banque'], 0, ',', ' ') }}</div>
                        <small class="text-muted">XAF · virtuel</small>
                    </div>
                    <i class="fas fa-building-columns fa-2x text-primary opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card data-card border-start border-3 border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small text-uppercase"><i class="fas fa-cash-register me-1"></i> Caisse</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($stats['total_caisse'], 0, ',', ' ') }}</div>
                        <small class="text-muted">XAF · numéraire réel</small>
                    </div>
                    <i class="fas fa-cash-register fa-2x text-warning opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card data-card border-start border-3 border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small text-uppercase"><i class="fas fa-mobile-screen-button me-1"></i> Électronique</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($stats['total_electronique'], 0, ',', ' ') }}</div>
                        <small class="text-muted">XAF · e-wallet / Mobile Money</small>
                    </div>
                    <i class="fas fa-mobile-screen-button fa-2x text-info opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═════════ Filtres ═════════ --}}
<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Code, nom, RIB…">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach(\App\Models\Compte::TYPES as $k => $v)
                        <option value="{{ $k }}" @selected(request('type') == $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-check form-switch mt-4">
                    <input type="checkbox" name="inactifs" value="1" class="form-check-input" id="inactifs" @checked(request()->boolean('inactifs'))>
                    <label class="form-check-label" for="inactifs">Comptes inactifs</label>
                </div>
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
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>RIB / N°</th>
                    <th class="text-end">Solde initial</th>
                    <th class="text-end">Solde actuel</th>
                    <th>Devise</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($comptes as $c)
                <tr>
                    <td><code>{{ $c->code ?: '—' }}</code></td>
                    <td>
                        <strong>{{ $c->nom }}</strong>
                        @if($c->responsable)<br><small class="text-muted"><i class="fas fa-user me-1"></i>{{ $c->responsable }}</small>@endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $c->type_couleur }}">
                            <i class="fas {{ $c->type_icone }} me-1"></i> {{ $c->type_libelle }}
                        </span>
                    </td>
                    <td><small><code>{{ $c->rib ?: '—' }}</code></small>
                        @if($c->domiciliation)<br><small class="text-muted">{{ $c->domiciliation }}</small>@endif
                    </td>
                    <td class="text-end text-muted">{{ number_format((float) $c->solde_initial, 0, ',', ' ') }}</td>
                    <td class="text-end fw-bold {{ $c->solde < 0 ? 'text-danger' : '' }}">{{ number_format((float) $c->solde, 0, ',', ' ') }}</td>
                    <td><small>{{ $c->devise ?: 'XAF' }}</small></td>
                    <td>
                        @if($c->actif)<span class="badge bg-success">Actif</span>
                        @else<span class="badge bg-secondary">Inactif</span>@endif
                    </td>
                    <td class="text-end">
                        @can('read:compte')
                        <a href="{{ route('finance.comptes.show', $c) }}" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fas fa-eye"></i></a>
                        @endcan
                        @can('update:compte')
                        <a href="{{ route('finance.comptes.edit', $c) }}" class="btn btn-sm btn-outline-secondary" title="Modifier"><i class="fas fa-pen"></i></a>
                        @endcan
                        @can('delete:compte')
                        <form action="{{ route('finance.comptes.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce compte ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">Aucun compte. Créez le premier.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($comptes->hasPages())
        <div class="card-footer">{{ $comptes->links() }}</div>
    @endif
</div>
@endsection
