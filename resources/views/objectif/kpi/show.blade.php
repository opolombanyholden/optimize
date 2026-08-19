@extends('layouts.app')
@section('title', $kpi->titre . ' — KPI')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('objectifs.dashboard') }}">Objectifs & KPI</a></li>
    <li class="breadcrumb-item"><a href="{{ route('objectifs.kpi.index') }}">KPI</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($kpi->titre, 35) }}</li>
</ol>
@endsection

@section('content')
@php
    $color = ! $kpi->valeur_cible ? '#94A3B8' : ($kpi->progression >= 90 ? '#16A34A' : ($kpi->progression >= 50 ? '#0891B2' : '#DC2626'));
@endphp
<div class="page-intranet" style="--accent: {{ $color }};">

    <div class="contact-detail-header" style="border-top-color: {{ $color }};">
        <div class="contact-detail-photo" style="background: {{ $color }}; display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-chart-line" style="color:#fff;font-size:1.5rem;"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                <span class="opp-stage-badge" style="background:{{ $color }};">{{ ucfirst($kpi->periodicite) }}</span>
                @if($kpi->tendance === 'hausse')<span class="opp-stage-badge" style="background:#16A34A;"><i class="fas fa-arrow-trend-up me-1"></i>Hausse</span>
                @elseif($kpi->tendance === 'baisse')<span class="opp-stage-badge" style="background:#DC2626;"><i class="fas fa-arrow-trend-down me-1"></i>Baisse</span>
                @else<span class="opp-stage-badge" style="background:#94A3B8;"><i class="fas fa-minus me-1"></i>Stable</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $kpi->titre }}</h1>
            @if($kpi->objectif)
            <a href="{{ route('objectifs.objectifs.show', $kpi->objectif) }}" class="contact-detail-orga"><i class="fas fa-bullseye"></i> {{ $kpi->objectif->titre }}</a>
            @endif
        </div>
        <div class="contact-detail-actions">
            <button class="btn btn-intranet" style="background:{{ $color }};" data-bs-toggle="modal" data-bs-target="#modalSaisirValeur">
                <i class="fas fa-plus me-1"></i> Saisir une mesure
            </button>
        </div>
    </div>

    {{-- Valeurs actuelles --}}
    <div class="contact-detail-card mt-3">
        <div class="row g-3 text-center">
            <div class="col-md-4">
                <div style="font-size:2rem;font-weight:800;color:{{ $color }};">{{ rtrim(rtrim($kpi->valeur_actuelle, '0'), '.') }} <small style="font-size:1rem;color:#64748B;">{{ $kpi->unite }}</small></div>
                <small style="color:#64748B;">Valeur actuelle</small>
            </div>
            @if($kpi->valeur_cible)
            <div class="col-md-4">
                <div style="font-size:2rem;font-weight:800;color:#0F172A;">{{ rtrim(rtrim($kpi->valeur_cible, '0'), '.') }} <small style="font-size:1rem;color:#64748B;">{{ $kpi->unite }}</small></div>
                <small style="color:#64748B;">Cible</small>
            </div>
            <div class="col-md-4">
                <div style="font-size:2rem;font-weight:800;color:{{ $color }};">{{ $kpi->progression }}%</div>
                <small style="color:#64748B;">Atteinte de la cible</small>
            </div>
            @endif
        </div>
        @if($kpi->valeur_cible)
        <div class="tache-detail-progress mt-3">
            <div class="tache-detail-progress-bar">
                <div class="tache-detail-progress-fill" style="width:{{ min($kpi->progression, 100) }}%;background:{{ $color }};"></div>
            </div>
            <span class="tache-detail-progress-pct">{{ $kpi->progression }}%</span>
        </div>
        @endif
    </div>

    {{-- Graphique d'évolution des mesures --}}
    @if($kpi->valeurs->count() >= 2)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-chart-area"></i> Évolution dans le temps</h4>
        <div style="position:relative;height:260px;">
            <canvas id="kpiChart"></canvas>
        </div>
    </div>
    @endif

    @if($kpi->description)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
        <p style="font-size:.85rem;color:#475569;">{{ $kpi->description }}</p>
    </div>
    @endif

    {{-- Historique des mesures --}}
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-clock-rotate-left"></i> Historique des mesures ({{ $kpi->valeurs->count() }})</h4>
        @if($kpi->valeurs->count())
        <table class="opp-table" style="font-size:.78rem;">
            <thead>
                <tr><th>Date</th><th style="text-align:right;">Valeur</th><th>Variation</th><th>Commentaire</th><th>Saisi par</th></tr>
            </thead>
            <tbody>
                @php $previous = null; @endphp
                @foreach($kpi->valeurs->sortByDesc('date_mesure') as $v)
                @php
                    $delta = $previous !== null ? ((float) $v->valeur - $previous) : null;
                    $previous = (float) $v->valeur;
                @endphp
                <tr>
                    <td>{{ $v->date_mesure->format('d/m/Y') }}</td>
                    <td style="text-align:right;font-weight:700;">{{ rtrim(rtrim($v->valeur, '0'), '.') }} {{ $kpi->unite }}</td>
                    <td>
                        @if($delta !== null && $delta != 0)
                            <span style="color:{{ $delta > 0 ? '#16A34A' : '#DC2626' }};font-weight:600;">
                                {{ $delta > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($delta, 2), '0'), '.') }}
                            </span>
                        @else — @endif
                    </td>
                    <td>{{ Str::limit($v->commentaire, 50) }}</td>
                    <td style="font-size:.7rem;color:#64748B;">{{ $v->auteur?->prenoms }} {{ $v->auteur?->name }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-muted small mb-0">Aucune mesure saisie pour le moment.</p>
        @endif
    </div>
</div>

<div class="modal fade" id="modalSaisirValeur" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content" action="{{ route('objectifs.kpi.valeurs.store', $kpi) }}">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Saisir une nouvelle mesure</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Valeur <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="valeur" class="form-control" required>
                            @if($kpi->unite)<span class="input-group-text">{{ $kpi->unite }}</span>@endif
                        </div></div>
                    <div class="col-md-6"><label class="form-label">Date de mesure <span class="text-danger">*</span></label>
                        <input type="date" name="date_mesure" class="form-control" value="{{ now()->toDateString() }}" required></div>
                </div>
                <div class="mt-3"><label class="form-label">Commentaire</label>
                    <textarea name="commentaire" rows="2" class="form-control" placeholder="Contexte de cette mesure..."></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:{{ $color }};"><i class="fas fa-save me-2"></i>Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
@if($kpi->valeurs->count() >= 2)
@php
    $valeursTri = $kpi->valeurs->sortBy('date_mesure')->values();
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx = document.getElementById('kpiChart');
    if (!ctx) return;
    const data = {
        labels: @json($valeursTri->map(fn($v) => $v->date_mesure->format('d M Y'))->all()),
        datasets: [
            {
                label: @json('Valeur (' . ($kpi->unite ?? '') . ')'),
                data: @json($valeursTri->map(fn($v) => (float) $v->valeur)->all()),
                borderColor: @json($color),
                backgroundColor: @json($color) + '33',
                borderWidth: 2.5,
                tension: 0.25,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
            },
            @if($kpi->valeur_cible)
            {
                label: 'Cible',
                data: @json(array_fill(0, $valeursTri->count(), (float) $kpi->valeur_cible)),
                borderColor: '#64748B',
                borderDash: [6, 4],
                borderWidth: 1.5,
                pointRadius: 0,
                fill: false,
            }
            @endif
        ],
    };
    new Chart(ctx, {
        type: 'line',
        data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { font: { size: 11 } } },
                tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('fr-FR') } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { font: { size: 11 } } },
                x: { ticks: { font: { size: 10 }, maxRotation: 45 } }
            }
        }
    });
})();
</script>
@endif
@endpush
@endsection
