@extends('layouts.app')

@section('title', 'Tableau de bord Finance & Budget')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   FINANCE DASHBOARD — même standard que /appro/dashboard
   Charte : indigo (#4F46E5) module Finance
═══════════════════════════════════════════════════════════════ */
:root {
    --fi-brand: #4F46E5;
    --fi-brand-deep: #4338CA;
    --fi-brand-soft: #E0E7FF;
    --fi-brand-tint: #EEF2FF;
    --fi-ink: #0F172A;
    --fi-ink-2: #334155;
    --fi-mute: #64748B;
    --fi-mute-2: #94A3B8;
    --fi-line: #E5E7EB;
    --fi-line-2: #F1F5F9;
    --fi-bg: #F8FAFC;
    --fi-danger: #DC2626;
    --fi-warn: #D97706;
    --fi-ok: #059669;
    --fi-primary: #0A66C2;
}
.fi-shell { max-width: 1400px; margin: 0 auto; }

/* ── HERO / HEADER ── */
.fi-head {
    background: #fff; border: 1px solid var(--fi-line); border-radius: 8px;
    padding: 1rem 1.25rem; margin-bottom: 1rem;
    display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.fi-head-title { display: flex; align-items: center; gap: .85rem; }
.fi-head-emblem {
    width: 44px; height: 44px; border-radius: 8px; background: var(--fi-brand-soft);
    color: var(--fi-brand); display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem;
    flex-shrink: 0;
}
.fi-head h1 { font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--fi-ink); }
.fi-head p  { margin: 0; font-size: .8rem; color: var(--fi-mute); }
.fi-head-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.fi-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem .85rem; font-size: .78rem; font-weight: 600;
    border-radius: 6px; text-decoration: none; border: 1px solid transparent;
    transition: background .15s, border-color .15s, color .15s;
}
.fi-btn-primary { background: var(--fi-brand); color: #fff; }
.fi-btn-primary:hover { background: var(--fi-brand-deep); color: #fff; }
.fi-btn-outline { background: #fff; color: var(--fi-ink-2); border-color: var(--fi-line); }
.fi-btn-outline:hover { border-color: var(--fi-brand); color: var(--fi-brand); }

/* ── PRIORITY BAR ── */
.fi-alerts { display: grid; gap: .65rem; margin-bottom: 1.25rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
.fi-alert {
    display: flex; align-items: center; gap: .75rem; padding: .75rem .9rem;
    background: #fff; border: 1px solid var(--fi-line); border-left: 3px solid var(--fi-danger);
    border-radius: 6px; text-decoration: none; color: inherit; transition: border-color .15s, background .15s;
}
.fi-alert:hover { background: #FEF2F2; border-left-color: var(--fi-danger); color: inherit; }
.fi-alert.fi-alert-warn { border-left-color: var(--fi-warn); }
.fi-alert.fi-alert-warn:hover { background: #FFFBEB; }
.fi-alert.fi-alert-info { border-left-color: var(--fi-brand); }
.fi-alert.fi-alert-info:hover { background: var(--fi-brand-tint); }
.fi-alert-ico {
    width: 34px; height: 34px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;
    background: #FEE2E2; color: var(--fi-danger); flex-shrink: 0;
}
.fi-alert-warn .fi-alert-ico { background: #FEF3C7; color: var(--fi-warn); }
.fi-alert-info .fi-alert-ico { background: var(--fi-brand-soft); color: var(--fi-brand); }
.fi-alert-body { flex: 1; min-width: 0; }
.fi-alert-title { font-size: .82rem; font-weight: 700; color: var(--fi-ink); line-height: 1.15; }
.fi-alert-sub { font-size: .7rem; color: var(--fi-mute); margin-top: .1rem; }
.fi-alert-arrow { color: var(--fi-mute-2); }

/* ── KPI CARDS ── */
.fi-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .85rem; margin-bottom: 1.25rem; }
@media (max-width: 992px) { .fi-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .fi-kpi-grid { grid-template-columns: 1fr; } }
.fi-kpi {
    background: #fff; border: 1px solid var(--fi-line); border-radius: 8px;
    padding: .95rem 1rem; display: flex; flex-direction: column; gap: .5rem;
    text-decoration: none; color: inherit; transition: border-color .15s, transform .15s;
    position: relative; overflow: hidden;
}
.fi-kpi:hover { border-color: var(--fi-brand); color: inherit; transform: translateY(-1px); }
.fi-kpi-head { display: flex; align-items: center; justify-content: space-between; }
.fi-kpi-lbl { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; color: var(--fi-mute); font-weight: 700; }
.fi-kpi-ico {
    width: 30px; height: 30px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;
    background: var(--fi-brand-soft); color: var(--fi-brand); font-size: .8rem;
}
.fi-kpi-val { font-size: 1.55rem; font-weight: 800; color: var(--fi-ink); line-height: 1; }
.fi-kpi-val small { font-size: .72rem; color: var(--fi-mute); font-weight: 500; margin-left: .25rem; }
.fi-kpi-foot { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
.fi-trend { display: inline-flex; align-items: center; gap: .2rem; font-size: .72rem; font-weight: 700; padding: .1rem .45rem; border-radius: 4px; }
.fi-trend-up   { color: var(--fi-danger); background: #FEE2E2; }   /* Hausse dépense = mauvais signe */
.fi-trend-down { color: var(--fi-ok); background: #ECFDF5; }        /* Baisse dépense = bon signe */
.fi-trend-flat { color: var(--fi-mute); background: var(--fi-line-2); }
.fi-kpi-sub { font-size: .7rem; color: var(--fi-mute); }

/* Progress bar (taux exécution) */
.fi-progress { height: 6px; background: var(--fi-line-2); border-radius: 3px; overflow: hidden; }
.fi-progress-fill { height: 100%; background: var(--fi-brand); border-radius: 3px; transition: width .3s; }
.fi-progress-fill.is-warn { background: var(--fi-warn); }
.fi-progress-fill.is-danger { background: var(--fi-danger); }

/* Mini bars sparkline */
.fi-spark { display: flex; align-items: flex-end; gap: 2px; height: 22px; }
.fi-spark-bar { width: 5px; background: var(--fi-brand); border-radius: 1px; opacity: .8; }

/* ── SECTION CARDS ── */
.fi-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.fi-row-2 { grid-template-columns: 1fr 1fr; }
@media (max-width: 991px) { .fi-row-2 { grid-template-columns: 1fr; } }

.fi-card { background: #fff; border: 1px solid var(--fi-line); border-radius: 8px; overflow: hidden; }
.fi-card-hd {
    display: flex; align-items: center; justify-content: space-between;
    padding: .85rem 1.1rem; border-bottom: 1px solid var(--fi-line-2);
}
.fi-card-hd h6 { margin: 0; font-size: .85rem; font-weight: 700; color: var(--fi-ink); display: flex; align-items: center; gap: .5rem; }
.fi-card-hd-ico {
    width: 26px; height: 26px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center;
    font-size: .75rem;
}
.fi-link { font-size: .72rem; font-weight: 600; color: var(--fi-primary); text-decoration: none; }
.fi-link:hover { text-decoration: underline; }

.fi-card-bd { padding: 1rem 1.1rem; }
.fi-card-tight { padding: 0; }

.fi-list-row {
    display: flex; align-items: center; justify-content: space-between; gap: .75rem;
    padding: .6rem 1.1rem; border-bottom: 1px solid var(--fi-line-2);
    font-size: .8rem; transition: background .12s;
}
.fi-list-row:last-child { border-bottom: 0; }
.fi-list-row:hover { background: #FAFAFA; }
.fi-list-row-main { min-width: 0; flex: 1; }
.fi-list-row-main a { color: var(--fi-ink); font-weight: 600; text-decoration: none; }
.fi-list-row-main a:hover { color: var(--fi-primary); }
.fi-list-row-meta { font-size: .7rem; color: var(--fi-mute); margin-top: .1rem; }
.fi-list-row-right { text-align: right; flex-shrink: 0; }
.fi-list-row-right strong { color: var(--fi-ink); font-size: .82rem; }
.fi-badge {
    font-size: .68rem; padding: .12rem .5rem; border-radius: 4px; font-weight: 700;
    display: inline-block;
}
.fi-badge-danger  { background: #FEE2E2; color: var(--fi-danger); }
.fi-badge-warn    { background: #FEF3C7; color: var(--fi-warn); }
.fi-badge-info    { background: var(--fi-brand-soft); color: var(--fi-brand); }
.fi-badge-ok      { background: #D1FAE5; color: var(--fi-ok); }
.fi-badge-mute    { background: var(--fi-line-2); color: var(--fi-mute); }
.fi-empty { padding: 1.75rem 1rem; text-align: center; color: var(--fi-mute-2); font-size: .8rem; }
.fi-empty i { display: block; font-size: 1.35rem; margin-bottom: .35rem; opacity: .55; }

/* Chart bars */
.fi-chart-bars { padding: 1rem 1.1rem 1.25rem; }
.fi-bars { display: flex; align-items: flex-end; gap: 6px; height: 130px; margin-bottom: .5rem; }
.fi-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: .35rem; height: 100%; }
.fi-bar-outer { width: 100%; display: flex; align-items: flex-end; flex: 1; }
.fi-bar-fill { width: 100%; background: var(--fi-brand); border-radius: 3px 3px 0 0; transition: opacity .2s; min-height: 2px; }
.fi-bar-col.is-current .fi-bar-fill { background: var(--fi-brand-deep); }
.fi-bar-col:hover .fi-bar-fill { opacity: .85; }
.fi-bar-label { font-size: .68rem; color: var(--fi-mute); text-transform: capitalize; font-weight: 600; }
.fi-bar-val { font-size: .68rem; color: var(--fi-ink); font-weight: 700; }
.fi-chart-total { padding-top: .5rem; border-top: 1px dashed var(--fi-line); display: flex; justify-content: space-between; font-size: .78rem; }
.fi-chart-total .fi-mute { color: var(--fi-mute); }

/* Ligne budgétaire — barre horizontale */
.fi-ligne-row { padding: .7rem 1.1rem; border-bottom: 1px solid var(--fi-line-2); }
.fi-ligne-row:last-child { border-bottom: 0; }
.fi-ligne-head { display: flex; justify-content: space-between; align-items: baseline; gap: .5rem; }
.fi-ligne-name { font-size: .8rem; font-weight: 600; color: var(--fi-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fi-ligne-taux { font-size: .72rem; font-weight: 700; }
.fi-ligne-meta { font-size: .68rem; color: var(--fi-mute); margin-top: .15rem; }
.fi-ligne-prog { height: 4px; background: var(--fi-line-2); border-radius: 2px; margin-top: .45rem; overflow: hidden; }
.fi-ligne-prog-fill { height: 100%; border-radius: 2px; }

/* Podium clients (like appro top fournisseurs) */
.fi-podium { padding: .8rem 1.1rem 1rem; display: flex; flex-direction: column; gap: .55rem; }
.fi-podium-row { display: flex; align-items: center; gap: .65rem; }
.fi-podium-rank {
    width: 22px; height: 22px; border-radius: 50%; background: var(--fi-line-2); color: var(--fi-mute);
    display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 700; flex-shrink: 0;
}
.fi-podium-row:nth-child(1) .fi-podium-rank { background: #FEF3C7; color: #B45309; }
.fi-podium-row:nth-child(2) .fi-podium-rank { background: #F1F5F9; color: #64748B; }
.fi-podium-row:nth-child(3) .fi-podium-rank { background: #FFEDD5; color: #EA580C; }
.fi-podium-info { flex: 1; min-width: 0; }
.fi-podium-name { font-size: .78rem; font-weight: 700; color: var(--fi-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fi-podium-bar { height: 4px; background: var(--fi-line-2); border-radius: 2px; margin-top: .3rem; overflow: hidden; }
.fi-podium-bar-fill { height: 100%; background: var(--fi-brand); border-radius: 2px; }
.fi-podium-vals { text-align: right; flex-shrink: 0; }
.fi-podium-vals strong { display: block; font-size: .78rem; color: var(--fi-ink); font-weight: 700; }
.fi-podium-vals span { font-size: .65rem; color: var(--fi-mute); }

/* Sens badge */
.fi-sens { display: inline-flex; align-items: center; gap: .2rem; font-size: .68rem; font-weight: 700; padding: .1rem .4rem; border-radius: 3px; }
.fi-sens.recette { color: var(--fi-ok); background: #ECFDF5; }
.fi-sens.depense { color: var(--fi-danger); background: #FEE2E2; }

@media (max-width: 575px) {
    .fi-head { padding: .85rem 1rem; }
    .fi-head h1 { font-size: 1rem; }
    .fi-head-actions .fi-btn { padding: .45rem .65rem; font-size: .72rem; }
}
</style>
@endpush

@section('content')
<div class="fi-shell">

{{-- ═════════════════ HEADER ═════════════════ --}}
<div class="fi-head">
    <div class="fi-head-title">
        <span class="fi-head-emblem"><i class="fas fa-coins"></i></span>
        <div>
            <h1>Finance &amp; Budget</h1>
            <p>
                @if($exerciceEnCours)
                    Exercice <strong>{{ $exerciceEnCours->libelle ?? $exerciceEnCours->exercice }}</strong>
                    @if($exerciceEnCours->datedebut && $exerciceEnCours->datefin)
                        · {{ \Carbon\Carbon::parse($exerciceEnCours->datedebut)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($exerciceEnCours->datefin)->format('d/m/Y') }}
                    @endif
                @else
                    <span style="color:var(--fi-warn);"><i class="fas fa-triangle-exclamation"></i> Aucun exercice en cours</span>
                @endif
            </p>
        </div>
    </div>
    <div class="fi-head-actions">
        @can('create:operation')
        <a href="{{ route('finance.ordres.create') }}" class="fi-btn fi-btn-primary">
            <i class="fas fa-plus"></i>Nouvel ordre
        </a>
        @endcan
        @can('create:facture')
        <a href="{{ route('finance.factures.create') }}" class="fi-btn fi-btn-outline">
            <i class="fas fa-file-invoice"></i>Nouvelle facture
        </a>
        @endcan
        @can('create:exercice')
        <a href="{{ route('finance.exercices.create') }}" class="fi-btn fi-btn-outline">
            <i class="fas fa-calendar-plus"></i>Nouvel exercice
        </a>
        @endcan
    </div>
</div>

{{-- ═════════════════ PRIORITY BAR ═════════════════ --}}
@php
    $alerts = collect();
    if ($lignesDepassees->count() > 0) {
        $alerts->push(['type'=>'danger', 'ico'=>'fa-triangle-exclamation',
            'title'=>$lignesDepassees->count().' ligne'.($lignesDepassees->count()>1?'s':'').' en dépassement',
            'sub'=>'Budget dépassé — décision urgente',
            'url'=>route('finance.budgets.index')]);
    }
    if ($nbFacturesEchues > 0) {
        $alerts->push(['type'=>'danger', 'ico'=>'fa-file-invoice-dollar',
            'title'=>$nbFacturesEchues.' facture'.($nbFacturesEchues>1?'s':'').' échue'.($nbFacturesEchues>1?'s':''),
            'sub'=>number_format($montantFacturesEchues,0,',',' ').' XAF non réglé',
            'url'=>route('finance.factures.index')]);
    }
    if ($exercicesSoumis->count() > 0) {
        $alerts->push(['type'=>'warn', 'ico'=>'fa-hourglass-half',
            'title'=>$exercicesSoumis->count().' planification'.($exercicesSoumis->count()>1?'s':'').' à valider',
            'sub'=>'Top management — approbation',
            'url'=>route('finance.exercices.index')]);
    }
    if ($exercicesAttenteCloture->count() > 0) {
        $alerts->push(['type'=>'warn', 'ico'=>'fa-calendar-xmark',
            'title'=>$exercicesAttenteCloture->count().' exercice'.($exercicesAttenteCloture->count()>1?'s':'').' à clôturer',
            'sub'=>'Date de fin dépassée',
            'url'=>route('finance.exercices.index')]);
    }
    if ($lignesEnAlerte->count() > 0) {
        $alerts->push(['type'=>'warn', 'ico'=>'fa-gauge-high',
            'title'=>$lignesEnAlerte->count().' ligne'.($lignesEnAlerte->count()>1?'s':'').' à 90%+',
            'sub'=>'Consommation avancée du budget',
            'url'=>route('finance.budgets.index')]);
    }
    if ($ecrituresBrouillon > 0) {
        $alerts->push(['type'=>'info', 'ico'=>'fa-pen-nib',
            'title'=>$ecrituresBrouillon.' écriture'.($ecrituresBrouillon>1?'s':'').' à valider',
            'sub'=>'Brouillon en attente',
            'url'=>route('finance.grand-livre.index')]);
    }
@endphp
@if($alerts->count())
<div class="fi-alerts">
    @foreach($alerts as $a)
    <a href="{{ $a['url'] }}" class="fi-alert fi-alert-{{ $a['type'] }}">
        <span class="fi-alert-ico"><i class="fas {{ $a['ico'] }}"></i></span>
        <div class="fi-alert-body">
            <div class="fi-alert-title">{{ $a['title'] }}</div>
            <div class="fi-alert-sub">{{ $a['sub'] }}</div>
        </div>
        <i class="fas fa-chevron-right fi-alert-arrow"></i>
    </a>
    @endforeach
</div>
@endif

{{-- ═════════════════ KPIS PRINCIPAUX ═════════════════ --}}
<div class="fi-kpi-grid">
    {{-- KPI 1 — Taux d'exécution budgétaire --}}
    <a href="{{ route('finance.budgets.index') }}" class="fi-kpi">
        <div class="fi-kpi-head">
            <span class="fi-kpi-lbl">Taux d'exécution</span>
            <span class="fi-kpi-ico"><i class="fas fa-gauge-high"></i></span>
        </div>
        <div class="fi-kpi-val">{{ number_format($tauxExecution, 1, ',', ' ') }}<small>%</small></div>
        @php
            $progClass = $tauxExecution >= 100 ? 'is-danger' : ($tauxExecution >= 90 ? 'is-warn' : '');
        @endphp
        <div class="fi-progress"><div class="fi-progress-fill {{ $progClass }}" style="width:{{ min(100, $tauxExecution) }}%;"></div></div>
        <div class="fi-kpi-sub">Reste : {{ number_format($resteAEngager / 1000, 0, ',', ' ') }} K XAF</div>
    </a>

    {{-- KPI 2 — Dépenses ce mois (débits comptables) --}}
    <a href="{{ route('finance.grand-livre.index') }}" class="fi-kpi">
        <div class="fi-kpi-head">
            <span class="fi-kpi-lbl">Dépenses ce mois</span>
            <span class="fi-kpi-ico" style="background:#FEE2E2; color:var(--fi-danger);"><i class="fas fa-money-bill-trend-up"></i></span>
        </div>
        <div class="fi-kpi-val">{{ number_format($montantDebitMois / 1000, 0, ',', ' ') }}<small>K XAF</small></div>
        <div class="fi-kpi-foot">
            @if($tendanceDebit > 0)
                <span class="fi-trend fi-trend-up"><i class="fas fa-arrow-up"></i>{{ $tendanceDebit }}%</span>
            @elseif($tendanceDebit < 0)
                <span class="fi-trend fi-trend-down"><i class="fas fa-arrow-down"></i>{{ abs($tendanceDebit) }}%</span>
            @else
                <span class="fi-trend fi-trend-flat"><i class="fas fa-minus"></i>0%</span>
            @endif
            <div class="fi-spark">
                @foreach($depensesMois as $d)
                    <div class="fi-spark-bar" style="height:{{ max(15, ($d['montant'] / $depensesMax) * 100) }}%;"></div>
                @endforeach
            </div>
        </div>
    </a>

    {{-- KPI 3 — Créances clients --}}
    <a href="{{ route('finance.factures.index') }}?sens=recette" class="fi-kpi">
        <div class="fi-kpi-head">
            <span class="fi-kpi-lbl">Créances clients</span>
            <span class="fi-kpi-ico" style="background:#D1FAE5; color:var(--fi-ok);"><i class="fas fa-file-invoice"></i></span>
        </div>
        <div class="fi-kpi-val">{{ number_format($creancesClients / 1000, 0, ',', ' ') }}<small>K XAF</small></div>
        <div class="fi-kpi-sub">À encaisser</div>
    </a>

    {{-- KPI 4 — Dettes fournisseurs --}}
    <a href="{{ route('finance.factures.index') }}?sens=depense" class="fi-kpi">
        <div class="fi-kpi-head">
            <span class="fi-kpi-lbl">Dettes fournisseurs</span>
            <span class="fi-kpi-ico" style="background:#FEF3C7; color:var(--fi-warn);"><i class="fas fa-file-invoice-dollar"></i></span>
        </div>
        <div class="fi-kpi-val">{{ number_format($dettesFournisseurs / 1000, 0, ',', ' ') }}<small>K XAF</small></div>
        <div class="fi-kpi-sub">À décaisser · {{ $facturesProchainesEcheances }} échéance{{ $facturesProchainesEcheances>1?'s':'' }} 7j</div>
    </a>
</div>

{{-- ═════════════════ CHARTS : Dépenses + Top clients ═════════════════ --}}
<div class="fi-row fi-row-2">
    {{-- Chart barres — dépenses 6 mois --}}
    <div class="fi-card">
        <div class="fi-card-hd">
            <h6>
                <span class="fi-card-hd-ico" style="background:var(--fi-brand-soft); color:var(--fi-brand);"><i class="fas fa-chart-column"></i></span>
                Dépenses mensuelles (6 mois)
            </h6>
            <a href="{{ route('finance.grand-livre.index') }}" class="fi-link">Détail →</a>
        </div>
        <div class="fi-chart-bars">
            <div class="fi-bars">
                @foreach($depensesMois as $i => $d)
                <div class="fi-bar-col {{ $i === count($depensesMois)-1 ? 'is-current' : '' }}"
                     title="{{ $d['label'] }} : {{ number_format($d['montant']/1000, 0, ',', ' ') }} K XAF">
                    <div class="fi-bar-val">{{ $d['montant'] > 0 ? round($d['montant']/1000) : '' }}</div>
                    <div class="fi-bar-outer">
                        <div class="fi-bar-fill" style="height:{{ max(2, ($d['montant'] / $depensesMax) * 100) }}%;"></div>
                    </div>
                    <div class="fi-bar-label">{{ $d['label'] }}</div>
                </div>
                @endforeach
            </div>
            <div class="fi-chart-total">
                <span class="fi-mute">Total 6 mois</span>
                <strong>{{ number_format(array_sum(array_column($depensesMois, 'montant')), 0, ',', ' ') }} XAF</strong>
            </div>
        </div>
    </div>

    {{-- Top clients (12 mois) --}}
    <div class="fi-card">
        <div class="fi-card-hd">
            <h6>
                <span class="fi-card-hd-ico" style="background:#D1FAE5; color:var(--fi-ok);"><i class="fas fa-award"></i></span>
                Top clients (12 mois)
            </h6>
            <a href="{{ route('finance.factures.index') }}?sens=recette" class="fi-link">Voir tout →</a>
        </div>
        <div class="fi-podium">
            @forelse($topClients as $i => $c)
            <div class="fi-podium-row">
                <div class="fi-podium-rank">{{ $i+1 }}</div>
                <div class="fi-podium-info">
                    <div class="fi-podium-name">
                        @php
                            $label = '—';
                            if ($c->tiers_source === 'organisation' && $c->tiers_id) {
                                $orga = \App\Models\Intranet\ContactOrganisation::find($c->tiers_id);
                                $label = $orga?->raison_sociale ?? $orga?->nom ?? 'Tiers #'.$c->tiers_id;
                            } else {
                                $label = 'Tiers #'.$c->tiers_id;
                            }
                        @endphp
                        {{ \Illuminate\Support\Str::limit($label, 35) }}
                    </div>
                    <div class="fi-podium-bar"><div class="fi-podium-bar-fill" style="width:{{ min(100, ($c->total / $topClientsMax) * 100) }}%;"></div></div>
                </div>
                <div class="fi-podium-vals">
                    <strong>{{ number_format($c->total / 1000, 0, ',', ' ') }} K</strong>
                    <span>{{ $c->nb }} fact.</span>
                </div>
            </div>
            @empty
            <div class="fi-empty"><i class="fas fa-users"></i>Aucune facture client sur 12 mois</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═════════════════ Top 5 lignes budgétaires + Alerte dépassement ═════════════════ --}}
<div class="fi-row fi-row-2">
    {{-- Top 5 lignes budgétaires --}}
    <div class="fi-card">
        <div class="fi-card-hd">
            <h6>
                <span class="fi-card-hd-ico" style="background:var(--fi-brand-soft); color:var(--fi-brand);"><i class="fas fa-list-ol"></i></span>
                Top 5 postes budgétaires
                @if($exerciceEnCours)<span class="fi-badge fi-badge-mute ms-1">{{ $exerciceEnCours->libelle ?? $exerciceEnCours->exercice }}</span>@endif
            </h6>
            <a href="{{ route('finance.budgets.index') }}" class="fi-link">Toutes lignes →</a>
        </div>
        <div class="fi-card-tight">
            @forelse($top5Lignes as $l)
            @php
                $color = $l['taux'] >= 100 ? 'var(--fi-danger)' : ($l['taux'] >= 90 ? 'var(--fi-warn)' : 'var(--fi-brand)');
            @endphp
            <div class="fi-ligne-row">
                <div class="fi-ligne-head">
                    <div class="fi-ligne-name">{{ \Illuminate\Support\Str::limit($l['libelle'], 45) }}</div>
                    <div class="fi-ligne-taux" style="color:{{ $color }};">{{ $l['taux'] }}%</div>
                </div>
                <div class="fi-ligne-meta">
                    Engagé <strong>{{ number_format($l['engage']/1000, 0, ',', ' ') }} K</strong> / {{ number_format($l['budget']/1000, 0, ',', ' ') }} K XAF
                </div>
                <div class="fi-ligne-prog"><div class="fi-ligne-prog-fill" style="width:{{ min(100, $l['taux']) }}%; background:{{ $color }};"></div></div>
            </div>
            @empty
            <div class="fi-empty"><i class="fas fa-inbox"></i>Aucune ligne budgétaire</div>
            @endforelse
        </div>
    </div>

    {{-- Alertes : lignes en dépassement --}}
    <div class="fi-card">
        <div class="fi-card-hd">
            <h6>
                <span class="fi-card-hd-ico" style="background:#FEE2E2; color:var(--fi-danger);"><i class="fas fa-triangle-exclamation"></i></span>
                Lignes en dépassement
                <span class="fi-badge fi-badge-danger ms-1">{{ $lignesDepassees->count() }}</span>
            </h6>
            <a href="{{ route('finance.budgets.index') }}" class="fi-link">Traiter →</a>
        </div>
        <div class="fi-card-tight">
            @forelse($lignesDepassees as $l)
            <div class="fi-list-row">
                <div class="fi-list-row-main">
                    <div style="color:var(--fi-ink); font-weight:600;">{{ \Illuminate\Support\Str::limit($l['libelle'], 45) }}</div>
                    <div class="fi-list-row-meta">
                        Budget {{ number_format($l['budget']/1000, 0, ',', ' ') }} K · <span style="color:var(--fi-danger);font-weight:700;">Dépassé de {{ number_format($l['depassement']/1000, 0, ',', ' ') }} K</span>
                    </div>
                </div>
                <div class="fi-list-row-right">
                    <span class="fi-badge fi-badge-danger">+{{ round($l['engage']/$l['budget'] * 100 - 100) }}%</span>
                </div>
            </div>
            @empty
            <div class="fi-empty"><i class="fas fa-check-circle"></i>Aucun dépassement — bien joué</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═════════════════ Factures échues + Dernières écritures ═════════════════ --}}
<div class="fi-row fi-row-2">
    {{-- Factures échues --}}
    <div class="fi-card">
        <div class="fi-card-hd">
            <h6>
                <span class="fi-card-hd-ico" style="background:#FEE2E2; color:var(--fi-danger);"><i class="fas fa-clock-rotate-left"></i></span>
                Factures échues
                @if($nbFacturesEchues > 0)<span class="fi-badge fi-badge-danger ms-1">{{ $nbFacturesEchues }}</span>@endif
            </h6>
            <a href="{{ route('finance.factures.index') }}" class="fi-link">Toutes →</a>
        </div>
        <div class="fi-card-tight">
            @forelse($facturesEchuesListe as $f)
            @php
                $jours = (int) now()->startOfDay()->diffInDays($f->date_echeance->startOfDay(), false);
                $reste = (float) $f->montant_ttc - (float) ($f->montant_regle ?? 0);
            @endphp
            <div class="fi-list-row">
                <div class="fi-list-row-main">
                    <a href="{{ route('finance.factures.show', $f) }}">{{ $f->numero ?? '#'.$f->id }}</a>
                    <div class="fi-list-row-meta">
                        <span class="fi-sens {{ $f->sens }}">{{ ucfirst($f->sens) }}</span>
                        · Échue depuis {{ abs($jours) }} j
                    </div>
                </div>
                <div class="fi-list-row-right">
                    <strong>{{ number_format($reste, 0, ',', ' ') }} XAF</strong>
                    <div class="fi-list-row-meta">Échéance {{ $f->date_echeance->format('d/m/Y') }}</div>
                </div>
            </div>
            @empty
            <div class="fi-empty"><i class="fas fa-check-circle"></i>Aucune facture échue</div>
            @endforelse
        </div>
    </div>

    {{-- Dernières écritures --}}
    <div class="fi-card">
        <div class="fi-card-hd">
            <h6>
                <span class="fi-card-hd-ico" style="background:var(--fi-brand-soft); color:var(--fi-brand);"><i class="fas fa-book"></i></span>
                Dernières écritures
                <span class="fi-badge fi-badge-mute ms-1">{{ $ecrituresMois }} ce mois</span>
            </h6>
            <a href="{{ route('finance.grand-livre.index') }}" class="fi-link">Grand livre →</a>
        </div>
        <div class="fi-card-tight">
            @forelse($dernieresEcritures as $e)
            <div class="fi-list-row">
                <div class="fi-list-row-main">
                    <a href="{{ route('finance.grand-livre.index') }}">{{ \Illuminate\Support\Str::limit($e->libelle ?: $e->description ?: 'Écriture #'.$e->id, 45) }}</a>
                    <div class="fi-list-row-meta">
                        {{ $e->date_ecriture ? \Carbon\Carbon::parse($e->date_ecriture)->format('d/m/Y') : '—' }}
                        @if($e->compte) · {{ \Illuminate\Support\Str::limit($e->compte->libelle ?? $e->compte->numero ?? '', 20) }}@endif
                    </div>
                </div>
                <div class="fi-list-row-right">
                    <strong style="color:{{ $e->sens === 'debit' ? 'var(--fi-danger)' : 'var(--fi-ok)' }};">
                        {{ $e->sens === 'debit' ? '−' : '+' }}{{ number_format((float) $e->montant_tc, 0, ',', ' ') }}
                    </strong>
                    <div class="fi-list-row-meta">
                        @if(!$e->isvalide)<span class="fi-badge fi-badge-warn">Brouillon</span>@endif
                    </div>
                </div>
            </div>
            @empty
            <div class="fi-empty"><i class="fas fa-book"></i>Aucune écriture enregistrée</div>
            @endforelse
        </div>
    </div>
</div>

</div>{{-- /.fi-shell --}}
@endsection
