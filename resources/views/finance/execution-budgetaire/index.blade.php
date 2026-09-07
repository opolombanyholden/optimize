@extends('layouts.app')

@section('title', 'Exécution budgétaire')

@push('styles')
<style>
:root {
    --eb-brand: #4F46E5;
    --eb-brand-deep: #4338CA;
    --eb-brand-soft: #E0E7FF;
    --eb-brand-tint: #EEF2FF;
    --eb-ink: #0F172A;
    --eb-ink-2: #334155;
    --eb-mute: #64748B;
    --eb-mute-2: #94A3B8;
    --eb-line: #E5E7EB;
    --eb-line-2: #F1F5F9;
    --eb-bg: #F8FAFC;
    --eb-ok: #059669;
    --eb-alerte: #D97706;
    --eb-epuise: #B45309;
    --eb-danger: #DC2626;
}
.eb-shell { max-width: 1400px; margin: 0 auto; }

.eb-head {
    background: #fff; border: 1px solid var(--eb-line); border-radius: 8px;
    padding: 1rem 1.25rem; margin-bottom: 1rem;
    display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.eb-head-title { display: flex; align-items: center; gap: .85rem; }
.eb-head-emblem {
    width: 44px; height: 44px; border-radius: 8px; background: var(--eb-brand-soft);
    color: var(--eb-brand); display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem;
    flex-shrink: 0;
}
.eb-head h1 { font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--eb-ink); }
.eb-head p  { margin: 0; font-size: .8rem; color: var(--eb-mute); }

.eb-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: .75rem; margin-bottom: 1.25rem; }
@media (max-width: 480px) { .eb-kpi-grid { grid-template-columns: 1fr; } }
.eb-kpi {
    background: #fff; border: 1px solid var(--eb-line); border-radius: 8px;
    padding: .95rem 1rem; display: flex; flex-direction: column; gap: .4rem;
}
.eb-kpi-lbl { font-size: .66rem; text-transform: uppercase; letter-spacing: .08em; color: var(--eb-mute); font-weight: 700; }
.eb-kpi-val { font-size: 1.35rem; font-weight: 800; color: var(--eb-ink); line-height: 1; }
.eb-kpi-val small { font-size: .68rem; color: var(--eb-mute); font-weight: 500; margin-left: .2rem; }
.eb-kpi-sub { font-size: .7rem; color: var(--eb-mute); }
.eb-kpi-progress { height: 4px; background: var(--eb-line-2); border-radius: 2px; overflow: hidden; }
.eb-kpi-progress-fill { height: 100%; background: var(--eb-brand); }
.eb-kpi-progress-fill.is-alerte { background: var(--eb-alerte); }
.eb-kpi-progress-fill.is-danger { background: var(--eb-danger); }

/* Bandeau filtres */
.eb-filters {
    background: #fff; border: 1px solid var(--eb-line); border-radius: 8px;
    padding: .75rem 1rem; margin-bottom: 1rem;
    display: flex; gap: .5rem; align-items: end; flex-wrap: wrap;
}
.eb-filters label { font-size: .68rem; font-weight: 700; color: var(--eb-mute); letter-spacing: .05em; text-transform: uppercase; margin-bottom: .25rem; display: block; }
.eb-filters .form-control, .eb-filters .form-select { font-size: .82rem; }
.eb-filter-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .35rem .65rem; border-radius: 999px; border: 1px solid var(--eb-line);
    font-size: .72rem; font-weight: 600; color: var(--eb-ink-2); text-decoration: none; background: #fff;
    transition: border-color .15s, background .15s;
}
.eb-filter-chip:hover { border-color: var(--eb-brand); color: var(--eb-brand); }
.eb-filter-chip.is-active { background: var(--eb-brand); color: #fff; border-color: var(--eb-brand); }
.eb-filter-chip.is-active-alerte { background: var(--eb-alerte); color: #fff; border-color: var(--eb-alerte); }
.eb-filter-chip.is-active-epuise { background: var(--eb-epuise); color: #fff; border-color: var(--eb-epuise); }
.eb-filter-chip.is-active-danger { background: var(--eb-danger); color: #fff; border-color: var(--eb-danger); }
.eb-filter-chip.is-active-ok { background: var(--eb-ok); color: #fff; border-color: var(--eb-ok); }
.eb-filter-chip .cnt { background: rgba(0,0,0,.06); padding: .05rem .35rem; border-radius: 6px; font-size: .68rem; font-weight: 700; }
.eb-filter-chip.is-active .cnt, .eb-filter-chip[class*="is-active-"] .cnt { background: rgba(255,255,255,.25); }

/* Table */
.eb-card { background: #fff; border: 1px solid var(--eb-line); border-radius: 8px; overflow: hidden; margin-bottom: 1rem; }
.eb-card-hd {
    display: flex; align-items: center; justify-content: space-between;
    padding: .85rem 1.1rem; border-bottom: 1px solid var(--eb-line-2);
}
.eb-card-hd h6 { margin: 0; font-size: .85rem; font-weight: 700; color: var(--eb-ink); }

.eb-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.eb-table thead th {
    background: #F8FAFC; padding: .55rem .75rem; text-align: left;
    font-size: .65rem; font-weight: 700; color: var(--eb-mute); letter-spacing: .08em; text-transform: uppercase;
    border-bottom: 2px solid var(--eb-line);
}
.eb-table thead th.num { text-align: right; }
.eb-table tbody td { padding: .55rem .75rem; border-bottom: 1px solid var(--eb-line-2); vertical-align: top; }
.eb-table tbody tr:hover { background: #FAFAFA; }
.eb-table tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
.eb-table .cell-code {
    display: inline-block; background: var(--eb-brand-tint); color: var(--eb-brand-deep);
    padding: .1rem .35rem; border-radius: 3px; font-family: ui-monospace, "SF Mono", "Menlo", "Consolas", monospace;
    font-size: .72rem; font-weight: 700;
}
.eb-table .cell-libelle { color: var(--eb-ink); font-weight: 600; }
.eb-table .cell-titre { font-size: .68rem; color: var(--eb-mute); margin-top: .1rem; }
.eb-table .cell-planif { color: var(--eb-ink-2); }
.eb-table .cell-modif { color: var(--eb-brand); font-weight: 600; }
.eb-table .cell-modif.neg { color: var(--eb-danger); }
.eb-table .cell-budget { color: var(--eb-ink); font-weight: 700; }
.eb-table .cell-eng { color: var(--eb-ink-2); }
.eb-table .cell-solde { font-weight: 700; }
.eb-table .cell-solde.neg { color: var(--eb-danger); }
.eb-table .cell-solde.pos { color: var(--eb-ok); }

/* Gauge par ligne */
.eb-gauge { display: flex; align-items: center; gap: .5rem; }
.eb-gauge-bar { flex: 1; height: 6px; background: var(--eb-line-2); border-radius: 3px; overflow: hidden; min-width: 60px; }
.eb-gauge-bar-fill { height: 100%; border-radius: 3px; transition: width .3s; }
.eb-gauge-pct { font-size: .72rem; font-weight: 700; min-width: 40px; text-align: right; }

/* État badge */
.eb-etat {
    display: inline-block; font-size: .62rem; font-weight: 700; padding: .1rem .4rem;
    border-radius: 3px; letter-spacing: .05em; text-transform: uppercase;
}
.eb-etat.ok      { background: #ECFDF5; color: var(--eb-ok); }
.eb-etat.alerte  { background: #FEF3C7; color: var(--eb-alerte); }
.eb-etat.epuise  { background: #FED7AA; color: var(--eb-epuise); }
.eb-etat.depasse { background: #FEE2E2; color: var(--eb-danger); }
.eb-etat.vide    { background: var(--eb-line-2); color: var(--eb-mute); }

/* Row 2 cols */
.eb-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
@media (max-width: 991px) { .eb-row2 { grid-template-columns: 1fr; } }

/* Donut sources */
.eb-donut-wrap { display: flex; align-items: center; gap: 1.25rem; padding: 1rem 1.1rem; flex-wrap: wrap; }
.eb-donut { width: 130px; height: 130px; position: relative; flex-shrink: 0; }
.eb-donut svg { transform: rotate(-90deg); }
.eb-donut-center {
    position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;
}
.eb-donut-center strong { font-size: 1.15rem; font-weight: 800; color: var(--eb-ink); line-height: 1; }
.eb-donut-center span { font-size: .68rem; color: var(--eb-mute); text-transform: uppercase; letter-spacing: .05em; margin-top: .2rem; }
.eb-donut-legend { flex: 1; min-width: 160px; display: flex; flex-direction: column; gap: .5rem; }
.eb-legend-item { display: flex; justify-content: space-between; font-size: .78rem; }
.eb-legend-key { display: inline-flex; align-items: center; gap: .5rem; color: var(--eb-ink-2); }
.eb-legend-dot { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }
.eb-legend-val { font-weight: 700; color: var(--eb-ink); }
.eb-legend-pct { color: var(--eb-mute); font-weight: 600; font-size: .7rem; margin-left: .35rem; }

/* Modif recentes */
.eb-modif-row { padding: .6rem 1.1rem; border-bottom: 1px solid var(--eb-line-2); font-size: .82rem; }
.eb-modif-row:last-child { border-bottom: 0; }
.eb-modif-head { display: flex; justify-content: space-between; align-items: baseline; gap: .5rem; }
.eb-modif-obj { color: var(--eb-ink); font-weight: 600; }
.eb-modif-amt { font-weight: 700; color: var(--eb-brand); font-variant-numeric: tabular-nums; }
.eb-modif-meta { font-size: .7rem; color: var(--eb-mute); margin-top: .15rem; }
.eb-modif-flow { display: inline-flex; align-items: center; gap: .3rem; font-size: .7rem; color: var(--eb-mute); }

.eb-empty { padding: 2rem 1rem; text-align: center; color: var(--eb-mute-2); font-size: .8rem; }
.eb-empty i { display: block; font-size: 1.5rem; margin-bottom: .35rem; opacity: .5; }
</style>
@endpush

@section('content')
<div class="eb-shell">

{{-- ═════════════════ HEADER ═════════════════ --}}
<div class="eb-head">
    <div class="eb-head-title">
        <span class="eb-head-emblem"><i class="fas fa-gauge-high"></i></span>
        <div>
            <h1>Exécution budgétaire</h1>
            <p>
                @if($exercice)
                    Exercice <strong>{{ $exercice->libelle ?? $exercice->exercice }}</strong>
                    · {{ $rows->count() }} ligne(s) affichée(s)
                @else
                    Aucun exercice sélectionné
                @endif
            </p>
        </div>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-end">
        <div>
            <label style="font-size:.68rem; font-weight:700; color:var(--eb-mute); letter-spacing:.05em; text-transform:uppercase;">Exercice</label>
            <select name="exercice" class="form-select" onchange="this.form.submit()" style="min-width:180px;">
                @foreach($exercicesDisponibles as $ex)
                    <option value="{{ $ex->id }}" @selected($ex->id == $exerciceId)>{{ $ex->libelle ?? $ex->exercice }}</option>
                @endforeach
            </select>
        </div>
        @if($filtreTitre || $filtreEtat || $filtreQ)
        <a href="{{ route('finance.execution-budgetaire', ['exercice'=>$exerciceId]) }}" class="btn btn-outline-secondary btn-sm">Réinit. filtres</a>
        @endif
    </form>
</div>

@if(!$exercice)
<div class="alert alert-warning">Aucun exercice trouvé. Créez-en un depuis <a href="{{ route('finance.exercices.index') }}">Finance → Exercices</a>.</div>
@else

{{-- ═════════════════ KPIS GLOBAUX ═════════════════ --}}
<div class="eb-kpi-grid">
    <div class="eb-kpi">
        <span class="eb-kpi-lbl">Fond propre</span>
        <div class="eb-kpi-val">{{ number_format($totaux['fond_propre'] / 1000, 0, ',', ' ') }}<small>K XAF</small></div>
        <div class="eb-kpi-sub">Fonds internes + reports</div>
    </div>
    <div class="eb-kpi">
        <span class="eb-kpi-lbl">Fond alloué</span>
        <div class="eb-kpi-val">{{ number_format($totaux['fond_alloue'] / 1000, 0, ',', ' ') }}<small>K XAF</small></div>
        <div class="eb-kpi-sub">Dotation de l'État</div>
    </div>
    <div class="eb-kpi">
        <span class="eb-kpi-lbl">Budget initial</span>
        <div class="eb-kpi-val">{{ number_format($totaux['budget_init'] / 1000, 0, ',', ' ') }}<small>K XAF</small></div>
        <div class="eb-kpi-sub">Fond propre + Fond alloué</div>
    </div>
    <div class="eb-kpi">
        <span class="eb-kpi-lbl">Modifications nettes</span>
        <div class="eb-kpi-val" style="color:{{ $totaux['modifs'] >= 0 ? 'var(--eb-brand)' : 'var(--eb-danger)' }};">
            {{ $totaux['modifs'] >= 0 ? '+' : '' }}{{ number_format($totaux['modifs'] / 1000, 0, ',', ' ') }}<small>K XAF</small>
        </div>
        <div class="eb-kpi-sub">Transferts + apports appliqués</div>
    </div>
    <div class="eb-kpi">
        <span class="eb-kpi-lbl">Budget final</span>
        <div class="eb-kpi-val">{{ number_format($totaux['budget'] / 1000, 0, ',', ' ') }}<small>K XAF</small></div>
        <div class="eb-kpi-sub">Budget initial + Modifs</div>
    </div>
    <div class="eb-kpi">
        <span class="eb-kpi-lbl">Consommation</span>
        <div class="eb-kpi-val">{{ number_format($totaux['engagement'] / 1000, 0, ',', ' ') }}<small>K XAF</small></div>
        @php $tauxClass = $totaux['taux'] >= 100 ? 'is-danger' : ($totaux['taux'] >= 90 ? 'is-alerte' : ''); @endphp
        <div class="eb-kpi-progress"><div class="eb-kpi-progress-fill {{ $tauxClass }}" style="width:{{ min(100, $totaux['taux']) }}%;"></div></div>
        <div class="eb-kpi-sub"><strong>{{ number_format($totaux['taux'], 1, ',', ' ') }} %</strong> engagé</div>
    </div>
    <div class="eb-kpi">
        <span class="eb-kpi-lbl">Solde disponible</span>
        <div class="eb-kpi-val" style="color:{{ $totaux['solde'] >= 0 ? 'var(--eb-ok)' : 'var(--eb-danger)' }};">
            {{ number_format($totaux['solde'] / 1000, 0, ',', ' ') }}<small>K XAF</small>
        </div>
        <div class="eb-kpi-sub">Reste à engager</div>
    </div>
</div>

{{-- ═════════════════ Sources + Modifs récentes ═════════════════ --}}
<div class="eb-row2">
    <div class="eb-card">
        <div class="eb-card-hd"><h6><i class="fas fa-chart-pie me-1" style="color:var(--eb-brand);"></i>Répartition des sources de financement</h6></div>
        @php
            $c = 251.327;
            $palette = ['#4F46E5', '#059669', '#D97706', '#0891B2'];
            $labels  = ['dotation_etat'=>"Dotation de l'État",'fonds_propres'=>"Fonds propres",'reports_budgetaire'=>"Reports budgétaires",'reports_tresorerie'=>"Reports trésorerie"];
            $offset = 0; $i = 0; $segments = [];
            foreach ($sourcesFin as $k => $v) {
                if ($v <= 0) { $i++; continue; }
                $len = ($v / $sourcesTotal) * $c;
                $segments[] = ['label'=>$labels[$k], 'val'=>$v, 'pct'=>round($v/$sourcesTotal*100,1), 'len'=>$len, 'off'=>$offset, 'color'=>$palette[$i]];
                $offset += $len; $i++;
            }
        @endphp
        <div class="eb-donut-wrap">
            <div class="eb-donut">
                <svg width="130" height="130" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#F1F5F9" stroke-width="14"/>
                    @foreach($segments as $s)
                    <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $s['color'] }}" stroke-width="14"
                            stroke-dasharray="{{ $s['len'] }} {{ $c - $s['len'] }}" stroke-dashoffset="{{ -$s['off'] }}"/>
                    @endforeach
                </svg>
                <div class="eb-donut-center">
                    <strong>{{ number_format($sourcesTotal / 1000, 0, ',', ' ') }}</strong>
                    <span>K XAF</span>
                </div>
            </div>
            <div class="eb-donut-legend">
                @forelse($segments as $s)
                <div class="eb-legend-item">
                    <span class="eb-legend-key"><span class="eb-legend-dot" style="background:{{ $s['color'] }};"></span>{{ $s['label'] }}</span>
                    <span><span class="eb-legend-val">{{ number_format($s['val']/1000, 0, ',', ' ') }} K</span><span class="eb-legend-pct">{{ $s['pct'] }}%</span></span>
                </div>
                @empty
                <div class="eb-empty" style="padding:.5rem 0;">Aucune source renseignée</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="eb-card">
        <div class="eb-card-hd">
            <h6><i class="fas fa-shuffle me-1" style="color:var(--eb-brand);"></i>Modifications appliquées récentes</h6>
            <a href="{{ route('finance.modifications-budgetaires.index') }}" style="font-size:.72rem; font-weight:600; color:var(--eb-brand); text-decoration:none;">Toutes →</a>
        </div>
        @forelse($modifsRecentes as $m)
        <div class="eb-modif-row">
            <div class="eb-modif-head">
                <div class="eb-modif-obj">{{ \Illuminate\Support\Str::limit($m->objetmodification, 55) }}</div>
                <div class="eb-modif-amt">+{{ number_format((float) $m->montant_modification, 0, ',', ' ') }} XAF</div>
            </div>
            <div class="eb-modif-meta">
                <span class="badge" style="background:var(--eb-brand-soft); color:var(--eb-brand-deep); font-size:.62rem;">{{ ucfirst($m->type_modification) }}</span>
                @if($m->ligneSource)
                <span class="eb-modif-flow"><i class="fas fa-arrow-right-from-bracket"></i> {{ \Illuminate\Support\Str::limit($m->ligneSource->codecompte ?: $m->ligneSource->budgetligne, 20) }}</span>
                @endif
                @if($m->ligneDestination)
                <span class="eb-modif-flow"><i class="fas fa-arrow-right-to-bracket"></i> {{ \Illuminate\Support\Str::limit($m->ligneDestination->codecompte ?: $m->ligneDestination->budgetligne, 20) }}</span>
                @endif
                · {{ $m->updated_at?->diffForHumans() }}
            </div>
        </div>
        @empty
        <div class="eb-empty"><i class="fas fa-shuffle"></i>Aucune modification appliquée sur cet exercice</div>
        @endforelse
    </div>
</div>

{{-- ═════════════════ FILTRES + TABLE ═════════════════ --}}
<div class="eb-filters">
    <form method="GET" style="display:flex; gap:.5rem; align-items:end; flex-wrap:wrap; flex:1;">
        <input type="hidden" name="exercice" value="{{ $exerciceId }}">
        <div style="flex:1; min-width:180px;">
            <label>Recherche</label>
            <input type="text" name="q" value="{{ $filtreQ }}" class="form-control" placeholder="Code, libellé, imputation…">
        </div>
        <div style="min-width:180px;">
            <label>Titre / Famille</label>
            <select name="titre" class="form-select" onchange="this.form.submit()">
                <option value="">Tous les titres</option>
                @foreach($titres as $t)
                    <option value="{{ $t->id }}" @selected($filtreTitre == $t->id)>{{ $t->imputation }} · {{ $t->libelle }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
    </form>
    <div style="display:flex; gap:.4rem; align-items:center; flex-wrap:wrap;">
        @php
            $qs = fn($etat) => ['exercice'=>$exerciceId, 'q'=>$filtreQ, 'titre'=>$filtreTitre, 'etat'=>$filtreEtat === $etat ? null : $etat];
        @endphp
        <a href="{{ route('finance.execution-budgetaire', $qs('ok')) }}" class="eb-filter-chip {{ $filtreEtat === 'ok' ? 'is-active-ok' : '' }}">
            <i class="fas fa-circle-check"></i> OK <span class="cnt">{{ $compteEtat['ok'] }}</span>
        </a>
        <a href="{{ route('finance.execution-budgetaire', $qs('alerte')) }}" class="eb-filter-chip {{ $filtreEtat === 'alerte' ? 'is-active-alerte' : '' }}">
            <i class="fas fa-triangle-exclamation"></i> Alerte ≥90% <span class="cnt">{{ $compteEtat['alerte'] }}</span>
        </a>
        <a href="{{ route('finance.execution-budgetaire', $qs('epuise')) }}" class="eb-filter-chip {{ $filtreEtat === 'epuise' ? 'is-active-epuise' : '' }}">
            <i class="fas fa-battery-empty"></i> Épuisé <span class="cnt">{{ $compteEtat['epuise'] }}</span>
        </a>
        <a href="{{ route('finance.execution-budgetaire', $qs('depasse')) }}" class="eb-filter-chip {{ $filtreEtat === 'depasse' ? 'is-active-danger' : '' }}">
            <i class="fas fa-arrow-up-right-from-square"></i> Dépassé <span class="cnt">{{ $compteEtat['depasse'] }}</span>
        </a>
    </div>
</div>

<div class="eb-card">
    <div class="eb-card-hd">
        <h6><i class="fas fa-table-list me-1" style="color:var(--eb-brand);"></i>Détail par ligne budgétaire</h6>
        <span style="font-size:.7rem; color:var(--eb-mute);">{{ $rows->count() }} ligne(s)</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="eb-table">
            <thead>
                <tr>
                    <th style="min-width:140px;">Ligne</th>
                    <th class="num" title="Fonds propres + Reports budgétaires + Reports trésorerie">Fond propre</th>
                    <th class="num" title="Dotation de l'État">Fond alloué</th>
                    <th class="num" title="Fond propre + Fond alloué">Budget initial</th>
                    <th class="num" title="Modifications nettes appliquées">Modifs</th>
                    <th class="num" title="Budget initial + Modifications">Budget final</th>
                    <th class="num" title="Engagement">Consommé</th>
                    <th class="num" title="Budget final - Engagement">Solde</th>
                    <th style="min-width:140px;">Exécution</th>
                    <th style="min-width:80px;">État</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                @php
                    $barColor = match($r->etat) {
                        'depasse' => 'var(--eb-danger)',
                        'epuise'  => 'var(--eb-epuise)',
                        'alerte'  => 'var(--eb-alerte)',
                        'vide'    => 'var(--eb-mute-2)',
                        default   => 'var(--eb-brand)',
                    };
                @endphp
                <tr>
                    <td>
                        <span class="cell-code">{{ \Illuminate\Support\Str::limit($r->code, 18) }}</span>
                        <div class="cell-libelle">{{ \Illuminate\Support\Str::limit($r->libelle, 48) }}</div>
                        @if($r->titre)<div class="cell-titre">{{ $r->titre }}</div>@endif
                    </td>
                    <td class="num cell-planif" title="FP {{ number_format($r->fonds_propres, 0, ',', ' ') }} · RB {{ number_format($r->reports_budgetaire, 0, ',', ' ') }} · RT {{ number_format($r->reports_tresorerie, 0, ',', ' ') }}">
                        {{ number_format($r->fond_propre, 0, ',', ' ') }}
                    </td>
                    <td class="num cell-planif" title="Dotation de l'État">
                        {{ number_format($r->fond_alloue, 0, ',', ' ') }}
                    </td>
                    <td class="num cell-budget">{{ number_format($r->budget_init, 0, ',', ' ') }}</td>
                    <td class="num cell-modif {{ $r->modifs < 0 ? 'neg' : '' }}">
                        {{ $r->modifs > 0 ? '+' : ($r->modifs < 0 ? '−' : '') }}{{ number_format(abs($r->modifs), 0, ',', ' ') }}
                    </td>
                    <td class="num cell-budget">{{ number_format($r->budget, 0, ',', ' ') }}</td>
                    <td class="num cell-eng">{{ number_format($r->engagement, 0, ',', ' ') }}</td>
                    <td class="num cell-solde {{ $r->solde < 0 ? 'neg' : ($r->solde > 0 ? 'pos' : '') }}">
                        {{ number_format($r->solde, 0, ',', ' ') }}
                    </td>
                    <td>
                        <div class="eb-gauge">
                            <div class="eb-gauge-bar">
                                <div class="eb-gauge-bar-fill" style="width:{{ min(100, $r->taux) }}%; background:{{ $barColor }};"></div>
                            </div>
                            <span class="eb-gauge-pct" style="color:{{ $barColor }};">{{ number_format($r->taux, 0, ',', ' ') }}%</span>
                        </div>
                    </td>
                    <td>
                        <span class="eb-etat {{ $r->etat }}">
                            @switch($r->etat)
                                @case('depasse') Dépassé @break
                                @case('epuise')  Épuisé  @break
                                @case('alerte')  Alerte  @break
                                @case('vide')    Vide    @break
                                @default        OK
                            @endswitch
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="eb-empty">
                    <i class="fas fa-inbox"></i>
                    @if($filtreEtat || $filtreTitre || $filtreQ)
                        Aucune ligne ne correspond à vos filtres.
                    @else
                        Aucune ligne budgétaire sur cet exercice.
                    @endif
                </td></tr>
                @endforelse
            </tbody>
            @if($rows->count() > 0)
            <tfoot>
                <tr style="background:#F8FAFC; font-weight:700;">
                    <td>TOTAUX</td>
                    <td class="num">{{ number_format($rows->sum('fond_propre'), 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($rows->sum('fond_alloue'), 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($rows->sum('budget_init'), 0, ',', ' ') }}</td>
                    <td class="num" style="color:{{ $rows->sum('modifs') >= 0 ? 'var(--eb-brand)' : 'var(--eb-danger)' }};">
                        {{ $rows->sum('modifs') > 0 ? '+' : '' }}{{ number_format($rows->sum('modifs'), 0, ',', ' ') }}
                    </td>
                    <td class="num">{{ number_format($rows->sum('budget'), 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($rows->sum('engagement'), 0, ',', ' ') }}</td>
                    <td class="num" style="color:{{ $rows->sum('solde') >= 0 ? 'var(--eb-ok)' : 'var(--eb-danger)' }};">
                        {{ number_format($rows->sum('solde'), 0, ',', ' ') }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endif

</div>{{-- /.eb-shell --}}
@endsection
