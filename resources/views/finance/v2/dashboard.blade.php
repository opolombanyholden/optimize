@extends('layouts.app')

@section('title', 'Finance V2 — Dashboard')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Finance V2</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-scale-balanced me-2 text-primary"></i>Finance V2 — Modèle initial</h1>
        <p class="text-muted mb-0">
            Aligné au cahier des charges original OPTIMIZE Finance (schéma budgets + sources + transactions + modifs).
            @if($exercice)· Exercice : <strong>{{ $exercice->label ?? $exercice->libelle }}</strong>@endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.v2.sources.index') }}" class="btn btn-outline-primary">Sources</a>
        <a href="{{ route('finance.v2.budgets.index') }}" class="btn btn-outline-primary">Budgets</a>
        <a href="{{ route('finance.v2.transactions.index') }}" class="btn btn-outline-primary">Transactions</a>
        <a href="{{ route('finance.v2.modifs.index') }}" class="btn btn-outline-primary">Modifs</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card data-card border-start border-3 border-primary"><div class="card-body">
        <div class="text-muted small">Sources</div><div class="h3 mb-0">{{ $kpis['nb_sources'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card data-card border-start border-3 border-success"><div class="card-body">
        <div class="text-muted small">Budgets</div><div class="h3 mb-0">{{ $kpis['nb_budgets'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card data-card border-start border-3 border-warning"><div class="card-body">
        <div class="text-muted small">Transactions</div><div class="h3 mb-0">{{ $kpis['nb_transactions'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card data-card border-start border-3 border-info"><div class="card-body">
        <div class="text-muted small">Modifs</div><div class="h3 mb-0">{{ $kpis['nb_modifs'] }}</div>
    </div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card data-card">
            <div class="card-header"><strong><i class="fas fa-chart-pie me-1"></i> Répartition budget par source</strong></div>
            <div class="card-body">
                @if($repartitionSources->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">Aucun budget planifié sur cet exercice.</p>
                @else
                    <div class="mb-2">
                        <small class="text-muted">Total budgétisé :</small>
                        <strong class="fs-5 ms-1">{{ number_format($totalBudgetsSources, 0, ',', ' ') }} XAF</strong>
                    </div>
                    @foreach($repartitionSources as $r)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span><strong>{{ $r['code'] }}</strong> — {{ $r['source'] }}</span>
                                <span>{{ number_format($r['total'], 0, ',', ' ') }} <small class="text-muted">({{ $r['pct'] }}%)</small></span>
                            </div>
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar" style="width: {{ $r['pct'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card data-card">
            <div class="card-header"><strong><i class="fas fa-list me-1"></i> Dernières transactions</strong></div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead><tr><th>Date</th><th>Code</th><th>Type</th><th>Libellé</th><th class="text-end">Montant</th></tr></thead>
                <tbody>
                @forelse($dernieresTransactions as $tx)
                    <tr>
                        <td><small>{{ $tx->date?->format('d/m/y') }}</small></td>
                        <td><code>{{ $tx->code ?? '—' }}</code></td>
                        <td>
                            @if($tx->type == 0)
                                <span class="badge bg-danger">Dép</span>
                            @else
                                <span class="badge bg-success">Rec</span>
                            @endif
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($tx->label, 30) }}</td>
                        <td class="text-end">{{ number_format((float) $tx->montant, 0, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Aucune</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>

@if($modifsEnAttente->isNotEmpty())
<div class="card data-card mt-3">
    <div class="card-header"><strong><i class="fas fa-hourglass-half me-1"></i> Modifications en cours</strong></div>
    <div class="table-responsive"><table class="table table-sm mb-0">
        <thead><tr><th>Objet</th><th>Montant</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        @foreach($modifsEnAttente as $m)
            <tr>
                <td>{{ \Illuminate\Support\Str::limit($m->comment, 60) }}</td>
                <td>{{ number_format((float) $m->montant, 0, ',', ' ') }} XAF</td>
                <td><span class="badge bg-{{ $m->status_couleur }}">{{ $m->status_libelle }}</span></td>
                <td><a href="{{ route('finance.v2.modifs.show', $m) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
</div>
@endif
@endsection
