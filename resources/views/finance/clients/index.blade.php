@extends('layouts.app')
@section('title', 'Clients')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item active">Clients</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-handshake me-2 text-muted"></i>Clients</h1>
        <p class="text-muted mb-0">Annuaire des clients (recettes / factures émises).</p>
    </div>
    @can('create:client')
        <a href="{{ route('finance.clients.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouveau client</a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Recherche</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Raison sociale, code, NIF…">
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="1" @selected(request('statut') === '1')>Actif</option>
                    <option value="0" @selected(request('statut') === '0')>Inactif</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filtrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Raison sociale</th>
                    <th>NIF / RCCM</th>
                    <th>Contact</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($clients as $c)
                <tr>
                    <td><code>{{ $c->code }}</code></td>
                    <td>
                        <strong>{{ $c->raison_sociale }}</strong>
                        @if($c->forme_juridique)<small class="text-muted ms-1">{{ $c->forme_juridique }}</small>@endif
                    </td>
                    <td><small>{{ $c->nif ?? '—' }} / {{ $c->rccm ?? '—' }}</small></td>
                    <td><small>{{ $c->email ?? $c->telephone ?? '—' }}</small></td>
                    <td>
                        @if($c->statut)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-secondary">Inactif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('finance.clients.show', $c) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        @can('update:client')
                            <a href="{{ route('finance.clients.edit', $c) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i></a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun client. <a href="{{ route('finance.clients.create') }}">Créer le premier →</a></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($clients->hasPages())<div class="card-footer">{{ $clients->links() }}</div>@endif
</div>
@endsection
