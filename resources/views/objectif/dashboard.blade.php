@extends('layouts.app')

@section('title', 'Tableau de bord Objectifs & KPI')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   OBJECTIFS DASHBOARD — même standard que /appro, /finance, /rh
   Charte : rose vif (#DB2777) module Objectifs & KPI
═══════════════════════════════════════════════════════════════ */
:root {
    --ob-brand: #DB2777;
    --ob-brand-deep: #BE185D;
    --ob-brand-soft: #FCE7F3;
    --ob-brand-tint: #FDF2F8;
    --ob-ink: #0F172A;
    --ob-ink-2: #334155;
    --ob-mute: #64748B;
    --ob-mute-2: #94A3B8;
    --ob-line: #E5E7EB;
    --ob-line-2: #F1F5F9;
    --ob-danger: #DC2626;
    --ob-warn: #D97706;
    --ob-info: #4F46E5;
    --ob-ok: #059669;
    --ob-primary: #0A66C2;
}
.ob-shell { max-width: 1400px; margin: 0 auto; }

/* HEADER */
.ob-head {
    background: #fff; border: 1px solid var(--ob-line); border-radius: 8px;
    padding: 1rem 1.25rem; margin-bottom: 1rem;
    display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.ob-head-title { display: flex; align-items: center; gap: .85rem; }
.ob-head-emblem {
    width: 44px; height: 44px; border-radius: 8px; background: var(--ob-brand-soft);
    color: var(--ob-brand); display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem;
}
.ob-head h1 { font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--ob-ink); }
.ob-head p  { margin: 0; font-size: .8rem; color: var(--ob-mute); }
.ob-head-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.ob-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .5rem .85rem; font-size: .78rem; font-weight: 600; border-radius: 6px; text-decoration: none; border: 1px solid transparent; transition: background .15s, border-color .15s, color .15s; }
.ob-btn-primary { background: var(--ob-brand); color: #fff; }
.ob-btn-primary:hover { background: var(--ob-brand-deep); color: #fff; }
.ob-btn-outline { background: #fff; color: var(--ob-ink-2); border-color: var(--ob-line); }
.ob-btn-outline:hover { border-color: var(--ob-brand); color: var(--ob-brand); }

/* PRIORITY BAR */
.ob-alerts { display: grid; gap: .65rem; margin-bottom: 1.25rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
.ob-alert { display: flex; align-items: center; gap: .75rem; padding: .75rem .9rem; background: #fff; border: 1px solid var(--ob-line); border-left: 3px solid var(--ob-danger); border-radius: 6px; text-decoration: none; color: inherit; transition: border-color .15s, background .15s; }
.ob-alert:hover { background: #FEF2F2; color: inherit; }
.ob-alert.ob-alert-warn { border-left-color: var(--ob-warn); }
.ob-alert.ob-alert-warn:hover { background: #FFFBEB; }
.ob-alert.ob-alert-info { border-left-color: var(--ob-brand); }
.ob-alert.ob-alert-info:hover { background: var(--ob-brand-tint); }
.ob-alert-ico { width: 34px; height: 34px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; background: #FEE2E2; color: var(--ob-danger); flex-shrink: 0; }
.ob-alert-warn .ob-alert-ico { background: #FEF3C7; color: var(--ob-warn); }
.ob-alert-info .ob-alert-ico { background: var(--ob-brand-soft); color: var(--ob-brand); }
.ob-alert-body { flex: 1; min-width: 0; }
.ob-alert-title { font-size: .82rem; font-weight: 700; color: var(--ob-ink); line-height: 1.15; }
.ob-alert-sub { font-size: .7rem; color: var(--ob-mute); margin-top: .1rem; }
.ob-alert-arrow { color: var(--ob-mute-2); }

/* KPI CARDS */
.ob-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .85rem; margin-bottom: 1.25rem; }
@media (max-width: 992px) { .ob-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .ob-kpi-grid { grid-template-columns: 1fr; } }
.ob-kpi { background: #fff; border: 1px solid var(--ob-line); border-radius: 8px; padding: .95rem 1rem; display: flex; flex-direction: column; gap: .5rem; text-decoration: none; color: inherit; transition: border-color .15s, transform .15s; }
.ob-kpi:hover { border-color: var(--ob-brand); color: inherit; transform: translateY(-1px); }
.ob-kpi-head { display: flex; align-items: center; justify-content: space-between; }
.ob-kpi-lbl { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; color: var(--ob-mute); font-weight: 700; }
.ob-kpi-ico { width: 30px; height: 30px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; background: var(--ob-brand-soft); color: var(--ob-brand); font-size: .8rem; }
.ob-kpi-val { font-size: 1.55rem; font-weight: 800; color: var(--ob-ink); line-height: 1; }
.ob-kpi-val small { font-size: .72rem; color: var(--ob-mute); font-weight: 500; margin-left: .25rem; }
.ob-kpi-sub { font-size: .7rem; color: var(--ob-mute); }
.ob-kpi-progress { height: 6px; background: var(--ob-line-2); border-radius: 3px; overflow: hidden; }
.ob-kpi-progress-fill { height: 100%; background: var(--ob-brand); border-radius: 3px; }
.ob-kpi-progress-fill.is-ok { background: var(--ob-ok); }
.ob-kpi-progress-fill.is-warn { background: var(--ob-warn); }
.ob-kpi-progress-fill.is-danger { background: var(--ob-danger); }

/* SECTION CARDS */
.ob-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.ob-row-2 { grid-template-columns: 1fr 1fr; }
@media (max-width: 991px) { .ob-row-2 { grid-template-columns: 1fr; } }
.ob-card { background: #fff; border: 1px solid var(--ob-line); border-radius: 8px; overflow: hidden; }
.ob-card-hd { display: flex; align-items: center; justify-content: space-between; padding: .85rem 1.1rem; border-bottom: 1px solid var(--ob-line-2); }
.ob-card-hd h6 { margin: 0; font-size: .85rem; font-weight: 700; color: var(--ob-ink); display: flex; align-items: center; gap: .5rem; }
.ob-card-hd-ico { width: 26px; height: 26px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; }
.ob-link { font-size: .72rem; font-weight: 600; color: var(--ob-primary); text-decoration: none; }
.ob-link:hover { text-decoration: underline; }
.ob-card-tight { padding: 0; }
.ob-empty { padding: 1.75rem 1rem; text-align: center; color: var(--ob-mute-2); font-size: .8rem; }
.ob-empty i { display: block; font-size: 1.35rem; margin-bottom: .35rem; opacity: .55; }

/* Objectif ligne — barre + label */
.ob-obj-row { padding: .7rem 1.1rem; border-bottom: 1px solid var(--ob-line-2); }
.ob-obj-row:last-child { border-bottom: 0; }
.ob-obj-head { display: flex; justify-content: space-between; align-items: baseline; gap: .5rem; }
.ob-obj-name { font-size: .82rem; font-weight: 700; color: var(--ob-ink); text-decoration: none; }
.ob-obj-name:hover { color: var(--ob-primary); }
.ob-obj-pct { font-size: .78rem; font-weight: 800; }
.ob-obj-meta { font-size: .68rem; color: var(--ob-mute); margin-top: .15rem; }
.ob-obj-prog { height: 5px; background: var(--ob-line-2); border-radius: 3px; margin-top: .45rem; overflow: hidden; }
.ob-obj-prog-fill { height: 100%; border-radius: 3px; transition: width .3s; }

/* KPI ligne */
.ob-kpi-row { padding: .65rem 1.1rem; border-bottom: 1px solid var(--ob-line-2); }
.ob-kpi-row:last-child { border-bottom: 0; }
.ob-kpi-title { font-size: .82rem; font-weight: 700; color: var(--ob-ink); }
.ob-kpi-values { display: flex; justify-content: space-between; font-size: .72rem; color: var(--ob-mute); margin-top: .15rem; }
.ob-kpi-values strong { color: var(--ob-ink); }

/* Donut */
.ob-donut-wrap { display: flex; align-items: center; gap: 1.25rem; padding: 1rem 1.1rem; flex-wrap: wrap; }
.ob-donut { width: 130px; height: 130px; position: relative; flex-shrink: 0; }
.ob-donut svg { transform: rotate(-90deg); }
.ob-donut-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.ob-donut-center strong { font-size: 1.35rem; font-weight: 800; color: var(--ob-ink); line-height: 1; }
.ob-donut-center span { font-size: .68rem; color: var(--ob-mute); text-transform: uppercase; letter-spacing: .05em; margin-top: .2rem; }
.ob-donut-legend { flex: 1; min-width: 160px; display: flex; flex-direction: column; gap: .55rem; }
.ob-legend-item { display: flex; align-items: center; justify-content: space-between; font-size: .78rem; }
.ob-legend-key { display: inline-flex; align-items: center; gap: .5rem; color: var(--ob-ink-2); text-transform: capitalize; }
.ob-legend-dot { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }
.ob-legend-val { font-weight: 700; color: var(--ob-ink); }
.ob-legend-pct { color: var(--ob-mute); font-weight: 600; font-size: .7rem; margin-left: .35rem; }

.ob-badge { font-size: .68rem; padding: .12rem .5rem; border-radius: 4px; font-weight: 700; display: inline-block; }
.ob-badge-danger { background: #FEE2E2; color: var(--ob-danger); }
.ob-badge-warn   { background: #FEF3C7; color: var(--ob-warn); }
.ob-badge-ok     { background: #D1FAE5; color: var(--ob-ok); }
.ob-badge-brand  { background: var(--ob-brand-soft); color: var(--ob-brand); }
.ob-badge-mute   { background: var(--ob-line-2); color: var(--ob-mute); }
</style>
@endpush

@section('content')
<div class="ob-shell">

{{-- ═════════════════ HEADER ═════════════════ --}}
<div class="ob-head">
    <div class="ob-head-title">
        <span class="ob-head-emblem"><i class="fas fa-bullseye"></i></span>
        <div>
            <h1>Objectifs &amp; KPI</h1>
            <p>Pilotage stratégique — {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
        </div>
    </div>
    <div class="ob-head-actions">
        @can('create:objectif')
        <a href="{{ route('objectifs.objectifs.index') }}" class="ob-btn ob-btn-primary">
            <i class="fas fa-plus"></i>Nouvel objectif
        </a>
        @endcan
        @can('read:kpi')
        <a href="{{ route('objectifs.kpi.index') }}" class="ob-btn ob-btn-outline">
            <i class="fas fa-chart-line"></i>Tableau KPI
        </a>
        @endcan
    </div>
</div>

{{-- ═════════════════ PRIORITY BAR ═════════════════ --}}
@php
    $alerts = collect();
    if ($stats['objectifs_en_retard'] > 0) $alerts->push([
        'type'=>'danger', 'ico'=>'fa-hourglass-half',
        'title'=>$stats['objectifs_en_retard'].' objectif'.($stats['objectifs_en_retard']>1?'s':'').' en retard',
        'sub'=>'Date de fin dépassée',
        'url'=>route('objectifs.objectifs.index')
    ]);
    if ($stats['kpi_en_alerte'] > 0) $alerts->push([
        'type'=>'danger', 'ico'=>'fa-chart-simple',
        'title'=>$stats['kpi_en_alerte'].' KPI en alerte',
        'sub'=>'Sous 50 % de la cible',
        'url'=>route('objectifs.kpi.index')
    ]);
    if (($compteurAlertes['plans_retard'] ?? 0) > 0) $alerts->push([
        'type'=>'warn', 'ico'=>'fa-list-ul',
        'title'=>$compteurAlertes['plans_retard'].' plan'.($compteurAlertes['plans_retard']>1?'s':'').' d\'action en retard',
        'sub'=>'À relancer',
        'url'=>route('objectifs.plans-action.index')
    ]);
    if (($compteurAlertes['evaluations_en_attente'] ?? 0) > 0) $alerts->push([
        'type'=>'info', 'ico'=>'fa-star-half-stroke',
        'title'=>$compteurAlertes['evaluations_en_attente'].' évaluation'.($compteurAlertes['evaluations_en_attente']>1?'s':'').' à finaliser',
        'sub'=>'Cycle en cours',
        'url'=>route('objectifs.evaluations.index')
    ]);
@endphp
@if($alerts->count())
<div class="ob-alerts">
    @foreach($alerts as $a)
    <a href="{{ $a['url'] }}" class="ob-alert ob-alert-{{ $a['type'] }}">
        <span class="ob-alert-ico"><i class="fas {{ $a['ico'] }}"></i></span>
        <div class="ob-alert-body">
            <div class="ob-alert-title">{{ $a['title'] }}</div>
            <div class="ob-alert-sub">{{ $a['sub'] }}</div>
        </div>
        <i class="fas fa-chevron-right ob-alert-arrow"></i>
    </a>
    @endforeach
</div>
@endif

{{-- ═════════════════ KPIS PRINCIPAUX ═════════════════ --}}
@php
    $tauxAtteinte = $stats['objectifs_total'] > 0
        ? round(($stats['objectifs_atteints'] / $stats['objectifs_total']) * 100)
        : 0;
    $tauxKpiOk = $stats['kpi_total'] > 0
        ? round(($stats['kpi_au_dessus_cible'] / $stats['kpi_total']) * 100)
        : 0;
    $tauxEval = $stats['evaluations_total'] > 0
        ? round(($stats['evaluations_finalisees'] / $stats['evaluations_total']) * 100)
        : 0;
@endphp
<div class="ob-kpi-grid">
    {{-- KPI 1 — Objectifs actifs / atteints --}}
    <a href="{{ route('objectifs.objectifs.index') }}" class="ob-kpi">
        <div class="ob-kpi-head">
            <span class="ob-kpi-lbl">Taux d'atteinte</span>
            <span class="ob-kpi-ico"><i class="fas fa-bullseye"></i></span>
        </div>
        <div class="ob-kpi-val">{{ $tauxAtteinte }}<small>%</small></div>
        @php $c = $tauxAtteinte >= 70 ? 'is-ok' : ($tauxAtteinte >= 40 ? 'is-warn' : 'is-danger'); @endphp
        <div class="ob-kpi-progress"><div class="ob-kpi-progress-fill {{ $c }}" style="width:{{ $tauxAtteinte }}%;"></div></div>
        <div class="ob-kpi-sub">{{ $stats['objectifs_atteints'] }} atteint(s) sur {{ $stats['objectifs_total'] }}</div>
    </a>

    {{-- KPI 2 — Objectifs actifs --}}
    <a href="{{ route('objectifs.objectifs.index', ['type' => 'strategique']) }}" class="ob-kpi">
        <div class="ob-kpi-head">
            <span class="ob-kpi-lbl">Objectifs actifs</span>
            <span class="ob-kpi-ico" style="background:#E0E7FF; color:var(--ob-info);"><i class="fas fa-list-check"></i></span>
        </div>
        <div class="ob-kpi-val">{{ $stats['objectifs_actifs'] }}</div>
        <div class="ob-kpi-sub">{{ $stats['objectifs_strategiques'] }} stratégiques</div>
    </a>

    {{-- KPI 3 — KPI au-dessus cible --}}
    <a href="{{ route('objectifs.kpi.index') }}" class="ob-kpi">
        <div class="ob-kpi-head">
            <span class="ob-kpi-lbl">KPI au-dessus cible</span>
            <span class="ob-kpi-ico" style="background:#D1FAE5; color:var(--ob-ok);"><i class="fas fa-chart-line"></i></span>
        </div>
        <div class="ob-kpi-val">{{ $stats['kpi_au_dessus_cible'] }}<small>/ {{ $stats['kpi_total'] }}</small></div>
        @php $ck = $tauxKpiOk >= 70 ? 'is-ok' : ($tauxKpiOk >= 40 ? 'is-warn' : 'is-danger'); @endphp
        <div class="ob-kpi-progress"><div class="ob-kpi-progress-fill {{ $ck }}" style="width:{{ $tauxKpiOk }}%;"></div></div>
        <div class="ob-kpi-sub">{{ $tauxKpiOk }} % de vos KPI performants</div>
    </a>

    {{-- KPI 4 — Évaluations finalisées --}}
    <a href="{{ route('objectifs.evaluations.index') }}" class="ob-kpi">
        <div class="ob-kpi-head">
            <span class="ob-kpi-lbl">Évaluations finalisées</span>
            <span class="ob-kpi-ico" style="background:#FEF3C7; color:var(--ob-warn);"><i class="fas fa-star-half-stroke"></i></span>
        </div>
        <div class="ob-kpi-val">{{ $stats['evaluations_finalisees'] }}<small>/ {{ $stats['evaluations_total'] }}</small></div>
        <div class="ob-kpi-progress"><div class="ob-kpi-progress-fill" style="width:{{ $tauxEval }}%;"></div></div>
        <div class="ob-kpi-sub">{{ $tauxEval }} % du cycle bouclé</div>
    </a>
</div>

{{-- ═════════════════ Top objectifs stratégiques + Répartition portée ═════════════════ --}}
<div class="ob-row ob-row-2">
    {{-- Top 8 objectifs stratégiques par progression --}}
    <div class="ob-card">
        <div class="ob-card-hd">
            <h6>
                <span class="ob-card-hd-ico" style="background:var(--ob-brand-soft); color:var(--ob-brand);"><i class="fas fa-trophy"></i></span>
                Top objectifs stratégiques
            </h6>
            <a href="{{ route('objectifs.objectifs.index', ['type' => 'strategique']) }}" class="ob-link">Voir tout →</a>
        </div>
        <div class="ob-card-tight">
            @forelse($objectifsStrategiques as $obj)
            @php
                $prog = (int) $obj->progression;
                $color = $prog >= 70 ? 'var(--ob-ok)' : ($prog >= 40 ? 'var(--ob-warn)' : 'var(--ob-danger)');
                $enRetard = $obj->estEnRetard();
            @endphp
            <div class="ob-obj-row">
                <div class="ob-obj-head">
                    <a href="{{ route('objectifs.objectifs.show', $obj) }}" class="ob-obj-name">
                        {{ \Illuminate\Support\Str::limit($obj->titre, 42) }}
                    </a>
                    <span class="ob-obj-pct" style="color:{{ $color }};">{{ $prog }}%</span>
                </div>
                <div class="ob-obj-meta">
                    @if($obj->code)<code style="background:var(--ob-line-2); color:var(--ob-ink-2); padding:.05rem .3rem; border-radius:3px;">{{ $obj->code }}</code>@endif
                    @if($obj->portee) · {{ ucfirst($obj->portee) }}@endif
                    @if($obj->date_fin) · Fin {{ $obj->date_fin->format('d/m/Y') }}@endif
                    @if($enRetard) <span class="ob-badge ob-badge-danger ms-1">Retard</span>@endif
                </div>
                <div class="ob-obj-prog"><div class="ob-obj-prog-fill" style="width:{{ $prog }}%; background:{{ $color }};"></div></div>
            </div>
            @empty
            <div class="ob-empty"><i class="fas fa-bullseye"></i>Aucun objectif stratégique</div>
            @endforelse
        </div>
    </div>

    {{-- Donut répartition par portée --}}
    <div class="ob-card">
        <div class="ob-card-hd">
            <h6>
                <span class="ob-card-hd-ico" style="background:#E0E7FF; color:var(--ob-info);"><i class="fas fa-chart-pie"></i></span>
                Répartition par portée
            </h6>
        </div>
        @php
            $totalP = $repartitionPortee->sum() ?: 1;
            $palette = ['#DB2777', '#4F46E5', '#059669', '#D97706', '#0891B2', '#7C3AED'];
            $c = 251.327; $off = 0; $i = 0; $segments = [];
            foreach ($repartitionPortee as $portee => $n) {
                if ($n <= 0) { $i++; continue; }
                $len = ($n / $totalP) * $c;
                $segments[] = ['label'=>$portee ?: 'Non défini', 'n'=>$n, 'pct'=>round($n/$totalP*100), 'len'=>$len, 'off'=>$off, 'color'=>$palette[$i % count($palette)]];
                $off += $len; $i++;
            }
        @endphp
        <div class="ob-donut-wrap">
            <div class="ob-donut">
                <svg width="130" height="130" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#F1F5F9" stroke-width="14"/>
                    @foreach($segments as $s)
                    <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $s['color'] }}" stroke-width="14"
                            stroke-dasharray="{{ $s['len'] }} {{ $c - $s['len'] }}" stroke-dashoffset="{{ -$s['off'] }}"/>
                    @endforeach
                </svg>
                <div class="ob-donut-center">
                    <strong>{{ $stats['objectifs_total'] }}</strong>
                    <span>Objectifs</span>
                </div>
            </div>
            <div class="ob-donut-legend">
                @forelse($segments as $s)
                <div class="ob-legend-item">
                    <span class="ob-legend-key"><span class="ob-legend-dot" style="background:{{ $s['color'] }};"></span>{{ $s['label'] }}</span>
                    <span><span class="ob-legend-val">{{ $s['n'] }}</span><span class="ob-legend-pct">{{ $s['pct'] }}%</span></span>
                </div>
                @empty
                <div class="ob-empty" style="padding:.5rem 0;">Aucune donnée</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ═════════════════ KPI critiques + KPI au top ═════════════════ --}}
<div class="ob-row ob-row-2">
    {{-- KPI critiques --}}
    <div class="ob-card">
        <div class="ob-card-hd">
            <h6>
                <span class="ob-card-hd-ico" style="background:#FEE2E2; color:var(--ob-danger);"><i class="fas fa-chart-simple"></i></span>
                KPI critiques
                @if($kpisCritiques->count())<span class="ob-badge ob-badge-danger ms-1">{{ $kpisCritiques->count() }}</span>@endif
            </h6>
            <a href="{{ route('objectifs.kpi.index') }}" class="ob-link">Tous →</a>
        </div>
        <div class="ob-card-tight">
            @forelse($kpisCritiques as $kpi)
            @php $prog = (int) $kpi->progression; @endphp
            <div class="ob-kpi-row">
                <div class="ob-kpi-title">{{ \Illuminate\Support\Str::limit($kpi->titre, 45) }}</div>
                <div class="ob-kpi-values">
                    <span>Actuel : <strong>{{ number_format($kpi->valeur_actuelle, 0, ',', ' ') }} {{ $kpi->unite }}</strong></span>
                    <span>Cible : <strong>{{ number_format($kpi->valeur_cible, 0, ',', ' ') }} {{ $kpi->unite }}</strong></span>
                    <span class="ob-badge ob-badge-danger">{{ $prog }}%</span>
                </div>
                <div class="ob-obj-prog" style="margin-top:.35rem;"><div class="ob-obj-prog-fill" style="width:{{ $prog }}%; background:var(--ob-danger);"></div></div>
            </div>
            @empty
            <div class="ob-empty"><i class="fas fa-check-circle"></i>Aucun KPI critique — situation saine</div>
            @endforelse
        </div>
    </div>

    {{-- KPI au top --}}
    <div class="ob-card">
        <div class="ob-card-hd">
            <h6>
                <span class="ob-card-hd-ico" style="background:#D1FAE5; color:var(--ob-ok);"><i class="fas fa-arrow-trend-up"></i></span>
                KPI au top
                @if($kpisAuTop->count())<span class="ob-badge ob-badge-ok ms-1">{{ $kpisAuTop->count() }}</span>@endif
            </h6>
            <a href="{{ route('objectifs.kpi.index') }}" class="ob-link">Tous →</a>
        </div>
        <div class="ob-card-tight">
            @forelse($kpisAuTop as $kpi)
            @php $prog = (int) $kpi->progression; @endphp
            <div class="ob-kpi-row">
                <div class="ob-kpi-title">{{ \Illuminate\Support\Str::limit($kpi->titre, 45) }}</div>
                <div class="ob-kpi-values">
                    <span>Actuel : <strong>{{ number_format($kpi->valeur_actuelle, 0, ',', ' ') }} {{ $kpi->unite }}</strong></span>
                    <span>Cible : <strong>{{ number_format($kpi->valeur_cible, 0, ',', ' ') }} {{ $kpi->unite }}</strong></span>
                    <span class="ob-badge ob-badge-ok">{{ $prog }}%</span>
                </div>
                <div class="ob-obj-prog" style="margin-top:.35rem;"><div class="ob-obj-prog-fill" style="width:{{ min(100, $prog) }}%; background:var(--ob-ok);"></div></div>
            </div>
            @empty
            <div class="ob-empty"><i class="fas fa-chart-line"></i>Aucun KPI n'a encore atteint 90 %</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═════════════════ Répartition statut + Alertes stratégiques ═════════════════ --}}
<div class="ob-row ob-row-2">
    {{-- Répartition par statut --}}
    <div class="ob-card">
        <div class="ob-card-hd">
            <h6>
                <span class="ob-card-hd-ico" style="background:var(--ob-brand-soft); color:var(--ob-brand);"><i class="fas fa-layer-group"></i></span>
                Répartition par statut
            </h6>
        </div>
        <div class="ob-card-tight" style="padding:.5rem 0;">
            @forelse($repartitionStatut as $statut => $n)
            @php
                $col = match($statut) {
                    'actif'    => 'var(--ob-brand)',
                    'atteint'  => 'var(--ob-ok)',
                    'abandonne'=> 'var(--ob-mute)',
                    'suspendu' => 'var(--ob-warn)',
                    default    => 'var(--ob-mute-2)',
                };
                $pct = $stats['objectifs_total'] > 0 ? round($n / $stats['objectifs_total'] * 100) : 0;
            @endphp
            <div class="ob-obj-row" style="padding:.6rem 1.1rem;">
                <div class="ob-obj-head">
                    <span style="text-transform:capitalize; font-weight:600; color:var(--ob-ink-2);">{{ $statut ?: 'Sans statut' }}</span>
                    <span style="font-weight:700; color:{{ $col }};">{{ $n }} <span style="color:var(--ob-mute); font-weight:500; font-size:.7rem;">({{ $pct }}%)</span></span>
                </div>
                <div class="ob-obj-prog"><div class="ob-obj-prog-fill" style="width:{{ $pct }}%; background:{{ $col }};"></div></div>
            </div>
            @empty
            <div class="ob-empty"><i class="fas fa-inbox"></i>Aucun objectif enregistré</div>
            @endforelse
        </div>
    </div>

    {{-- Alertes détaillées --}}
    <div class="ob-card">
        <div class="ob-card-hd">
            <h6>
                <span class="ob-card-hd-ico" style="background:#FEE2E2; color:var(--ob-danger);"><i class="fas fa-triangle-exclamation"></i></span>
                Alertes stratégiques
                @if($alertesListe->count())<span class="ob-badge ob-badge-danger ms-1">{{ $alertesListe->count() }}</span>@endif
            </h6>
        </div>
        <div class="ob-card-tight">
            @forelse($alertesListe as $al)
            <div class="ob-obj-row" style="padding:.6rem 1.1rem;">
                <div class="ob-obj-head">
                    <div style="font-weight:600; color:var(--ob-ink); font-size:.82rem; flex:1;">
                        {{ \Illuminate\Support\Str::limit($al['titre'] ?? $al['message'] ?? 'Alerte', 55) }}
                    </div>
                    @if(!empty($al['niveau']))
                    <span class="ob-badge {{ $al['niveau'] === 'critique' ? 'ob-badge-danger' : ($al['niveau'] === 'attention' ? 'ob-badge-warn' : 'ob-badge-mute') }}">
                        {{ $al['niveau'] }}
                    </span>
                    @endif
                </div>
                @if(!empty($al['description']))
                <div class="ob-obj-meta">{{ \Illuminate\Support\Str::limit($al['description'], 90) }}</div>
                @endif
            </div>
            @empty
            <div class="ob-empty"><i class="fas fa-check-circle"></i>Aucune alerte stratégique — bien joué</div>
            @endforelse
        </div>
    </div>
</div>

</div>{{-- /.ob-shell --}}
@endsection
