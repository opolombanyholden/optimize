@extends('layouts.app')

@section('title', 'Tableau de bord Finance')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Finance & Budget</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-coins me-2 text-primary"></i>Tableau de bord Finance</h1>
        <p class="text-muted mb-0">
            @if($exerciceEnCours)
                Exercice en cours : <strong>{{ $exerciceEnCours->libelle ?? $exerciceEnCours->exercice }}</strong>
                @if($exerciceEnCours->datedebut && $exerciceEnCours->datefin)
                    · du {{ \Carbon\Carbon::parse($exerciceEnCours->datedebut)->format('d/m/Y') }}
                    au {{ \Carbon\Carbon::parse($exerciceEnCours->datefin)->format('d/m/Y') }}
                @endif
            @else
                <span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Aucun exercice en cours</span>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        @can('create:grandlivre')
            <a href="{{ route('finance.grand-livre.create') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus me-1"></i> Nouvelle écriture
            </a>
        @endcan
        @can('create:exercice')
            <a href="{{ route('finance.exercices.create') }}" class="btn btn-primary">
                <i class="fas fa-calendar-plus me-1"></i> Nouvel exercice
            </a>
        @endcan
    </div>
</div>

{{-- ═════════ ALERTES WORKFLOW EXERCICE ═════════ --}}
@if($exercicesSoumis->isNotEmpty())
    <div class="alert alert-warning d-flex align-items-center">
        <i class="fas fa-hourglass-half me-2 fs-4"></i>
        <div class="flex-grow-1">
            <strong>{{ $exercicesSoumis->count() }} planification(s) en attente de validation top management.</strong>
            @foreach($exercicesSoumis as $ex)
                <br><a href="{{ route('finance.exercices.show', $ex) }}">{{ $ex->libelle }}</a>
                <small class="text-muted"> · soumis le {{ $ex->soumis_at?->translatedFormat('d M Y') }}</small>
            @endforeach
        </div>
    </div>
@endif
@if($exercicesAttenteCloture->isNotEmpty())
    <div class="alert alert-danger d-flex align-items-center">
        <i class="fas fa-triangle-exclamation me-2 fs-4"></i>
        <div class="flex-grow-1">
            <strong>{{ $exercicesAttenteCloture->count() }} exercice(s) à clôturer.</strong>
            La date de fin est dépassée.
            @foreach($exercicesAttenteCloture as $ex)
                <br><a href="{{ route('finance.exercices.show', $ex) }}">{{ $ex->libelle }}</a>
                <small class="text-muted"> · échéance {{ \Carbon\Carbon::parse($ex->datefin)->translatedFormat('d M Y') }}</small>
            @endforeach
        </div>
    </div>
@endif

{{-- ═════════ KPIs ═════════ --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card data-card border-start border-3 border-primary">
            <div class="card-body">
                <div class="text-muted small">Exercices</div>
                <div class="h3 mb-0">{{ $kpis['exercices_total'] }}</div>
                <small class="text-muted">
                    {{ $kpis['exercices_en_cours'] }} en cours ·
                    {{ $kpis['exercices_planifies'] }} planifiés ·
                    {{ $kpis['exercices_clotures'] }} clôturés
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card data-card border-start border-3 border-success">
            <div class="card-body">
                <div class="text-muted small">Comptes</div>
                <div class="h3 mb-0">{{ $kpis['comptes_actifs'] }}</div>
                <small class="text-muted">au plan comptable</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card data-card border-start border-3 border-info">
            <div class="card-body">
                <div class="text-muted small">Écritures du mois</div>
                <div class="h3 mb-0">{{ $kpis['ecritures_mois'] }}</div>
                <small class="text-muted">{{ now()->translatedFormat('F Y') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card data-card border-start border-3 {{ $kpis['ecritures_brouillons'] > 0 ? 'border-warning' : 'border-secondary' }}">
            <div class="card-body">
                <div class="text-muted small">Brouillons à valider</div>
                <div class="h3 mb-0">{{ $kpis['ecritures_brouillons'] }}</div>
                <small class="text-muted">écritures non validées</small>
            </div>
        </div>
    </div>
</div>

@if($exerciceEnCours)
{{-- ═════════ EXÉCUTION BUDGÉTAIRE ═════════ --}}
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card data-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="fas fa-chart-pie me-1 text-muted"></i> Exécution budgétaire</strong>
                <small class="text-muted">{{ $exerciceEnCours->libelle ?? $exerciceEnCours->exercice }}</small>
            </div>
            <div class="card-body">
                <div class="row g-2 text-center mb-3">
                    <div class="col-4">
                        <div class="text-muted small">Budget total</div>
                        <strong class="d-block fs-5">{{ number_format($budgetTotal, 0, ',', ' ') }}</strong>
                        <small class="text-muted">XAF</small>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Engagé</div>
                        <strong class="d-block fs-5 text-warning">{{ number_format($engagementTotal, 0, ',', ' ') }}</strong>
                        <small class="text-muted">XAF</small>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Disponible</div>
                        <strong class="d-block fs-5 text-success">{{ number_format($budgetTotal - $engagementTotal, 0, ',', ' ') }}</strong>
                        <small class="text-muted">XAF</small>
                    </div>
                </div>

                @php
                    $couleur = $tauxExecution > 90 ? 'danger' : ($tauxExecution > 75 ? 'warning' : 'success');
                @endphp
                <div class="d-flex justify-content-between mb-1">
                    <small>Taux d'exécution</small>
                    <strong class="text-{{ $couleur }}">{{ $tauxExecution }}%</strong>
                </div>
                <div class="progress" style="height:10px;">
                    <div class="progress-bar bg-{{ $couleur }}" style="width: {{ min($tauxExecution, 100) }}%"></div>
                </div>

                @if($top5Lignes->count() > 0)
                    <hr>
                    <strong class="small d-block mb-2">Top 5 lignes par engagement</strong>
                    <table class="table table-sm mb-0">
                        <tbody>
                        @foreach($top5Lignes as $l)
                            <tr>
                                <td>{{ \Illuminate\Support\Str::limit($l['libelle'], 40) }}</td>
                                <td class="text-end">
                                    <small class="text-muted">{{ number_format($l['engage'], 0, ',', ' ') }} / {{ number_format($l['budget'], 0, ',', ' ') }}</small>
                                </td>
                                <td class="text-end" style="width:60px;">
                                    @php $tc = $l['taux'] > 90 ? 'danger' : ($l['taux'] > 75 ? 'warning' : 'success'); @endphp
                                    <span class="badge bg-{{ $tc }}">{{ $l['taux'] }}%</span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- ALERTES --}}
    <div class="col-lg-5">
        <div class="card data-card h-100">
            <div class="card-header"><strong><i class="fas fa-triangle-exclamation me-1 text-warning"></i> Alertes</strong></div>
            <div class="card-body">
                @if($lignesEnAlerte->isEmpty())
                    <p class="text-muted mb-0 text-center py-4">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        Aucune ligne en alerte.
                    </p>
                @else
                    <p class="small text-muted mb-2">Lignes ≥ 90% du budget engagé :</p>
                    @foreach($lignesEnAlerte as $a)
                        <div class="alert alert-{{ $a['depasse'] ? 'danger' : 'warning' }} py-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <strong class="small">{{ \Illuminate\Support\Str::limit($a['libelle'], 35) }}</strong>
                                <span class="badge bg-{{ $a['depasse'] ? 'danger' : 'warning' }}">{{ $a['taux'] }}%</span>
                            </div>
                            <small class="text-muted">
                                {{ number_format($a['engage'], 0, ',', ' ') }} / {{ number_format($a['budget'], 0, ',', ' ') }} XAF
                                @if($a['depasse']) · <strong class="text-danger">DÉPASSÉ</strong> @endif
                            </small>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═════════ ÉVOLUTION + DERNIÈRES ÉCRITURES ═════════ --}}
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card data-card h-100">
            <div class="card-header"><strong><i class="fas fa-chart-line me-1 text-muted"></i> Évolution sur 12 mois</strong></div>
            <div class="card-body" style="min-height:280px;">
                <canvas id="chartEvolution"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card data-card h-100">
            <div class="card-header"><strong><i class="fas fa-list me-1 text-muted"></i> Dernières écritures</strong></div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Libellé</th>
                            <th>Sens</th>
                            <th class="text-end">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($dernieresEcritures as $e)
                        <tr>
                            <td><small>{{ $e->date_ecriture?->format('d/m') }}</small></td>
                            <td>
                                <a href="{{ route('finance.grand-livre.show', $e) }}" class="text-decoration-none">
                                    {{ \Illuminate\Support\Str::limit($e->libelle ?? $e->description ?? '—', 35) }}
                                </a>
                            </td>
                            <td>
                                @if($e->sens === 'debit' || $e->sens === 'D')
                                    <span class="badge bg-danger">D</span>
                                @elseif($e->sens === 'credit' || $e->sens === 'C')
                                    <span class="badge bg-success">C</span>
                                @else
                                    <span class="badge bg-secondary">{{ $e->sens ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format((float) $e->montant_tc, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Aucune écriture récente</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- TODO sécurité : ajouter integrity="sha384-..." crossorigin="anonymous" (idem dans objectif/kpi/show.blade.php). À régénérer depuis https://www.srihash.org/ ou en bundlant Chart.js dans public/vendor/. --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx = document.getElementById('chartEvolution');
    if (!ctx) return;
    const data = @json($evolutionEcritures);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.mois),
            datasets: [{
                label: 'Nombre d\'écritures',
                data: data.map(d => d.count),
                backgroundColor: 'rgba(79, 70, 229, 0.6)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
})();
</script>
@endpush
@endsection
