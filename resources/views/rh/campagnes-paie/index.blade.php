@extends('layouts.app')

@section('title', 'Campagnes de paie')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item active">Campagnes de paie</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1><i class="fas fa-rocket me-2 text-primary"></i> Campagnes de paie</h1>
        <p class="text-muted mb-0">Traitement collectif mensuel — simulations et campagnes réelles.</p>
    </div>
    @can('create:paie')
    <a href="{{ route('rh.campagnes-paie.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouvelle campagne
    </a>
    @endcan
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Année</label>
                <select name="annee" class="form-select" onchange="this.form.submit()">
                    <option value="">Toutes</option>
                    @for($y = now()->year + 1; $y >= 2020; $y--)
                        <option value="{{ $y }}" @selected(request('annee') == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous</option>
                    @foreach(\App\Models\CampagnePaie::STATUTS as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('statut') !== null && request('statut') !== '' && (int)request('statut') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="simulation" class="form-select" onchange="this.form.submit()">
                    <option value="">Toutes</option>
                    <option value="0" @selected(request('simulation') === '0')>Réelle</option>
                    <option value="1" @selected(request('simulation') === '1')>Simulation</option>
                </select>
            </div>
            <div class="col-md-3">
                <a href="{{ route('rh.campagnes-paie.index') }}" class="btn btn-outline-secondary"><i class="fas fa-xmark me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list-ul me-2 text-muted"></i> Liste</h5>
        <span class="text-muted small">{{ $campagnes->total() }} campagne(s)</span>
    </div>
    <div class="card-body p-0">
        @if($campagnes->isEmpty())
            <div class="empty-state"><i class="fas fa-rocket"></i><p>Aucune campagne.</p></div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Période</th>
                            <th>Libellé</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th class="text-end">Bulletins</th>
                            <th class="text-end">Masse brute</th>
                            <th class="text-end">Masse nette</th>
                            <th>Créée par</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($campagnes as $c)
                        <tr>
                            <td><code>{{ $c->code }}</code></td>
                            <td>{{ $c->periode_libelle }}</td>
                            <td>{{ $c->libelle }}</td>
                            <td>
                                @if($c->simulation)
                                    <span class="badge bg-info-subtle text-info"><i class="fas fa-vial me-1"></i> Simulation</span>
                                @else
                                    <span class="badge bg-primary"><i class="fas fa-circle-check me-1"></i> Réelle</span>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $c->statut_couleur }}">{{ $c->statut_libelle }}</span></td>
                            <td class="text-end">{{ $c->nombre_bulletins }}</td>
                            <td class="text-end font-monospace">{{ number_format($c->masse_brute, 0, ',', ' ') }}</td>
                            <td class="text-end font-monospace">{{ number_format($c->masse_nette, 0, ',', ' ') }}</td>
                            <td class="small">{{ $c->createur?->name ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('rh.campagnes-paie.show', $c) }}" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fas fa-eye"></i></a>
                                @if($c->est_modifiable)
                                <form action="{{ route('rh.campagnes-paie.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette campagne et tous ses bulletins ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if($campagnes->hasPages())
<div class="d-flex justify-content-center mt-4">{{ $campagnes->withQueryString()->links() }}</div>
@endif
@endsection
