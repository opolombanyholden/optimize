@extends('layouts.app')

@section('title', 'Tableau de bord RH & Paie')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   RH DASHBOARD — même standard que /appro et /finance
   Charte : vert émeraude (#059669) module RH
═══════════════════════════════════════════════════════════════ */
:root {
    --rh-brand: #059669;
    --rh-brand-deep: #047857;
    --rh-brand-soft: #D1FAE5;
    --rh-brand-tint: #ECFDF5;
    --rh-ink: #0F172A;
    --rh-ink-2: #334155;
    --rh-mute: #64748B;
    --rh-mute-2: #94A3B8;
    --rh-line: #E5E7EB;
    --rh-line-2: #F1F5F9;
    --rh-bg: #F8FAFC;
    --rh-danger: #DC2626;
    --rh-warn: #D97706;
    --rh-info: #4F46E5;
    --rh-primary: #0A66C2;
    --rh-accent: #F5B800;
}
.rh-shell { max-width: 1400px; margin: 0 auto; }

/* ── HERO ── */
.rh-head {
    background: #fff; border: 1px solid var(--rh-line); border-radius: 8px;
    padding: 1rem 1.25rem; margin-bottom: 1rem;
    display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.rh-head-title { display: flex; align-items: center; gap: .85rem; }
.rh-head-emblem {
    width: 44px; height: 44px; border-radius: 8px; background: var(--rh-brand-soft);
    color: var(--rh-brand); display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem;
    flex-shrink: 0;
}
.rh-head h1 { font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--rh-ink); }
.rh-head p  { margin: 0; font-size: .8rem; color: var(--rh-mute); }
.rh-head-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.rh-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem .85rem; font-size: .78rem; font-weight: 600;
    border-radius: 6px; text-decoration: none; border: 1px solid transparent;
    transition: background .15s, border-color .15s, color .15s;
}
.rh-btn-primary { background: var(--rh-brand); color: #fff; }
.rh-btn-primary:hover { background: var(--rh-brand-deep); color: #fff; }
.rh-btn-outline { background: #fff; color: var(--rh-ink-2); border-color: var(--rh-line); }
.rh-btn-outline:hover { border-color: var(--rh-brand); color: var(--rh-brand); }

/* ── PRIORITY BAR ── */
.rh-alerts { display: grid; gap: .65rem; margin-bottom: 1.25rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
.rh-alert {
    display: flex; align-items: center; gap: .75rem; padding: .75rem .9rem;
    background: #fff; border: 1px solid var(--rh-line); border-left: 3px solid var(--rh-danger);
    border-radius: 6px; text-decoration: none; color: inherit; transition: border-color .15s, background .15s;
}
.rh-alert:hover { background: #FEF2F2; color: inherit; }
.rh-alert.rh-alert-warn { border-left-color: var(--rh-warn); }
.rh-alert.rh-alert-warn:hover { background: #FFFBEB; }
.rh-alert.rh-alert-info { border-left-color: var(--rh-info); }
.rh-alert.rh-alert-info:hover { background: #EEF2FF; }
.rh-alert-ico {
    width: 34px; height: 34px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;
    background: #FEE2E2; color: var(--rh-danger); flex-shrink: 0;
}
.rh-alert-warn .rh-alert-ico { background: #FEF3C7; color: var(--rh-warn); }
.rh-alert-info .rh-alert-ico { background: #E0E7FF; color: var(--rh-info); }
.rh-alert-body { flex: 1; min-width: 0; }
.rh-alert-title { font-size: .82rem; font-weight: 700; color: var(--rh-ink); line-height: 1.15; }
.rh-alert-sub { font-size: .7rem; color: var(--rh-mute); margin-top: .1rem; }
.rh-alert-arrow { color: var(--rh-mute-2); }

/* ── KPI CARDS ── */
.rh-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .85rem; margin-bottom: 1.25rem; }
@media (max-width: 992px) { .rh-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .rh-kpi-grid { grid-template-columns: 1fr; } }
.rh-kpi {
    background: #fff; border: 1px solid var(--rh-line); border-radius: 8px;
    padding: .95rem 1rem; display: flex; flex-direction: column; gap: .5rem;
    text-decoration: none; color: inherit; transition: border-color .15s, transform .15s;
    position: relative; overflow: hidden;
}
.rh-kpi:hover { border-color: var(--rh-brand); color: inherit; transform: translateY(-1px); }
.rh-kpi-head { display: flex; align-items: center; justify-content: space-between; }
.rh-kpi-lbl { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; color: var(--rh-mute); font-weight: 700; }
.rh-kpi-ico {
    width: 30px; height: 30px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;
    background: var(--rh-brand-soft); color: var(--rh-brand); font-size: .8rem;
}
.rh-kpi-val { font-size: 1.55rem; font-weight: 800; color: var(--rh-ink); line-height: 1; }
.rh-kpi-val small { font-size: .72rem; color: var(--rh-mute); font-weight: 500; margin-left: .25rem; }
.rh-kpi-foot { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
.rh-trend { display: inline-flex; align-items: center; gap: .2rem; font-size: .72rem; font-weight: 700; padding: .1rem .45rem; border-radius: 4px; }
/* Convention RH : hausse embauche = bon ; hausse masse salariale = neutre à surveiller */
.rh-trend-up   { color: var(--rh-brand); background: var(--rh-brand-soft); }
.rh-trend-down { color: var(--rh-danger); background: #FEE2E2; }
.rh-trend-flat { color: var(--rh-mute); background: var(--rh-line-2); }
.rh-kpi-sub { font-size: .7rem; color: var(--rh-mute); }

.rh-spark { display: flex; align-items: flex-end; gap: 2px; height: 22px; }
.rh-spark-bar { width: 5px; background: var(--rh-brand); border-radius: 1px; opacity: .8; }

/* ── SECTIONS ── */
.rh-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.rh-row-2 { grid-template-columns: 1fr 1fr; }
@media (max-width: 991px) { .rh-row-2 { grid-template-columns: 1fr; } }

.rh-card { background: #fff; border: 1px solid var(--rh-line); border-radius: 8px; overflow: hidden; }
.rh-card-hd {
    display: flex; align-items: center; justify-content: space-between;
    padding: .85rem 1.1rem; border-bottom: 1px solid var(--rh-line-2);
}
.rh-card-hd h6 { margin: 0; font-size: .85rem; font-weight: 700; color: var(--rh-ink); display: flex; align-items: center; gap: .5rem; }
.rh-card-hd-ico {
    width: 26px; height: 26px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center;
    font-size: .75rem;
}
.rh-link { font-size: .72rem; font-weight: 600; color: var(--rh-primary); text-decoration: none; }
.rh-link:hover { text-decoration: underline; }
.rh-card-bd { padding: 1rem 1.1rem; }
.rh-card-tight { padding: 0; }

.rh-list-row {
    display: flex; align-items: center; justify-content: space-between; gap: .75rem;
    padding: .6rem 1.1rem; border-bottom: 1px solid var(--rh-line-2);
    font-size: .8rem; transition: background .12s;
}
.rh-list-row:last-child { border-bottom: 0; }
.rh-list-row:hover { background: #FAFAFA; }
.rh-list-row-main { min-width: 0; flex: 1; }
.rh-list-row-main a { color: var(--rh-ink); font-weight: 600; text-decoration: none; }
.rh-list-row-main a:hover { color: var(--rh-primary); }
.rh-list-row-meta { font-size: .7rem; color: var(--rh-mute); margin-top: .1rem; }
.rh-list-row-right { text-align: right; flex-shrink: 0; }
.rh-list-row-right strong { color: var(--rh-ink); font-size: .82rem; }

.rh-badge {
    font-size: .68rem; padding: .12rem .5rem; border-radius: 4px; font-weight: 700; display: inline-block;
}
.rh-badge-danger  { background: #FEE2E2; color: var(--rh-danger); }
.rh-badge-warn    { background: #FEF3C7; color: var(--rh-warn); }
.rh-badge-info    { background: #E0E7FF; color: var(--rh-info); }
.rh-badge-ok      { background: var(--rh-brand-soft); color: var(--rh-brand); }
.rh-badge-mute    { background: var(--rh-line-2); color: var(--rh-mute); }
.rh-empty { padding: 1.75rem 1rem; text-align: center; color: var(--rh-mute-2); font-size: .8rem; }
.rh-empty i { display: block; font-size: 1.35rem; margin-bottom: .35rem; opacity: .55; }

/* Chart bars */
.rh-chart-bars { padding: 1rem 1.1rem 1.25rem; }
.rh-bars { display: flex; align-items: flex-end; gap: 6px; height: 130px; margin-bottom: .5rem; }
.rh-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: .35rem; height: 100%; }
.rh-bar-outer { width: 100%; display: flex; align-items: flex-end; flex: 1; }
.rh-bar-fill { width: 100%; background: var(--rh-brand); border-radius: 3px 3px 0 0; min-height: 2px; }
.rh-bar-col.is-current .rh-bar-fill { background: var(--rh-brand-deep); }
.rh-bar-col:hover .rh-bar-fill { opacity: .85; }
.rh-bar-label { font-size: .68rem; color: var(--rh-mute); text-transform: capitalize; font-weight: 600; }
.rh-bar-val { font-size: .68rem; color: var(--rh-ink); font-weight: 700; }
.rh-chart-total { padding-top: .5rem; border-top: 1px dashed var(--rh-line); display: flex; justify-content: space-between; font-size: .78rem; }
.rh-chart-total .rh-mute { color: var(--rh-mute); }

/* Donut contrats */
.rh-donut-wrap { display: flex; align-items: center; gap: 1.25rem; padding: 1rem 1.1rem; flex-wrap: wrap; }
.rh-donut { width: 140px; height: 140px; position: relative; flex-shrink: 0; }
.rh-donut svg { transform: rotate(-90deg); }
.rh-donut-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.rh-donut-center strong { font-size: 1.35rem; font-weight: 800; color: var(--rh-ink); line-height: 1; }
.rh-donut-center span { font-size: .68rem; color: var(--rh-mute); text-transform: uppercase; letter-spacing: .05em; margin-top: .2rem; }
.rh-donut-legend { flex: 1; min-width: 160px; display: flex; flex-direction: column; gap: .55rem; }
.rh-legend-item { display: flex; align-items: center; justify-content: space-between; font-size: .78rem; }
.rh-legend-key { display: inline-flex; align-items: center; gap: .5rem; color: var(--rh-ink-2); }
.rh-legend-dot { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }
.rh-legend-val { font-weight: 700; color: var(--rh-ink); }
.rh-legend-pct { color: var(--rh-mute); font-weight: 600; font-size: .72rem; margin-left: .35rem; }

/* Podium départements */
.rh-podium { padding: .8rem 1.1rem 1rem; display: flex; flex-direction: column; gap: .55rem; }
.rh-podium-row { display: flex; align-items: center; gap: .65rem; }
.rh-podium-rank {
    width: 22px; height: 22px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 700; flex-shrink: 0;
    background: var(--rh-line-2); color: var(--rh-mute);
}
.rh-podium-row:nth-child(1) .rh-podium-rank { background: #FEF3C7; color: #B45309; }
.rh-podium-row:nth-child(2) .rh-podium-rank { background: #F1F5F9; color: #64748B; }
.rh-podium-row:nth-child(3) .rh-podium-rank { background: #FFEDD5; color: #EA580C; }
.rh-podium-info { flex: 1; min-width: 0; }
.rh-podium-name { font-size: .78rem; font-weight: 700; color: var(--rh-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rh-podium-bar { height: 4px; background: var(--rh-line-2); border-radius: 2px; margin-top: .3rem; overflow: hidden; }
.rh-podium-bar-fill { height: 100%; background: var(--rh-brand); border-radius: 2px; }
.rh-podium-vals { text-align: right; flex-shrink: 0; }
.rh-podium-vals strong { display: block; font-size: .78rem; color: var(--rh-ink); font-weight: 700; }
.rh-podium-vals span { font-size: .65rem; color: var(--rh-mute); }

/* CDD expirants */
.rh-cdd-row { padding: .7rem 1.1rem; border-bottom: 1px solid var(--rh-line-2); }
.rh-cdd-row:last-child { border-bottom: 0; }
.rh-cdd-head { display: flex; justify-content: space-between; align-items: baseline; gap: .5rem; }
.rh-cdd-name { font-size: .8rem; font-weight: 700; color: var(--rh-ink); }
.rh-cdd-name a { color: inherit; text-decoration: none; }
.rh-cdd-name a:hover { color: var(--rh-primary); }
.rh-cdd-jrs { font-size: .72rem; font-weight: 700; }
.rh-cdd-meta { font-size: .68rem; color: var(--rh-mute); margin-top: .1rem; }
.rh-cdd-prog { height: 4px; background: var(--rh-line-2); border-radius: 2px; margin-top: .45rem; overflow: hidden; }
.rh-cdd-prog-fill { height: 100%; border-radius: 2px; }

/* Anniversaires */
.rh-birthday {
    padding: .55rem 1.1rem; border-bottom: 1px dashed var(--rh-line-2);
    display: flex; align-items: center; gap: .7rem; font-size: .8rem;
}
.rh-birthday:last-child { border-bottom: 0; }
.rh-birthday-day {
    width: 38px; height: 38px; border-radius: 8px; background: #FEF3C7; color: #B45309;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: .95rem; flex-shrink: 0;
}
.rh-birthday-info { flex: 1; min-width: 0; }
.rh-birthday-name { font-weight: 700; color: var(--rh-ink); }
.rh-birthday-poste { font-size: .7rem; color: var(--rh-mute); }

@media (max-width: 575px) {
    .rh-head { padding: .85rem 1rem; }
    .rh-head h1 { font-size: 1rem; }
    .rh-head-actions .rh-btn { padding: .45rem .65rem; font-size: .72rem; }
    .rh-donut-wrap { flex-direction: column; align-items: stretch; }
    .rh-donut { align-self: center; }
}
</style>
@endpush

@section('content')
<div class="rh-shell">

{{-- ═════════════════ HEADER ═════════════════ --}}
<div class="rh-head">
    <div class="rh-head-title">
        <span class="rh-head-emblem"><i class="fas fa-users"></i></span>
        <div>
            <h1>RH &amp; Paie</h1>
            <p>Pilotage — {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
        </div>
    </div>
    <div class="rh-head-actions">
        @can('create:employee')
        <a href="{{ route('rh.employees.create') }}" class="rh-btn rh-btn-primary">
            <i class="fas fa-user-plus"></i>Nouvel employé
        </a>
        @endcan
        @can('create:absence')
        <a href="{{ route('rh.absences.create') }}" class="rh-btn rh-btn-outline">
            <i class="fas fa-plane-departure"></i>Nouvelle absence
        </a>
        @endcan
        @can('create:paie')
        <a href="{{ route('rh.paie.index') }}" class="rh-btn rh-btn-outline">
            <i class="fas fa-file-invoice-dollar"></i>Bulletins
        </a>
        @endcan
    </div>
</div>

{{-- ═════════════════ PRIORITY BAR ═════════════════ --}}
@php
    $alerts = collect();
    if ($nbAvancementsAttente > 0) $alerts->push([
        'type'=>'warn', 'ico'=>'fa-wand-magic-sparkles',
        'title'=>$nbAvancementsAttente.' avancement'.($nbAvancementsAttente>1?'s':'').' auto à valider',
        'sub'=>'Durée max atteinte au grade actuel',
        'url'=>'#rh-avancements-attente'
    ]);
    if ($absencesRetardApprobation > 0) $alerts->push([
        'type'=>'danger', 'ico'=>'fa-hourglass-half',
        'title'=>$absencesRetardApprobation.' absence'.($absencesRetardApprobation>1?'s':'').' en attente > 3j',
        'sub'=>'Approbation urgente',
        'url'=>route('rh.absences.index')
    ]);
    if ($nbCddProchainement > 0) $alerts->push([
        'type'=>'warn', 'ico'=>'fa-calendar-xmark',
        'title'=>$nbCddProchainement.' CDD arrive'.($nbCddProchainement>1?'nt':'').' à échéance',
        'sub'=>'Fin de contrat < 30 jours',
        'url'=>route('rh.employees.index')
    ]);
    if ($bulletinsBrouillon > 0) $alerts->push([
        'type'=>'warn', 'ico'=>'fa-file-invoice-dollar',
        'title'=>$bulletinsBrouillon.' bulletin'.($bulletinsBrouillon>1?'s':'').' brouillon',
        'sub'=>'À valider',
        'url'=>route('rh.paie.index')
    ]);
    if ($departsEnCours > 0) $alerts->push([
        'type'=>'warn', 'ico'=>'fa-door-open',
        'title'=>$departsEnCours.' départ'.($departsEnCours>1?'s':'').' en cours',
        'sub'=>'Processus à finaliser',
        'url'=>route('rh.departs.index')
    ]);
    if ($sanctionsActives > 0) $alerts->push([
        'type'=>'info', 'ico'=>'fa-gavel',
        'title'=>$sanctionsActives.' sanction'.($sanctionsActives>1?'s':'').' active'.($sanctionsActives>1?'s':''),
        'sub'=>'Suivi disciplinaire',
        'url'=>route('rh.sanctions.index')
    ]);
    if ($evaluationsEnCours > 0) $alerts->push([
        'type'=>'info', 'ico'=>'fa-star',
        'title'=>$evaluationsEnCours.' évaluation'.($evaluationsEnCours>1?'s':'').' en cours',
        'sub'=>'Campagne active',
        'url'=>route('rh.evaluations-performance.index')
    ]);
@endphp
@if($alerts->count())
<div class="rh-alerts">
    @foreach($alerts as $a)
    <a href="{{ $a['url'] }}" class="rh-alert rh-alert-{{ $a['type'] }}">
        <span class="rh-alert-ico"><i class="fas {{ $a['ico'] }}"></i></span>
        <div class="rh-alert-body">
            <div class="rh-alert-title">{{ $a['title'] }}</div>
            <div class="rh-alert-sub">{{ $a['sub'] }}</div>
        </div>
        <i class="fas fa-chevron-right rh-alert-arrow"></i>
    </a>
    @endforeach
</div>
@endif

{{-- ═════════════════ KPIS PRINCIPAUX ═════════════════ --}}
<div class="rh-kpi-grid">
    {{-- KPI 1 — Effectif actif --}}
    <a href="{{ route('rh.employees.index') }}" class="rh-kpi">
        <div class="rh-kpi-head">
            <span class="rh-kpi-lbl">Effectif actif</span>
            <span class="rh-kpi-ico"><i class="fas fa-users"></i></span>
        </div>
        <div class="rh-kpi-val">{{ $effectifActif }}</div>
        <div class="rh-kpi-foot">
            <span class="rh-kpi-sub">
                @if($embauchesMois > 0)<span style="color:var(--rh-brand); font-weight:700;">+{{ $embauchesMois }}</span> embauche{{ $embauchesMois>1?'s':'' }} ce mois@else Aucune embauche ce mois @endif
            </span>
        </div>
    </a>

    {{-- KPI 2 — Masse salariale --}}
    <a href="{{ route('rh.paie.index') }}" class="rh-kpi">
        <div class="rh-kpi-head">
            <span class="rh-kpi-lbl">Masse salariale ce mois</span>
            <span class="rh-kpi-ico" style="background:#FEF3C7; color:#B45309;"><i class="fas fa-coins"></i></span>
        </div>
        <div class="rh-kpi-val">{{ number_format($masseSalarialeMois / 1000, 0, ',', ' ') }}<small>K XAF</small></div>
        <div class="rh-kpi-foot">
            @if($tendanceMasse > 0)
                <span class="rh-trend rh-trend-down"><i class="fas fa-arrow-up"></i>{{ $tendanceMasse }}%</span>
            @elseif($tendanceMasse < 0)
                <span class="rh-trend rh-trend-up"><i class="fas fa-arrow-down"></i>{{ abs($tendanceMasse) }}%</span>
            @else
                <span class="rh-trend rh-trend-flat"><i class="fas fa-minus"></i>0%</span>
            @endif
            <div class="rh-spark">
                @foreach($masseSix as $m)
                    <div class="rh-spark-bar" style="height:{{ max(15, ($m['montant'] / $masseMax) * 100) }}%;"></div>
                @endforeach
            </div>
        </div>
    </a>

    {{-- KPI 3 — Absences --}}
    <a href="{{ route('rh.absences.index') }}" class="rh-kpi">
        <div class="rh-kpi-head">
            <span class="rh-kpi-lbl">Absences en attente</span>
            <span class="rh-kpi-ico" style="background:#FEE2E2; color:var(--rh-danger);"><i class="fas fa-plane-departure"></i></span>
        </div>
        <div class="rh-kpi-val">{{ $absencesEnAttente }}</div>
        <div class="rh-kpi-foot">
            <span class="rh-kpi-sub">{{ $absencesEnCours }} en cours aujourd'hui</span>
        </div>
    </a>

    {{-- KPI 4 — Bulletins --}}
    <a href="{{ route('rh.paie.index') }}" class="rh-kpi">
        <div class="rh-kpi-head">
            <span class="rh-kpi-lbl">Bulletins ce mois</span>
            <span class="rh-kpi-ico" style="background:#E0E7FF; color:var(--rh-info);"><i class="fas fa-file-invoice-dollar"></i></span>
        </div>
        <div class="rh-kpi-val">{{ $bulletinsMois }}</div>
        <div class="rh-kpi-foot">
            @if($tendanceBulletins > 0)
                <span class="rh-trend rh-trend-up"><i class="fas fa-arrow-up"></i>{{ $tendanceBulletins }}%</span>
            @elseif($tendanceBulletins < 0)
                <span class="rh-trend rh-trend-down"><i class="fas fa-arrow-down"></i>{{ abs($tendanceBulletins) }}%</span>
            @else
                <span class="rh-trend rh-trend-flat"><i class="fas fa-minus"></i>0%</span>
            @endif
            <span class="rh-kpi-sub">{{ $bulletinsBrouillon }} brouillon(s)</span>
        </div>
    </a>
</div>

{{-- ═════════════════ CHARTS ═════════════════ --}}
<div class="rh-row rh-row-2">
    {{-- Masse salariale 6 mois --}}
    <div class="rh-card">
        <div class="rh-card-hd">
            <h6>
                <span class="rh-card-hd-ico" style="background:var(--rh-brand-soft); color:var(--rh-brand);"><i class="fas fa-chart-column"></i></span>
                Masse salariale (6 mois)
            </h6>
            <a href="{{ route('rh.paie.index') }}" class="rh-link">Détail →</a>
        </div>
        <div class="rh-chart-bars">
            <div class="rh-bars">
                @foreach($masseSix as $i => $m)
                <div class="rh-bar-col {{ $i === count($masseSix)-1 ? 'is-current' : '' }}"
                     title="{{ $m['label'] }} : {{ number_format($m['montant']/1000, 0, ',', ' ') }} K XAF">
                    <div class="rh-bar-val">{{ $m['montant'] > 0 ? round($m['montant']/1000) : '' }}</div>
                    <div class="rh-bar-outer">
                        <div class="rh-bar-fill" style="height:{{ max(2, ($m['montant'] / $masseMax) * 100) }}%;"></div>
                    </div>
                    <div class="rh-bar-label">{{ $m['label'] }}</div>
                </div>
                @endforeach
            </div>
            <div class="rh-chart-total">
                <span class="rh-mute">Total 6 mois</span>
                <strong>{{ number_format(array_sum(array_column($masseSix, 'montant')), 0, ',', ' ') }} XAF</strong>
            </div>
        </div>
    </div>

    {{-- Top départements --}}
    <div class="rh-card">
        <div class="rh-card-hd">
            <h6>
                <span class="rh-card-hd-ico" style="background:#E0E7FF; color:var(--rh-info);"><i class="fas fa-building"></i></span>
                Top départements par effectif
            </h6>
            <a href="{{ route('rh.employees.index') }}" class="rh-link">Voir tout →</a>
        </div>
        <div class="rh-podium">
            @forelse($topDepartements as $d)
            <div class="rh-podium-row">
                <div class="rh-podium-rank">{{ $loop->iteration }}</div>
                <div class="rh-podium-info">
                    <div class="rh-podium-name">{{ \Illuminate\Support\Str::limit($d->departement, 35) }}</div>
                    <div class="rh-podium-bar"><div class="rh-podium-bar-fill" style="width:{{ min(100, ($d->n / $topDeptMax) * 100) }}%;"></div></div>
                </div>
                <div class="rh-podium-vals">
                    <strong>{{ $d->n }}</strong>
                    <span>{{ round($d->n / max(1,$effectifActif) * 100) }}%</span>
                </div>
            </div>
            @empty
            <div class="rh-empty"><i class="fas fa-building"></i>Aucun département renseigné</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═════════════════ Contrats + CDD expirants ═════════════════ --}}
<div class="rh-row rh-row-2">
    {{-- Donut répartition contrats --}}
    <div class="rh-card">
        <div class="rh-card-hd">
            <h6>
                <span class="rh-card-hd-ico" style="background:var(--rh-brand-soft); color:var(--rh-brand);"><i class="fas fa-file-contract"></i></span>
                Répartition des contrats
            </h6>
            <a href="{{ route('rh.employees.index') }}" class="rh-link">Employés →</a>
        </div>
        @php
            $c = 251.327;
            $palette = ['#059669', '#4F46E5', '#D97706', '#DC2626', '#0891B2', '#7C3AED', '#B45309', '#DB2777'];
            $offset = 0;
            $segments = [];
            $i = 0;
            foreach ($repartitionContrats as $type => $n) {
                $len = ($n / $totalContrats) * $c;
                $segments[] = [
                    'type'  => $type,
                    'n'     => $n,
                    'pct'   => round($n / $totalContrats * 100, 1),
                    'len'   => $len,
                    'off'   => $offset,
                    'color' => $palette[$i % count($palette)],
                ];
                $offset += $len;
                $i++;
            }
        @endphp
        <div class="rh-donut-wrap">
            <div class="rh-donut">
                <svg width="140" height="140" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#F1F5F9" stroke-width="14"/>
                    @foreach($segments as $s)
                    <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $s['color'] }}" stroke-width="14"
                            stroke-dasharray="{{ $s['len'] }} {{ $c - $s['len'] }}" stroke-dashoffset="{{ -$s['off'] }}"/>
                    @endforeach
                </svg>
                <div class="rh-donut-center">
                    <strong>{{ $effectifActif }}</strong>
                    <span>Employés</span>
                </div>
            </div>
            <div class="rh-donut-legend">
                @forelse($segments as $s)
                <div class="rh-legend-item">
                    <span class="rh-legend-key"><span class="rh-legend-dot" style="background:{{ $s['color'] }};"></span>{{ \Illuminate\Support\Str::limit($s['type'], 20) }}</span>
                    <span><span class="rh-legend-val">{{ $s['n'] }}</span><span class="rh-legend-pct">{{ $s['pct'] }}%</span></span>
                </div>
                @empty
                <div class="rh-empty" style="padding:.5rem 0;">Aucune donnée</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- CDD arrivant à échéance --}}
    <div class="rh-card">
        <div class="rh-card-hd">
            <h6>
                <span class="rh-card-hd-ico" style="background:#FEF3C7; color:#B45309;"><i class="fas fa-calendar-xmark"></i></span>
                CDD arrivant à échéance
                <span class="rh-badge rh-badge-mute ms-1">{{ $nbCddProchainement }}</span>
            </h6>
            <a href="{{ route('rh.employees.index') }}" class="rh-link">Employés →</a>
        </div>
        <div class="rh-card-tight">
            @forelse($cddProchainement as $emp)
            @php
                $jrs = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($emp->date_fin_contrat)->startOfDay(), false);
                $pct = max(0, min(100, 100 - ($jrs / 30 * 100)));
                $color = $jrs <= 7 ? 'var(--rh-danger)' : ($jrs <= 15 ? 'var(--rh-warn)' : 'var(--rh-info)');
            @endphp
            <div class="rh-cdd-row">
                <div class="rh-cdd-head">
                    <div class="rh-cdd-name">
                        <a href="{{ route('rh.employees.show', $emp) }}">{{ $emp->prenoms }} {{ $emp->noms }}</a>
                    </div>
                    <span class="rh-cdd-jrs" style="color:{{ $color }};"><i class="fas fa-clock"></i> {{ $jrs }}j</span>
                </div>
                <div class="rh-cdd-meta">
                    {{ $emp->poste ?: '—' }} · {{ $emp->departement ?: 'Sans département' }} · Fin le {{ \Carbon\Carbon::parse($emp->date_fin_contrat)->format('d/m/Y') }}
                </div>
                <div class="rh-cdd-prog"><div class="rh-cdd-prog-fill" style="width:{{ $pct }}%; background:{{ $color }};"></div></div>
            </div>
            @empty
            <div class="rh-empty"><i class="fas fa-check-circle"></i>Aucun CDD à échéance dans les 30 jours</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═════════════════ Avancements automatiques en attente ═════════════════ --}}
<div class="rh-card" id="rh-avancements-attente" style="margin-bottom:1rem;">
    <div class="rh-card-hd">
        <h6>
            <span class="rh-card-hd-ico" style="background:var(--rh-brand-soft); color:var(--rh-brand);"><i class="fas fa-wand-magic-sparkles"></i></span>
            Avancements automatiques en attente de validation
            @if($nbAvancementsAttente > 0)<span class="rh-badge rh-badge-warn ms-1">{{ $nbAvancementsAttente }}</span>@endif
        </h6>
        @can('update:employee')
        <form action="{{ route('rh.avancements.detecter-auto') }}" method="POST" class="d-inline">@csrf
            <button class="rh-btn rh-btn-outline" title="Analyse les employés et propose ceux qui ont atteint la durée max à leur grade actuel">
                <i class="fas fa-magnifying-glass"></i> Détecter maintenant
            </button>
        </form>
        @endcan
    </div>
    <div class="rh-card-tight">
        @forelse($avancementsAttente as $av)
        <div class="rh-list-row" style="align-items:flex-start;">
            <div class="rh-list-row-main">
                @if($av->employee)
                    <a href="{{ route('rh.employees.avancements.index', $av->employee) }}">{{ $av->employee->prenoms }} {{ $av->employee->noms }}</a>
                @else
                    <span class="text-muted">Employé supprimé</span>
                @endif
                <div class="rh-list-row-meta">
                    @if($av->gradePrecedent)<span class="rh-badge rh-badge-mute">{{ $av->gradePrecedent->code }}</span>@endif
                    <i class="fas fa-arrow-right" style="font-size:.65rem; color:var(--rh-mute-2); margin:0 .25rem;"></i>
                    <span class="rh-badge rh-badge-ok">{{ $av->grade->code }} · {{ $av->grade->libelle }}</span>
                    · {{ $av->motif }}
                </div>
            </div>
            @can('update:employee')
            <div class="rh-list-row-right d-flex gap-1" style="flex-shrink:0;">
                <form action="{{ route('rh.avancements.valider', $av) }}" method="POST" class="d-inline">@csrf
                    <button class="rh-btn rh-btn-primary" style="padding:.35rem .65rem; font-size:.72rem;" title="Valider l'avancement — le grade sera mis à jour">
                        <i class="fas fa-check"></i> Valider
                    </button>
                </form>
                <button type="button" class="rh-btn rh-btn-outline" style="padding:.35rem .65rem; font-size:.72rem; color:var(--rh-danger); border-color:var(--rh-danger);"
                        data-bs-toggle="modal" data-bs-target="#refuserAv-{{ $av->id }}">
                    <i class="fas fa-xmark"></i> Refuser
                </button>
            </div>
            @endcan
        </div>

        @can('update:employee')
        <div class="modal fade" id="refuserAv-{{ $av->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('rh.avancements.refuser', $av) }}" method="POST" class="modal-content">@csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Refuser l'avancement — {{ $av->employee?->prenoms }} {{ $av->employee?->noms }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted" style="font-size:.85rem;">
                            Vers grade : <strong>{{ $av->grade->libelle }}</strong>. L'employé conservera son grade actuel.
                        </p>
                        <label class="form-label">Motif du refus <span class="text-danger">*</span></label>
                        <textarea name="motif_refus" class="form-control" rows="3" required minlength="5" maxlength="500" placeholder="Ex : Critères de qualification non remplis"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button class="btn btn-danger">Refuser l'avancement</button>
                    </div>
                </form>
            </div>
        </div>
        @endcan
        @empty
        <div class="rh-empty"><i class="fas fa-check-circle"></i>Aucun avancement automatique en attente.
            @can('update:employee')
            <br><small>Cliquez sur « Détecter maintenant » pour analyser les employés éligibles.</small>
            @endcan
        </div>
        @endforelse
    </div>
</div>

{{-- ═════════════════ Absences + Embauches récentes ═════════════════ --}}
<div class="rh-row rh-row-2">
    {{-- Absences en attente --}}
    <div class="rh-card">
        <div class="rh-card-hd">
            <h6>
                <span class="rh-card-hd-ico" style="background:#FEE2E2; color:var(--rh-danger);"><i class="fas fa-plane-departure"></i></span>
                Absences à valider
                @if($absencesEnAttente > 0)<span class="rh-badge rh-badge-danger ms-1">{{ $absencesEnAttente }}</span>@endif
            </h6>
            <a href="{{ route('rh.absences.index') }}" class="rh-link">Tout voir →</a>
        </div>
        <div class="rh-card-tight">
            @forelse($dernieresAbsences as $a)
            @php
                $duree = $a->debut && $a->fin
                    ? (int) \Carbon\Carbon::parse($a->debut)->startOfDay()->diffInDays(\Carbon\Carbon::parse($a->fin)->startOfDay()) + 1
                    : null;
            @endphp
            <div class="rh-list-row">
                <div class="rh-list-row-main">
                    <a href="{{ route('rh.absences.show', $a) }}">{{ \Illuminate\Support\Str::limit($a->label ?: 'Absence #'.$a->id, 42) }}</a>
                    <div class="rh-list-row-meta">
                        @if($a->employee)<i class="fas fa-user"></i> {{ $a->employee->prenoms }} {{ $a->employee->noms }}@endif
                        @if($a->debut) · Du {{ \Carbon\Carbon::parse($a->debut)->format('d/m') }}@endif
                        @if($a->fin) au {{ \Carbon\Carbon::parse($a->fin)->format('d/m/Y') }}@endif
                    </div>
                </div>
                <div class="rh-list-row-right">
                    @if($duree)<span class="rh-badge rh-badge-warn">{{ $duree }}j</span>@endif
                    <div class="rh-list-row-meta">{{ $a->created_at?->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div class="rh-empty"><i class="fas fa-check-circle"></i>Aucune absence en attente</div>
            @endforelse
        </div>
    </div>

    {{-- Derniers embauches --}}
    <div class="rh-card">
        <div class="rh-card-hd">
            <h6>
                <span class="rh-card-hd-ico" style="background:var(--rh-brand-soft); color:var(--rh-brand);"><i class="fas fa-user-plus"></i></span>
                Embauches récentes
            </h6>
            <a href="{{ route('rh.employees.index') }}" class="rh-link">Tout voir →</a>
        </div>
        <div class="rh-card-tight">
            @forelse($derniersEmbauches as $emp)
            <div class="rh-list-row">
                <div class="rh-list-row-main">
                    <a href="{{ route('rh.employees.show', $emp) }}">{{ $emp->prenoms }} {{ $emp->noms }}</a>
                    <div class="rh-list-row-meta">
                        {{ $emp->poste ?: '—' }} @if($emp->departement) · {{ $emp->departement }}@endif
                    </div>
                </div>
                <div class="rh-list-row-right">
                    @if($emp->type_contrat)<span class="rh-badge rh-badge-info">{{ $emp->type_contrat }}</span>@endif
                    <div class="rh-list-row-meta">{{ \Carbon\Carbon::parse($emp->date_embauche)->format('d/m/Y') }}</div>
                </div>
            </div>
            @empty
            <div class="rh-empty"><i class="fas fa-inbox"></i>Aucune embauche enregistrée</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═════════════════ Anniversaires + Activité RH ═════════════════ --}}
<div class="rh-row rh-row-2">
    {{-- Anniversaires du mois --}}
    <div class="rh-card">
        <div class="rh-card-hd">
            <h6>
                <span class="rh-card-hd-ico" style="background:#FEF3C7; color:#B45309;"><i class="fas fa-cake-candles"></i></span>
                Anniversaires ce mois
                <span class="rh-badge rh-badge-mute ms-1">{{ $anniversairesMois->count() }}</span>
            </h6>
        </div>
        <div class="rh-card-tight" style="max-height:280px; overflow-y:auto;">
            @forelse($anniversairesMois as $emp)
            <div class="rh-birthday">
                <div class="rh-birthday-day">{{ \Carbon\Carbon::parse($emp->date_naissance)->format('d') }}</div>
                <div class="rh-birthday-info">
                    <div class="rh-birthday-name">{{ $emp->prenoms }} {{ $emp->noms }}</div>
                    <div class="rh-birthday-poste">{{ $emp->poste ?: '—' }}</div>
                </div>
                @php $age = \Carbon\Carbon::parse($emp->date_naissance)->age; @endphp
                <span class="rh-badge rh-badge-mute">{{ $age }} ans</span>
            </div>
            @empty
            <div class="rh-empty"><i class="fas fa-cake-candles"></i>Aucun anniversaire ce mois</div>
            @endforelse
        </div>
    </div>

    {{-- Synthèse activité --}}
    <div class="rh-card">
        <div class="rh-card-hd">
            <h6>
                <span class="rh-card-hd-ico" style="background:#E0E7FF; color:var(--rh-info);"><i class="fas fa-chart-simple"></i></span>
                Activité RH en cours
            </h6>
        </div>
        <div class="rh-card-bd">
            <div class="row g-2 text-center">
                <div class="col-6 col-md-4">
                    <a href="{{ route('rh.recrutements.index') }}" style="display:block; padding:.65rem .35rem; border:1px solid var(--rh-line-2); border-radius:6px; text-decoration:none;">
                        <div style="font-size:1.5rem; font-weight:800; color:var(--rh-info);">{{ $recrutementsOuverts }}</div>
                        <div style="font-size:.68rem; color:var(--rh-mute); text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Recrutements</div>
                    </a>
                </div>
                <div class="col-6 col-md-4">
                    <a href="{{ route('rh.missions.index') }}" style="display:block; padding:.65rem .35rem; border:1px solid var(--rh-line-2); border-radius:6px; text-decoration:none;">
                        <div style="font-size:1.5rem; font-weight:800; color:var(--rh-brand);">{{ $missionsActives }}</div>
                        <div style="font-size:.68rem; color:var(--rh-mute); text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Missions</div>
                    </a>
                </div>
                <div class="col-6 col-md-4">
                    <a href="{{ route('rh.evaluations-performance.index') }}" style="display:block; padding:.65rem .35rem; border:1px solid var(--rh-line-2); border-radius:6px; text-decoration:none;">
                        <div style="font-size:1.5rem; font-weight:800; color:var(--rh-accent);">{{ $evaluationsEnCours }}</div>
                        <div style="font-size:.68rem; color:var(--rh-mute); text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Évaluations</div>
                    </a>
                </div>
                <div class="col-6 col-md-4">
                    <a href="{{ route('rh.sanctions.index') }}" style="display:block; padding:.65rem .35rem; border:1px solid var(--rh-line-2); border-radius:6px; text-decoration:none;">
                        <div style="font-size:1.5rem; font-weight:800; color:var(--rh-warn);">{{ $sanctionsActives }}</div>
                        <div style="font-size:.68rem; color:var(--rh-mute); text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Sanctions</div>
                    </a>
                </div>
                <div class="col-6 col-md-4">
                    <a href="{{ route('rh.departs.index') }}" style="display:block; padding:.65rem .35rem; border:1px solid var(--rh-line-2); border-radius:6px; text-decoration:none;">
                        <div style="font-size:1.5rem; font-weight:800; color:var(--rh-danger);">{{ $departsEnCours }}</div>
                        <div style="font-size:.68rem; color:var(--rh-mute); text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Départs</div>
                    </a>
                </div>
                <div class="col-6 col-md-4">
                    <a href="{{ route('rh.employees.index') }}" style="display:block; padding:.65rem .35rem; border:1px solid var(--rh-line-2); border-radius:6px; text-decoration:none;">
                        <div style="font-size:1.5rem; font-weight:800; color:var(--rh-mute);">{{ $departsMois }}</div>
                        <div style="font-size:.68rem; color:var(--rh-mute); text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Départs / mois</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</div>{{-- /.rh-shell --}}
@endsection
