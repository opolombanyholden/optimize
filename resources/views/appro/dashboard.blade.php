@extends('layouts.app')

@section('title', 'Dashboard Achats & Moyens Généraux')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   APPRO DASHBOARD — Design pro type Coupa / SAP Ariba
   Charte OptimiZe : couleur module #D97706 (orange), accent #FCD34D
═══════════════════════════════════════════════════════════════ */
:root {
    --ap-brand: #D97706;
    --ap-brand-soft: #FEF3C7;
    --ap-brand-tint: #FFFBEB;
    --ap-ink: #0F172A;
    --ap-ink-2: #334155;
    --ap-mute: #64748B;
    --ap-mute-2: #94A3B8;
    --ap-line: #E5E7EB;
    --ap-line-2: #F1F5F9;
    --ap-bg: #F8FAFC;
    --ap-danger: #DC2626;
    --ap-warn: #D97706;
    --ap-info: #4F46E5;
    --ap-ok: #059669;
    --ap-primary: #0A66C2;
}
.ap-shell { max-width: 1400px; margin: 0 auto; }

/* ──────── HERO / HEADER ──────── */
.ap-head {
    background: #fff; border: 1px solid var(--ap-line); border-radius: 8px;
    padding: 1rem 1.25rem; margin-bottom: 1rem;
    display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.ap-head-title { display: flex; align-items: center; gap: .85rem; }
.ap-head-emblem {
    width: 44px; height: 44px; border-radius: 8px; background: var(--ap-brand-soft);
    color: var(--ap-brand); display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem;
    flex-shrink: 0;
}
.ap-head h1 { font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--ap-ink); }
.ap-head p  { margin: 0; font-size: .8rem; color: var(--ap-mute); }
.ap-head-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.ap-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem .85rem; font-size: .78rem; font-weight: 600;
    border-radius: 6px; text-decoration: none; border: 1px solid transparent;
    transition: background .15s, border-color .15s, color .15s;
}
.ap-btn-primary { background: var(--ap-brand); color: #fff; }
.ap-btn-primary:hover { background: #B45309; color: #fff; }
.ap-btn-outline { background: #fff; color: var(--ap-ink-2); border-color: var(--ap-line); }
.ap-btn-outline:hover { border-color: var(--ap-brand); color: var(--ap-brand); }

/* ──────── PRIORITY BAR ──────── */
.ap-alerts { display: grid; gap: .65rem; margin-bottom: 1.25rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
.ap-alert {
    display: flex; align-items: center; gap: .75rem; padding: .75rem .9rem;
    background: #fff; border: 1px solid var(--ap-line); border-left: 3px solid var(--ap-danger);
    border-radius: 6px; text-decoration: none; color: inherit; transition: border-color .15s, background .15s;
}
.ap-alert:hover { background: #FEF2F2; border-left-color: var(--ap-danger); color: inherit; }
.ap-alert.ap-alert-warn { border-left-color: var(--ap-warn); }
.ap-alert.ap-alert-warn:hover { background: var(--ap-brand-tint); }
.ap-alert.ap-alert-info { border-left-color: var(--ap-info); }
.ap-alert.ap-alert-info:hover { background: #EEF2FF; }
.ap-alert-ico {
    width: 34px; height: 34px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;
    background: #FEE2E2; color: var(--ap-danger); flex-shrink: 0;
}
.ap-alert-warn .ap-alert-ico { background: var(--ap-brand-soft); color: var(--ap-warn); }
.ap-alert-info .ap-alert-ico { background: #E0E7FF; color: var(--ap-info); }
.ap-alert-body { flex: 1; min-width: 0; }
.ap-alert-title { font-size: .82rem; font-weight: 700; color: var(--ap-ink); line-height: 1.15; }
.ap-alert-sub { font-size: .7rem; color: var(--ap-mute); margin-top: .1rem; }
.ap-alert-arrow { color: var(--ap-mute-2); }

/* ──────── KPI CARDS ──────── */
.ap-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .85rem; margin-bottom: 1.25rem; }
@media (max-width: 992px) { .ap-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .ap-kpi-grid { grid-template-columns: 1fr; } }
.ap-kpi {
    background: #fff; border: 1px solid var(--ap-line); border-radius: 8px;
    padding: .95rem 1rem; display: flex; flex-direction: column; gap: .5rem;
    text-decoration: none; color: inherit; transition: border-color .15s, transform .15s;
    position: relative; overflow: hidden;
}
.ap-kpi:hover { border-color: var(--ap-brand); color: inherit; transform: translateY(-1px); }
.ap-kpi-head { display: flex; align-items: center; justify-content: space-between; }
.ap-kpi-lbl { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; color: var(--ap-mute); font-weight: 700; }
.ap-kpi-ico {
    width: 30px; height: 30px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;
    background: var(--ap-brand-soft); color: var(--ap-brand); font-size: .8rem;
}
.ap-kpi-val { font-size: 1.6rem; font-weight: 800; color: var(--ap-ink); line-height: 1; }
.ap-kpi-val small { font-size: .75rem; color: var(--ap-mute); font-weight: 500; margin-left: .25rem; }
.ap-kpi-foot { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
.ap-trend { display: inline-flex; align-items: center; gap: .2rem; font-size: .72rem; font-weight: 700; padding: .1rem .45rem; border-radius: 4px; }
.ap-trend-up   { color: var(--ap-ok); background: #ECFDF5; }
.ap-trend-down { color: var(--ap-danger); background: #FEE2E2; }
.ap-trend-flat { color: var(--ap-mute); background: var(--ap-line-2); }
.ap-kpi-sub { font-size: .7rem; color: var(--ap-mute); }

/* Mini bars sparkline */
.ap-spark { display: flex; align-items: flex-end; gap: 2px; height: 22px; }
.ap-spark-bar { width: 5px; background: var(--ap-brand); border-radius: 1px; opacity: .8; }

/* ──────── SECTION CARDS ──────── */
.ap-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.ap-row-2 { grid-template-columns: 1fr 1fr; }
.ap-row-3 { grid-template-columns: repeat(3, 1fr); }
@media (max-width: 991px) { .ap-row-2, .ap-row-3 { grid-template-columns: 1fr; } }

.ap-card { background: #fff; border: 1px solid var(--ap-line); border-radius: 8px; overflow: hidden; }
.ap-card-hd {
    display: flex; align-items: center; justify-content: space-between;
    padding: .85rem 1.1rem; border-bottom: 1px solid var(--ap-line-2);
}
.ap-card-hd h6 { margin: 0; font-size: .85rem; font-weight: 700; color: var(--ap-ink); display: flex; align-items: center; gap: .5rem; }
.ap-card-hd-ico {
    width: 26px; height: 26px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center;
    font-size: .75rem;
}
.ap-link { font-size: .72rem; font-weight: 600; color: var(--ap-primary); text-decoration: none; }
.ap-link:hover { text-decoration: underline; }

.ap-card-bd { padding: 1rem 1.1rem; }
.ap-card-tight { padding: 0; }

.ap-list-row {
    display: flex; align-items: center; justify-content: space-between; gap: .75rem;
    padding: .6rem 1.1rem; border-bottom: 1px solid var(--ap-line-2);
    font-size: .8rem; transition: background .12s;
}
.ap-list-row:last-child { border-bottom: 0; }
.ap-list-row:hover { background: #FAFAFA; }
.ap-list-row-main { min-width: 0; flex: 1; }
.ap-list-row-main a { color: var(--ap-ink); font-weight: 600; text-decoration: none; }
.ap-list-row-main a:hover { color: var(--ap-primary); }
.ap-list-row-meta { font-size: .7rem; color: var(--ap-mute); margin-top: .1rem; }
.ap-list-row-right { text-align: right; flex-shrink: 0; }
.ap-list-row-right strong { color: var(--ap-ink); font-size: .82rem; }
.ap-badge {
    font-size: .68rem; padding: .12rem .5rem; border-radius: 4px; font-weight: 700;
    display: inline-block;
}
.ap-badge-danger  { background: #FEE2E2; color: var(--ap-danger); }
.ap-badge-warn    { background: var(--ap-brand-soft); color: var(--ap-warn); }
.ap-badge-info    { background: #E0E7FF; color: var(--ap-info); }
.ap-badge-ok      { background: #D1FAE5; color: var(--ap-ok); }
.ap-badge-mute    { background: var(--ap-line-2); color: var(--ap-mute); }
.ap-empty { padding: 1.75rem 1rem; text-align: center; color: var(--ap-mute-2); font-size: .8rem; }
.ap-empty i { display: block; font-size: 1.35rem; margin-bottom: .35rem; opacity: .55; }

/* ──────── CHARTS SVG ──────── */
.ap-chart-bars { padding: 1rem 1.1rem 1.25rem; }
.ap-bars { display: flex; align-items: flex-end; gap: 6px; height: 130px; margin-bottom: .5rem; }
.ap-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: .35rem; height: 100%; }
.ap-bar-outer { width: 100%; display: flex; align-items: flex-end; flex: 1; }
.ap-bar-fill { width: 100%; background: var(--ap-brand); border-radius: 3px 3px 0 0; transition: opacity .2s; min-height: 2px; }
.ap-bar-col.is-current .ap-bar-fill { background: var(--ap-primary); }
.ap-bar-col:hover .ap-bar-fill { opacity: .85; }
.ap-bar-label { font-size: .68rem; color: var(--ap-mute); text-transform: capitalize; font-weight: 600; }
.ap-bar-val { font-size: .68rem; color: var(--ap-ink); font-weight: 700; }
.ap-chart-total { padding-top: .5rem; border-top: 1px dashed var(--ap-line); display: flex; justify-content: space-between; font-size: .78rem; }
.ap-chart-total .ap-mute { color: var(--ap-mute); }

/* Donut stock */
.ap-donut-wrap { display: flex; align-items: center; gap: 1.25rem; padding: 1rem 1.1rem; flex-wrap: wrap; }
.ap-donut { width: 140px; height: 140px; position: relative; flex-shrink: 0; }
.ap-donut svg { transform: rotate(-90deg); }
.ap-donut-center {
    position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;
}
.ap-donut-center strong { font-size: 1.35rem; font-weight: 800; color: var(--ap-ink); line-height: 1; }
.ap-donut-center span { font-size: .68rem; color: var(--ap-mute); text-transform: uppercase; letter-spacing: .05em; margin-top: .2rem; }
.ap-donut-legend { flex: 1; min-width: 160px; display: flex; flex-direction: column; gap: .55rem; }
.ap-legend-item { display: flex; align-items: center; justify-content: space-between; font-size: .78rem; }
.ap-legend-key { display: inline-flex; align-items: center; gap: .5rem; color: var(--ap-ink-2); }
.ap-legend-dot { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }
.ap-legend-val { font-weight: 700; color: var(--ap-ink); }
.ap-legend-pct { color: var(--ap-mute); font-weight: 600; font-size: .72rem; margin-left: .35rem; }

/* Podium fournisseurs */
.ap-podium { padding: .8rem 1.1rem 1rem; display: flex; flex-direction: column; gap: .55rem; }
.ap-podium-row { display: flex; align-items: center; gap: .65rem; }
.ap-podium-rank {
    width: 22px; height: 22px; border-radius: 50%; background: var(--ap-line-2); color: var(--ap-mute);
    display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 700; flex-shrink: 0;
}
.ap-podium-row:nth-child(1) .ap-podium-rank { background: #FEF3C7; color: #B45309; }
.ap-podium-row:nth-child(2) .ap-podium-rank { background: #F1F5F9; color: #64748B; }
.ap-podium-row:nth-child(3) .ap-podium-rank { background: #FFEDD5; color: #EA580C; }
.ap-podium-info { flex: 1; min-width: 0; }
.ap-podium-name { font-size: .78rem; font-weight: 700; color: var(--ap-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ap-podium-bar { height: 4px; background: var(--ap-line-2); border-radius: 2px; margin-top: .3rem; overflow: hidden; }
.ap-podium-bar-fill { height: 100%; background: var(--ap-brand); border-radius: 2px; }
.ap-podium-vals { text-align: right; flex-shrink: 0; }
.ap-podium-vals strong { display: block; font-size: .78rem; color: var(--ap-ink); font-weight: 700; }
.ap-podium-vals span { font-size: .65rem; color: var(--ap-mute); }

/* Progress bar contrats */
.ap-contrat-row { padding: .7rem 1.1rem; border-bottom: 1px solid var(--ap-line-2); }
.ap-contrat-row:last-child { border-bottom: 0; }
.ap-contrat-head { display: flex; justify-content: space-between; align-items: baseline; gap: .5rem; }
.ap-contrat-obj { font-size: .8rem; font-weight: 700; color: var(--ap-ink); text-decoration: none; }
.ap-contrat-obj:hover { color: var(--ap-primary); }
.ap-contrat-jrs { font-size: .72rem; font-weight: 700; }
.ap-contrat-meta { font-size: .68rem; color: var(--ap-mute); margin-top: .1rem; }
.ap-contrat-prog { height: 4px; background: var(--ap-line-2); border-radius: 2px; margin-top: .45rem; overflow: hidden; }
.ap-contrat-prog-fill { height: 100%; border-radius: 2px; transition: width .3s; }

/* ──────── RUPTURE MODAL ──────── */
.ap-rupture-modal .modal-content { border: 1px solid var(--ap-line); border-radius: 8px; overflow: hidden; }
.ap-rupture-modal .modal-header { background: #FEF2F2; border-bottom: 1px solid #FCA5A5; padding: 1rem 1.5rem; }
.ap-rupture-modal .modal-title { color: var(--ap-danger); font-weight: 800; }

@media (max-width: 575px) {
    .ap-head { padding: .85rem 1rem; }
    .ap-head h1 { font-size: 1rem; }
    .ap-head-actions .ap-btn { padding: .45rem .65rem; font-size: .72rem; }
    .ap-donut-wrap { flex-direction: column; align-items: stretch; }
    .ap-donut { align-self: center; }
}
</style>
@endpush

@section('content')
<div class="ap-shell">

{{-- Note: la modale d'alerte rupture est désormais globale à l'espace
     Achats & MG (voir layouts/app.blade.php + appro/_partials/stock-alert-monitor).
     Elle s'affiche automatiquement toutes les 5 minutes pour les gestionnaires. --}}

{{-- ═════════════════ HEADER ═════════════════ --}}
<div class="ap-head">
    <div class="ap-head-title">
        <span class="ap-head-emblem"><i class="fas fa-cart-flatbed"></i></span>
        <div>
            <h1>Achats &amp; Moyens Généraux</h1>
            <p>Pilotage — {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
        </div>
    </div>
    <div class="ap-head-actions">
        <a href="{{ route('appro.commandes.create') }}" class="ap-btn ap-btn-primary">
            <i class="fas fa-plus"></i>Nouvelle commande
        </a>
        <a href="{{ route('appro.commandes-internes.create') }}" class="ap-btn ap-btn-outline">
            <i class="fas fa-clipboard-list"></i>Demande interne
        </a>
        <a href="{{ route('mg.dysfonctionnements.create') }}" class="ap-btn ap-btn-outline">
            <i class="fas fa-triangle-exclamation"></i>Signaler
        </a>
    </div>
</div>

{{-- ═════════════════ PRIORITY BAR ═════════════════ --}}
@php
    $alerts = collect();
    if ($stockRupture > 0) $alerts->push(['type'=>'danger', 'ico'=>'fa-boxes-stacked', 'title'=>$stockRupture.' rupture'.($stockRupture>1?'s':'').' de stock', 'sub'=>'Réapprovisionnement urgent', 'url'=>route('referentiel.catalogue.index').'?stock=rupture']);
    if ($demandesRetard > 0) $alerts->push(['type'=>'warn', 'ico'=>'fa-hourglass-half', 'title'=>$demandesRetard.' demande'.($demandesRetard>1?'s':'').' en retard', 'sub'=>'En attente depuis > 3 jours', 'url'=>route('appro.commandes-internes.index')]);
    if ($facturesEchues > 0) $alerts->push(['type'=>'danger', 'ico'=>'fa-file-invoice-dollar', 'title'=>$facturesEchues.' facture'.($facturesEchues>1?'s':'').' échue'.($facturesEchues>1?'s':''), 'sub'=>'Échéance dépassée', 'url'=>route('finance.factures.index')]);
    if ($contratsProcheExpiration > 0) $alerts->push(['type'=>'warn', 'ico'=>'fa-file-signature', 'title'=>$contratsProcheExpiration.' engagement'.($contratsProcheExpiration>1?'s':'').' à renouveler', 'sub'=>'Expiration < 60 jours', 'url'=>route('appro.contrats.index').'?expiration=proche']);
    if ($ticketsSignales > 0) $alerts->push(['type'=>'info', 'ico'=>'fa-triangle-exclamation', 'title'=>$ticketsSignales.' ticket'.($ticketsSignales>1?'s':'').' à prendre en charge', 'sub'=>'Dysfonctionnements ouverts', 'url'=>route('mg.dysfonctionnements.index')]);
@endphp
@if($alerts->count())
<div class="ap-alerts">
    @foreach($alerts as $a)
    <a href="{{ $a['url'] }}" class="ap-alert ap-alert-{{ $a['type'] }}">
        <span class="ap-alert-ico"><i class="fas {{ $a['ico'] }}"></i></span>
        <div class="ap-alert-body">
            <div class="ap-alert-title">{{ $a['title'] }}</div>
            <div class="ap-alert-sub">{{ $a['sub'] }}</div>
        </div>
        <i class="fas fa-chevron-right ap-alert-arrow"></i>
    </a>
    @endforeach
</div>
@endif

{{-- ═════════════════ KPIS PRINCIPAUX ═════════════════ --}}
<div class="ap-kpi-grid">
    {{-- KPI 1 — Montant engagé ce mois --}}
    <a href="{{ route('appro.commandes.index') }}" class="ap-kpi">
        <div class="ap-kpi-head">
            <span class="ap-kpi-lbl">Engagé ce mois</span>
            <span class="ap-kpi-ico"><i class="fas fa-coins"></i></span>
        </div>
        <div class="ap-kpi-val">{{ number_format($montantMois / 1000, 0, ',', ' ') }}<small>K XAF</small></div>
        <div class="ap-kpi-foot">
            @if($tendanceMontant > 0)
                <span class="ap-trend ap-trend-up"><i class="fas fa-arrow-up"></i>{{ $tendanceMontant }}%</span>
            @elseif($tendanceMontant < 0)
                <span class="ap-trend ap-trend-down"><i class="fas fa-arrow-down"></i>{{ abs($tendanceMontant) }}%</span>
            @else
                <span class="ap-trend ap-trend-flat"><i class="fas fa-minus"></i>0%</span>
            @endif
            <div class="ap-spark">
                @foreach($depensesMois as $d)
                    <div class="ap-spark-bar" style="height:{{ max(15, ($d['montant'] / $depensesMax) * 100) }}%;"></div>
                @endforeach
            </div>
        </div>
    </a>

    {{-- KPI 2 — Nb commandes --}}
    <a href="{{ route('appro.commandes.index') }}" class="ap-kpi">
        <div class="ap-kpi-head">
            <span class="ap-kpi-lbl">Commandes ce mois</span>
            <span class="ap-kpi-ico" style="background:#E0E7FF; color:var(--ap-info);"><i class="fas fa-cart-shopping"></i></span>
        </div>
        <div class="ap-kpi-val">{{ $nbCommandesMois }}</div>
        <div class="ap-kpi-foot">
            @if($tendanceNbCmd > 0)
                <span class="ap-trend ap-trend-up"><i class="fas fa-arrow-up"></i>{{ $tendanceNbCmd }}%</span>
            @elseif($tendanceNbCmd < 0)
                <span class="ap-trend ap-trend-down"><i class="fas fa-arrow-down"></i>{{ abs($tendanceNbCmd) }}%</span>
            @else
                <span class="ap-trend ap-trend-flat"><i class="fas fa-minus"></i>0%</span>
            @endif
            <span class="ap-kpi-sub">{{ $commandesEnCours }} en cours</span>
        </div>
    </a>

    {{-- KPI 3 — Délai moyen --}}
    <a href="{{ route('appro.commandes.index') }}" class="ap-kpi">
        <div class="ap-kpi-head">
            <span class="ap-kpi-lbl">Délai livraison</span>
            <span class="ap-kpi-ico" style="background:#D1FAE5; color:var(--ap-ok);"><i class="fas fa-truck-fast"></i></span>
        </div>
        <div class="ap-kpi-val">{{ number_format($delaiMoyenJours, 1, ',', ' ') }}<small>j moy.</small></div>
        <div class="ap-kpi-foot">
            <span class="ap-kpi-sub">Basé sur 90 derniers jours</span>
        </div>
    </a>

    {{-- KPI 4 — Valeur du stock --}}
    <a href="{{ route('referentiel.catalogue.index') }}" class="ap-kpi">
        <div class="ap-kpi-head">
            <span class="ap-kpi-lbl">Valorisation stock</span>
            <span class="ap-kpi-ico" style="background:#FEF3C7; color:#B45309;"><i class="fas fa-warehouse"></i></span>
        </div>
        <div class="ap-kpi-val">{{ number_format($valeurStock / 1000, 0, ',', ' ') }}<small>K XAF</small></div>
        <div class="ap-kpi-foot">
            <span class="ap-kpi-sub">{{ $produitsStock }} article(s) stockable(s)</span>
        </div>
    </a>
</div>

{{-- ═════════════════ CHARTS : Dépenses + Fournisseurs ═════════════════ --}}
<div class="ap-row ap-row-2">
    {{-- Chart barres — dépenses 6 mois --}}
    <div class="ap-card">
        <div class="ap-card-hd">
            <h6>
                <span class="ap-card-hd-ico" style="background:var(--ap-brand-soft); color:var(--ap-brand);"><i class="fas fa-chart-column"></i></span>
                Dépenses mensuelles (6 mois)
            </h6>
            <a href="{{ route('appro.commandes.index') }}" class="ap-link">Détail →</a>
        </div>
        <div class="ap-chart-bars">
            <div class="ap-bars">
                @foreach($depensesMois as $i => $d)
                <div class="ap-bar-col {{ $i === count($depensesMois)-1 ? 'is-current' : '' }}"
                     title="{{ $d['label'] }} : {{ number_format($d['montant']/1000, 0, ',', ' ') }} K XAF">
                    <div class="ap-bar-val">{{ $d['montant'] > 0 ? round($d['montant']/1000) : '' }}</div>
                    <div class="ap-bar-outer">
                        <div class="ap-bar-fill" style="height:{{ max(2, ($d['montant'] / $depensesMax) * 100) }}%;"></div>
                    </div>
                    <div class="ap-bar-label">{{ $d['label'] }}</div>
                </div>
                @endforeach
            </div>
            <div class="ap-chart-total">
                <span class="ap-mute">Total 6 mois</span>
                <strong>{{ number_format(array_sum(array_column($depensesMois, 'montant')), 0, ',', ' ') }} XAF</strong>
            </div>
        </div>
    </div>

    {{-- Podium fournisseurs --}}
    <div class="ap-card">
        <div class="ap-card-hd">
            <h6>
                <span class="ap-card-hd-ico" style="background:#E0E7FF; color:var(--ap-info);"><i class="fas fa-award"></i></span>
                Top fournisseurs (12 mois)
            </h6>
            <a href="{{ route('appro.fournisseurs.index') }}" class="ap-link">Voir tout →</a>
        </div>
        <div class="ap-podium">
            @forelse($topFournisseurs as $i => $f)
            <div class="ap-podium-row">
                <div class="ap-podium-rank">{{ $i+1 }}</div>
                <div class="ap-podium-info">
                    <div class="ap-podium-name">{{ $f->fournisseur->raison_sociale ?? $f->fournisseur->nom ?? '—' }}</div>
                    <div class="ap-podium-bar"><div class="ap-podium-bar-fill" style="width:{{ min(100, ($f->total / $topFournisseurMax) * 100) }}%;"></div></div>
                </div>
                <div class="ap-podium-vals">
                    <strong>{{ number_format($f->total / 1000, 0, ',', ' ') }} K</strong>
                    <span>{{ $f->nb }} cmd</span>
                </div>
            </div>
            @empty
            <div class="ap-empty"><i class="fas fa-building"></i>Aucune commande sur 12 mois</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═════════════════ Stock health + Contrats ═════════════════ --}}
<div class="ap-row ap-row-2">
    {{-- Donut stock --}}
    <div class="ap-card">
        <div class="ap-card-hd">
            <h6>
                <span class="ap-card-hd-ico" style="background:#D1FAE5; color:var(--ap-ok);"><i class="fas fa-heart-pulse"></i></span>
                Santé du stock
            </h6>
            <a href="{{ route('referentiel.catalogue.index') }}" class="ap-link">Catalogue →</a>
        </div>
        @php
            // Segments donut : circumference = 2*PI*r = 2*PI*40 = 251.327
            $c = 251.327;
            $pctOk = $stockOk / $stockTotal;
            $pctAl = $stockAlerte / $stockTotal;
            $pctRu = $stockRupture / $stockTotal;
            $lOk = $pctOk * $c; $lAl = $pctAl * $c; $lRu = $pctRu * $c;
            $off1 = 0;
            $off2 = $lOk;
            $off3 = $lOk + $lAl;
        @endphp
        <div class="ap-donut-wrap">
            <div class="ap-donut">
                <svg width="140" height="140" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#F1F5F9" stroke-width="14"/>
                    @if($stockOk > 0)
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#059669" stroke-width="14"
                            stroke-dasharray="{{ $lOk }} {{ $c - $lOk }}" stroke-dashoffset="{{ -$off1 }}"/>
                    @endif
                    @if($stockAlerte > 0)
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#D97706" stroke-width="14"
                            stroke-dasharray="{{ $lAl }} {{ $c - $lAl }}" stroke-dashoffset="{{ -$off2 }}"/>
                    @endif
                    @if($stockRupture > 0)
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#DC2626" stroke-width="14"
                            stroke-dasharray="{{ $lRu }} {{ $c - $lRu }}" stroke-dashoffset="{{ -$off3 }}"/>
                    @endif
                </svg>
                <div class="ap-donut-center">
                    <strong>{{ $produitsStock }}</strong>
                    <span>Articles</span>
                </div>
            </div>
            <div class="ap-donut-legend">
                <div class="ap-legend-item">
                    <span class="ap-legend-key"><span class="ap-legend-dot" style="background:#059669;"></span>Stock OK</span>
                    <span><span class="ap-legend-val">{{ $stockOk }}</span><span class="ap-legend-pct">{{ round($pctOk * 100) }}%</span></span>
                </div>
                <div class="ap-legend-item">
                    <span class="ap-legend-key"><span class="ap-legend-dot" style="background:#D97706;"></span>Seuil alerte</span>
                    <span><span class="ap-legend-val">{{ $stockAlerte }}</span><span class="ap-legend-pct">{{ round($pctAl * 100) }}%</span></span>
                </div>
                <div class="ap-legend-item">
                    <span class="ap-legend-key"><span class="ap-legend-dot" style="background:#DC2626;"></span>Rupture</span>
                    <span><span class="ap-legend-val">{{ $stockRupture }}</span><span class="ap-legend-pct">{{ round($pctRu * 100) }}%</span></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Contrats expirants --}}
    <div class="ap-card">
        <div class="ap-card-hd">
            <h6>
                <span class="ap-card-hd-ico" style="background:#FEF3C7; color:#B45309;"><i class="fas fa-file-signature"></i></span>
                Engagements à surveiller
                <span class="ap-badge ap-badge-mute ms-1">{{ $contratsActifs }} actifs</span>
            </h6>
            <a href="{{ route('appro.contrats.index') }}" class="ap-link">Tout voir →</a>
        </div>
        <div class="ap-card-tight">
            @forelse($contratsExpirentBientot as $ct)
            @php
                $jours = (int) now()->startOfDay()->diffInDays($ct->date_fin->startOfDay(), false);
                $pct   = max(0, min(100, 100 - ($jours / 60 * 100)));
                $color = $jours <= 15 ? '#DC2626' : ($jours <= 30 ? '#D97706' : '#4F46E5');
            @endphp
            <div class="ap-contrat-row">
                <div class="ap-contrat-head">
                    <a href="{{ route('appro.contrats.show', $ct) }}" class="ap-contrat-obj">
                        {{ \Illuminate\Support\Str::limit($ct->objet, 42) }}
                    </a>
                    <span class="ap-contrat-jrs" style="color:{{ $color }};">
                        <i class="fas fa-clock"></i> {{ $jours }}j
                    </span>
                </div>
                <div class="ap-contrat-meta">
                    <i class="fas fa-building"></i>
                    {{ \Illuminate\Support\Str::limit($ct->fournisseur?->raison_sociale ?? $ct->fournisseur?->nom ?? '—', 35) }}
                    · Expire le {{ $ct->date_fin?->format('d/m/Y') }}
                </div>
                <div class="ap-contrat-prog"><div class="ap-contrat-prog-fill" style="width:{{ $pct }}%; background:{{ $color }};"></div></div>
            </div>
            @empty
            <div class="ap-empty"><i class="fas fa-check-circle"></i>Aucun engagement à surveiller</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═════════════════ Activité opérationnelle ═════════════════ --}}
<div class="ap-row ap-row-2">
    {{-- Demandes internes --}}
    <div class="ap-card">
        <div class="ap-card-hd">
            <h6>
                <span class="ap-card-hd-ico" style="background:#FEE2E2; color:var(--ap-danger);"><i class="fas fa-clipboard-list"></i></span>
                Demandes internes à traiter
            </h6>
            <a href="{{ route('appro.commandes-internes.index') }}" class="ap-link">Tout voir →</a>
        </div>
        <div class="ap-card-tight">
            @forelse($dernieresDemandes as $dem)
            <div class="ap-list-row">
                <div class="ap-list-row-main">
                    <a href="{{ route('appro.commandes-internes.show', $dem) }}">{{ $dem->numero ?? '#'.$dem->id }}</a>
                    <div class="ap-list-row-meta">
                        @if($dem->demandeur)<i class="fas fa-user"></i> {{ $dem->demandeur->prenoms ?? '' }} {{ $dem->demandeur->name }}@endif
                    </div>
                </div>
                <div class="ap-list-row-right">
                    <span class="ap-badge ap-badge-danger">{{ $dem->statut_libelle }}</span>
                    <div class="ap-list-row-meta">{{ $dem->created_at?->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div class="ap-empty"><i class="fas fa-check-circle"></i>Aucune demande en attente</div>
            @endforelse
        </div>
    </div>

    {{-- Commandes récentes --}}
    <div class="ap-card">
        <div class="ap-card-hd">
            <h6>
                <span class="ap-card-hd-ico" style="background:var(--ap-brand-soft); color:var(--ap-brand);"><i class="fas fa-cart-shopping"></i></span>
                Commandes fournisseur récentes
            </h6>
            <a href="{{ route('appro.commandes.index') }}" class="ap-link">Tout voir →</a>
        </div>
        <div class="ap-card-tight">
            @forelse($derniersCommandes as $cmd)
            <div class="ap-list-row">
                <div class="ap-list-row-main">
                    <a href="{{ route('appro.commandes.show', $cmd) }}">{{ $cmd->numero_commande ?? '#'.$cmd->id }}</a>
                    <div class="ap-list-row-meta">
                        @if($cmd->fournisseur)<i class="fas fa-building"></i> {{ \Illuminate\Support\Str::limit($cmd->fournisseur->raison_sociale ?? $cmd->fournisseur->nom ?? '—', 32) }}@endif
                    </div>
                </div>
                <div class="ap-list-row-right">
                    <strong>{{ number_format((float) ($cmd->montant_ttc ?? 0), 0, ',', ' ') }} XAF</strong>
                    <div class="ap-list-row-meta">{{ $cmd->date_commande?->diffForHumans() ?? $cmd->created_at?->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div class="ap-empty"><i class="fas fa-inbox"></i>Aucune commande enregistrée</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═════════════════ MOYENS GÉNÉRAUX ═════════════════ --}}
<div class="ap-row ap-row-2">
    {{-- Dysfonctionnements --}}
    <div class="ap-card">
        <div class="ap-card-hd">
            <h6>
                <span class="ap-card-hd-ico" style="background:#FEE2E2; color:var(--ap-danger);"><i class="fas fa-triangle-exclamation"></i></span>
                Dysfonctionnements ouverts
                <span class="ap-badge ap-badge-danger ms-1">{{ $ticketsSignales + $ticketsEnTraitement }}</span>
            </h6>
            <a href="{{ route('mg.dysfonctionnements.index') }}" class="ap-link">Tout voir →</a>
        </div>
        <div class="ap-card-tight">
            @forelse($derniersTickets as $tick)
            <div class="ap-list-row">
                <div class="ap-list-row-main">
                    <a href="{{ route('mg.dysfonctionnements.show', $tick) }}">{{ \Illuminate\Support\Str::limit($tick->label, 55) }}</a>
                    <div class="ap-list-row-meta">
                        @if($tick->declarant)<i class="fas fa-user"></i> {{ $tick->declarant->prenoms ?? '' }} {{ $tick->declarant->name }}@endif
                    </div>
                </div>
                <div class="ap-list-row-right">
                    <span class="ap-badge {{ $tick->statut === \App\Models\Dysfonctionnement::STATUT_SIGNALE ? 'ap-badge-danger' : 'ap-badge-info' }}">
                        {{ $tick->statut_libelle ?? '—' }}
                    </span>
                    <div class="ap-list-row-meta">{{ $tick->created_at?->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div class="ap-empty"><i class="fas fa-check-circle"></i>Aucun ticket ouvert</div>
            @endforelse
        </div>
    </div>

    {{-- Interventions --}}
    <div class="ap-card">
        <div class="ap-card-hd">
            <h6>
                <span class="ap-card-hd-ico" style="background:#E0E7FF; color:var(--ap-info);"><i class="fas fa-screwdriver-wrench"></i></span>
                Interventions
            </h6>
            <a href="{{ route('mg.interventions.index') }}" class="ap-link">Tout voir →</a>
        </div>
        <div class="ap-card-bd">
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div style="padding:.5rem; border:1px solid var(--ap-line-2); border-radius:6px;">
                        <div style="font-size:1.4rem; font-weight:800; color:var(--ap-info);">{{ $interventionsPlanifiees }}</div>
                        <div style="font-size:.7rem; color:var(--ap-mute); text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Planifiées</div>
                    </div>
                </div>
                <div class="col-4">
                    <div style="padding:.5rem; border:1px solid var(--ap-line-2); border-radius:6px;">
                        <div style="font-size:1.4rem; font-weight:800; color:var(--ap-warn);">{{ $interventionsEnCours }}</div>
                        <div style="font-size:.7rem; color:var(--ap-mute); text-transform:uppercase; letter-spacing:.05em; font-weight:700;">En cours</div>
                    </div>
                </div>
                <div class="col-4">
                    <div style="padding:.5rem; border:1px solid var(--ap-line-2); border-radius:6px;">
                        <div style="font-size:1.4rem; font-weight:800; color:var(--ap-ok);">{{ $interventionsTerminees }}</div>
                        <div style="font-size:.7rem; color:var(--ap-mute); text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Terminées</div>
                    </div>
                </div>
            </div>
            <div style="margin-top:.85rem; padding-top:.85rem; border-top:1px dashed var(--ap-line); font-size:.78rem; color:var(--ap-mute-2); display:flex; justify-content:space-between;">
                <span><i class="fas fa-building"></i> {{ $immobilisationsTotal }} immobilisations suivies</span>
                <span><i class="fas fa-users"></i> {{ $totalFournisseurs }} fournisseurs</span>
            </div>
        </div>
    </div>
</div>

</div>{{-- /.ap-shell --}}
@endsection
