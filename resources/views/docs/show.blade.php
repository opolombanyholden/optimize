@extends('layouts.docs')

@section('title', $guide['title'])

@section('crumb')
    <a href="{{ route('docs.index') }}" class="ds-crumb is-link">Bibliothèque</a>
    <span class="ds-sep">/</span>
    <span class="ds-crumb">{{ $guide['title'] }}</span>
@endsection

@section('actions')
    <a href="{{ route('docs.pdf', $slug) }}" class="ds-btn ds-btn-primary">
        <i class="fas fa-file-pdf"></i> <span class="ds-btn-label">Télécharger PDF</span>
    </a>
@endsection

@push('styles')
<style>
    /* Styles web (grand format) pour le contenu du guide — repris du design du guide print */
    .doc { max-width: 800px; margin: 0 auto; padding: 2rem 1.5rem 5rem; }

    /* Bandeau accent en tête (remplace la cover imprimée) */
    .doc-hero {
        background: {{ $guide['accent_soft'] }};
        border-left: 4px solid {{ $guide['accent'] }};
        padding: 1.5rem 1.75rem; margin-bottom: 3rem;
    }
    .doc-hero-eyebrow {
        font-family: var(--ds-sans); font-size: .7rem; font-weight: 700;
        letter-spacing: .15em; text-transform: uppercase; color: {{ $guide['accent'] }};
        margin-bottom: .5rem;
    }
    .doc-hero-title {
        font-family: var(--ds-sans); font-size: 1.6rem; font-weight: 800;
        color: var(--ds-ink); margin: 0 0 .35rem; letter-spacing: -0.01em;
    }
    .doc-hero-sub {
        font-family: var(--ds-serif); font-style: italic; color: var(--ds-body);
        font-size: .98rem; margin: 0;
    }

    /* Sommaire */
    .toc { border-left: 3px solid {{ $guide['accent'] }}; padding-left: 1.5rem; margin: 3rem 0; }
    .toc h2 { font-family: var(--ds-sans); font-size: .75rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; color: var(--ds-mute); margin: 0 0 1rem; }
    .toc-list { list-style: none; padding: 0; margin: 0; font-family: var(--ds-sans); font-size: .92rem; }
    .toc-list > li { padding: .35rem 0; border-bottom: 1px dotted var(--ds-line); display: flex; justify-content: space-between; gap: 1rem; }
    .toc-list > li:last-child { border-bottom: none; }
    .toc-list a { color: var(--ds-ink); text-decoration: none; }
    .toc-list a:hover { color: {{ $guide['accent'] }}; }

    /* Typography */
    .doc h2.chapter {
        font-family: var(--ds-sans); font-size: 2rem; font-weight: 800; letter-spacing: -0.015em;
        color: var(--ds-ink); margin: 4rem 0 .35rem; line-height: 1.15;
        border-top: 1px solid var(--ds-line); padding-top: 2.5rem;
    }
    .doc h2.chapter .chapter-num {
        display: block; font-family: var(--ds-mono); font-size: .8rem; font-weight: 500;
        color: {{ $guide['accent'] }}; letter-spacing: .1em; margin-bottom: .5rem;
    }
    .doc .chapter-lead {
        font-family: var(--ds-serif); font-style: italic; font-size: 1.1rem; color: var(--ds-mute);
        margin: 0 0 2.5rem;
    }
    .doc h3 { font-family: var(--ds-sans); font-size: 1.25rem; font-weight: 700; color: var(--ds-ink); margin: 2.5rem 0 .75rem; letter-spacing: -0.005em; }
    .doc h4 { font-family: var(--ds-sans); font-size: 1rem; font-weight: 700; color: var(--ds-ink); margin: 1.5rem 0 .5rem; }
    .doc p { margin: 0 0 1rem; }
    .doc strong { color: var(--ds-ink); font-weight: 600; }

    /* Callouts */
    .doc .callout {
        padding: 1rem 1.15rem; border-left: 3px solid; margin: 1.5rem 0;
        font-family: var(--ds-sans); font-size: .9rem; line-height: 1.55; background: var(--ds-panel);
    }
    .doc .callout p { margin: 0 0 .4rem; }
    .doc .callout p:last-child { margin-bottom: 0; }
    .doc .callout-label {
        display: block; font-size: .68rem; font-weight: 800; letter-spacing: .12em;
        text-transform: uppercase; margin-bottom: .35rem;
    }
    .doc .callout-tip { border-color: {{ $guide['accent'] }}; background: {{ $guide['accent_soft'] }}; }
    .doc .callout-tip .callout-label { color: {{ $guide['accent'] }}; }
    .doc .callout-warn { border-color: #DC2626; background: #FEE2E2; }
    .doc .callout-warn .callout-label { color: #DC2626; }
    .doc .callout-info { border-color: #0A66C2; background: #DBEAFE; }
    .doc .callout-info .callout-label { color: #0A66C2; }
    .doc .callout-ok { border-color: #059669; background: #D1FAE5; }
    .doc .callout-ok .callout-label { color: #059669; }

    /* Steps — table-based (compat dompdf + web) */
    .doc table.steps { border-collapse: collapse; margin: 1.5rem 0 2rem; width: 100%; }
    .doc table.steps tr { border-bottom: 1px dotted var(--ds-line-2); }
    .doc table.steps tr:last-child { border-bottom: none; }
    .doc table.steps .step-num {
        width: 2.5rem; padding: .55rem .5rem .5rem 0; vertical-align: top;
        font-family: var(--ds-mono); font-size: .8rem; font-weight: 700;
        color: {{ $guide['accent'] }}; text-align: center;
    }
    .doc table.steps .step-num span {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.85rem; height: 1.85rem; border-radius: 50%;
        background: {{ $guide['accent_soft'] }};
    }
    .doc table.steps .step-body { padding: .5rem 0 1rem .5rem; vertical-align: top; }
    .doc table.steps .step-title { font-family: var(--ds-sans); font-weight: 700; color: var(--ds-ink); font-size: .98rem; margin-bottom: .25rem; }
    .doc table.steps .step-body p { margin-bottom: .5rem; }
    .doc table.steps .step-body p:last-child { margin-bottom: 0; }

    /* Screen mockups */
    .doc .screen { margin: 1.5rem 0 2rem; }
    .doc .screen-frame { border: 1px solid var(--ds-line); background: #fff; padding: .35rem; }
    .doc .screen-caption { font-family: var(--ds-sans); font-size: .78rem; color: var(--ds-mute); margin-top: .6rem; text-align: center; }
    .doc .screen-caption strong { color: var(--ds-ink); font-weight: 600; }

    /* ══ Maquettes HTML (identiques web + PDF) ══ */
    .doc .mock { padding: 0; font-family: var(--ds-sans); }

    /* Login mock */
    .doc .mock-login { background: #0F172A; padding: 2.5rem 1.5rem; }
    .doc .mock-login-card { background: #fff; padding: 1.75rem; max-width: 320px; margin: 0 auto; }
    .doc .mock-login-brand { font-size: 1.4rem; font-weight: 800; text-align: center; color: #0F172A; margin: 0 0 .1rem; }
    .doc .mock-login-sub { font-size: .7rem; text-align: center; color: #64748B; margin: 0 0 1.5rem; }
    .doc table.mock-form { width: 100%; border-collapse: collapse; }
    .doc table.mock-form td { padding: .4rem 0; vertical-align: top; }
    .doc table.mock-form td.mock-annot { width: 2.25rem; padding-top: 1.35rem; padding-right: .5rem; }
    .doc .mock-annot span { display: inline-flex; align-items: center; justify-content: center; background: #171717; color: #fff; font-size: .72rem; font-weight: 700; width: 1.5rem; height: 1.5rem; border-radius: 50%; }
    .doc .mock-label { font-size: .68rem; font-weight: 700; color: #334155; letter-spacing: .08em; margin-bottom: .3rem; text-transform: uppercase; }
    .doc .mock-input { background: #F8FAFC; border: 1px solid #E2E8F0; padding: .55rem .75rem; font-size: .82rem; color: #0F172A; }
    .doc .mock-btn-primary { background: #0A66C2; color: #fff; padding: .75rem; text-align: center; font-weight: 700; font-size: .82rem; letter-spacing: .05em; }
    .doc .mock-forgot { font-size: .72rem; color: #94A3B8; text-align: center; margin-top: .85rem; }

    /* Workspace picker mock */
    .doc .mock-picker { background: #fff; padding: 1.5rem; border-left: 3px solid #7C3AED; }
    .doc .mock-picker-title { font-size: 1.05rem; font-weight: 700; color: #171717; margin: 0 0 .2rem; }
    .doc .mock-picker-sub { font-size: .82rem; color: #64748B; margin: 0 0 1.15rem; }
    .doc .mock-picker-section { font-size: .68rem; font-weight: 800; color: #64748B; letter-spacing: .1em; margin: 1rem 0 .5rem; text-transform: uppercase; }
    .doc table.mock-grid { width: 100%; border-collapse: collapse; margin-bottom: .5rem; }
    .doc table.mock-grid td { padding: .25rem; vertical-align: top; width: 33.33%; }
    .doc .mock-card { background: #fff; border: 1px solid #E0DFDC; padding: .75rem; }
    .doc .mock-card-featured { border: 2px solid #D97706; background: #FFFBEB; }
    .doc .mock-card-name { font-size: .88rem; font-weight: 700; color: #171717; }
    .doc .mock-card-featured .mock-card-name { color: #B45309; }
    .doc .mock-card-desc { font-size: .7rem; color: #64748B; margin-top: .2rem; }

    /* Dashboard mock */
    .doc .mock-dash { background: #F8FAFC; padding: .75rem; }
    .doc .mock-dash-hd { background: #fff; border: 1px solid #E5E7EB; padding: .85rem 1.1rem; margin-bottom: .5rem; }
    .doc .mock-dash-hd table { width: 100%; border-collapse: collapse; }
    .doc .mock-dash-hd td { vertical-align: middle; }
    .doc .mock-dash-hd .title { font-size: 1rem; font-weight: 700; color: #0F172A; }
    .doc .mock-dash-hd .sub { font-size: .72rem; color: #64748B; margin-top: .15rem; }
    .doc .mock-dash-hd .cta { background: #D97706; color: #fff; padding: .35rem .75rem; font-size: .72rem; font-weight: 700; text-align: center; display: inline-block; }

    .doc table.mock-alerts { width: 100%; border-collapse: collapse; margin-bottom: .5rem; }
    .doc table.mock-alerts td { padding: .25rem; vertical-align: top; width: 33.33%; }
    .doc .mock-alert { background: #fff; border: 1px solid #E5E7EB; border-left: 3px solid #DC2626; padding: .55rem .75rem; }
    .doc .mock-alert.warn { border-left-color: #D97706; }
    .doc .mock-alert.info { border-left-color: #0A66C2; }
    .doc .mock-alert-t { font-size: .78rem; font-weight: 700; color: #171717; }
    .doc .mock-alert-s { font-size: .68rem; color: #64748B; margin-top: .15rem; }

    .doc table.mock-kpis { width: 100%; border-collapse: collapse; margin-bottom: .5rem; }
    .doc table.mock-kpis td { padding: .25rem; vertical-align: top; width: 25%; }
    .doc .mock-kpi { background: #fff; border: 1px solid #E5E7EB; padding: .65rem .8rem; }
    .doc .mock-kpi-l { font-size: .62rem; font-weight: 700; color: #64748B; letter-spacing: .1em; text-transform: uppercase; }
    .doc .mock-kpi-v { font-size: 1.35rem; font-weight: 800; color: #0F172A; margin: .25rem 0; line-height: 1; }
    .doc .mock-kpi-v small { font-size: .7rem; font-weight: 500; color: #64748B; }
    .doc .mock-kpi-t { display: inline-block; background: #ECFDF5; color: #059669; padding: .05rem .4rem; font-size: .68rem; font-weight: 700; }
    .doc .mock-kpi-s { font-size: .68rem; color: #64748B; }

    .doc table.mock-charts { width: 100%; border-collapse: collapse; }
    .doc table.mock-charts td { padding: .25rem; vertical-align: top; width: 50%; }
    .doc .mock-chart { background: #fff; border: 1px solid #E5E7EB; padding: .85rem; }
    .doc .mock-chart-t { font-size: .78rem; font-weight: 700; color: #171717; margin-bottom: .85rem; }
    .doc table.mock-chart-bars { width: 100%; border-collapse: collapse; }
    .doc table.mock-chart-bars td { vertical-align: bottom; padding: 0 2px; text-align: center; }
    .doc .mock-bar { background: #D97706; margin: 0 auto; width: 1.15rem; border-radius: 2px 2px 0 0; }
    .doc .mock-bar-cur { background: #0A66C2; }
    .doc .mock-bar-lbl { font-size: .62rem; color: #64748B; margin-top: .25rem; text-transform: capitalize; }
    .doc table.mock-top { width: 100%; border-collapse: collapse; font-size: .78rem; }
    .doc table.mock-top td { padding: .35rem .25rem; border-bottom: 1px solid #F1F5F9; }
    .doc table.mock-top .rank { width: 1.5rem; text-align: center; font-weight: 700; color: #B45309; background: #FEF3C7; border-radius: 50%; padding: .1rem 0; }
    .doc table.mock-top .amt { text-align: right; font-weight: 700; color: #0F172A; }

    /* Popup rupture mock */
    .doc .mock-popup { background: #fff; border: 1px solid #FCA5A5; }
    .doc .mock-popup-hd { background: #FEF2F2; padding: .85rem 1.15rem; border-bottom: 1px solid #FCA5A5; }
    .doc .mock-popup-hd .t { font-size: 1rem; font-weight: 800; color: #DC2626; }
    .doc .mock-popup-hd .s { font-size: .72rem; color: #64748B; margin-top: .25rem; }
    .doc table.mock-popup-body { width: 100%; border-collapse: collapse; }
    .doc table.mock-popup-body th { background: #F1F5F9; padding: .5rem .85rem; text-align: left; font-size: .65rem; font-weight: 700; color: #334155; letter-spacing: .08em; text-transform: uppercase; }
    .doc table.mock-popup-body td { padding: .55rem .85rem; border-bottom: 1px solid #F1F5F9; font-size: .82rem; color: #0F172A; }
    .doc table.mock-popup-body td.qty { text-align: right; }
    .doc table.mock-popup-body td.qty strong { color: #DC2626; font-weight: 700; }
    .doc .badge-rupt { display: inline-block; background: #FEE2E2; color: #DC2626; font-size: .6rem; font-weight: 700; padding: .1rem .4rem; letter-spacing: .05em; margin-left: .35rem; }
    .doc .btn-reappro { display: inline-block; background: #DC2626; color: #fff; font-size: .68rem; font-weight: 700; padding: .28rem .6rem; }
    .doc .mock-popup-ft { background: #FEF2F2; padding: .7rem 1.15rem; border-top: 1px solid #FCA5A5; }
    .doc .btn-cmd { background: #D97706; color: #fff; padding: .5rem .85rem; font-size: .8rem; font-weight: 700; display: inline-block; }
    .doc table.screen-annots { border-collapse: collapse; width: 100%; margin: 1rem 0 0; }
    .doc table.screen-annots td { padding: .3rem 0; vertical-align: top; font-family: var(--ds-sans); font-size: .88rem; }
    .doc table.screen-annots .num {
        width: 2.25rem; padding-right: .75rem;
    }
    .doc table.screen-annots .num span {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.5rem; height: 1.5rem; background: var(--ds-ink); color: #fff;
        border-radius: 50%; font-size: .72rem; font-weight: 700;
    }
    .doc table.screen-annots strong { color: var(--ds-ink); }

    /* Flow (workflow horizontal) — table pour dompdf */
    .doc table.flow { border-collapse: collapse; width: 100%; margin: 1.5rem 0; }
    .doc table.flow td {
        border: 1px solid var(--ds-line); background: var(--ds-panel);
        padding: .85rem 1rem; font-family: var(--ds-sans);
        vertical-align: top; width: 25%;
    }
    .doc table.flow .flow-num { font-family: var(--ds-mono); font-size: .68rem; color: {{ $guide['accent'] }}; font-weight: 700; letter-spacing: .1em; }
    .doc table.flow .flow-name { font-size: .82rem; font-weight: 700; color: var(--ds-ink); margin-top: .2rem; }
    .doc table.flow .flow-who { font-size: .72rem; color: var(--ds-mute); margin-top: .2rem; }

    /* SVG font pour les codes techniques */
    .doc svg text.mono { font-family: "Courier New", "Consolas", ui-monospace, monospace; }

    /* Tables */
    .doc table.data { width: 100%; border-collapse: collapse; margin: 1.5rem 0; font-family: var(--ds-sans); font-size: .88rem; }
    .doc table.data th, .doc table.data td { padding: .65rem .8rem; border-bottom: 1px solid var(--ds-line); text-align: left; vertical-align: top; }
    .doc table.data th { font-size: .7rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ds-mute); border-bottom: 2px solid var(--ds-ink); background: var(--ds-panel); }
    .doc table.data code { font-family: var(--ds-mono); font-size: .82rem; background: var(--ds-line-2); padding: .05rem .3rem; border-radius: 3px; color: var(--ds-ink); }

    /* Flow */
    .doc .flow { display: flex; align-items: stretch; gap: 0; margin: 1.5rem 0; overflow-x: auto; }
    .doc .flow-step { flex: 1; min-width: 130px; padding: .85rem 1rem; border: 1px solid var(--ds-line); background: var(--ds-panel); font-family: var(--ds-sans); }
    .doc .flow-step + .flow-step { margin-left: -1px; }
    .doc .flow-step-num { font-family: var(--ds-mono); font-size: .68rem; color: {{ $guide['accent'] }}; font-weight: 700; letter-spacing: .1em; }
    .doc .flow-step-name { font-size: .82rem; font-weight: 700; color: var(--ds-ink); margin-top: .2rem; }
    .doc .flow-step-who { font-size: .72rem; color: var(--ds-mute); margin-top: .2rem; }

    /* Btn refs */
    .doc .btn-ref {
        display: inline-flex; align-items: center; gap: .3rem; padding: .1rem .5rem;
        background: {{ $guide['accent'] }}; color: #fff; font-family: var(--ds-sans); font-weight: 600;
        font-size: .82rem; border-radius: 3px; vertical-align: baseline;
    }
    .doc .btn-ref.btn-neutral { background: var(--ds-line); color: var(--ds-ink); }
    .doc .btn-ref.btn-danger { background: #DC2626; color: #fff; }

    /* Checklist */
    .doc ul.checklist { list-style: none; padding: 0; margin: 1rem 0 2rem; font-family: var(--ds-sans); font-size: .92rem; }
    .doc ul.checklist li { padding: .4rem 0 .4rem 1.85rem; position: relative; border-bottom: 1px dotted var(--ds-line-2); }
    .doc ul.checklist li::before { content: ""; position: absolute; left: 0; top: .65rem; width: 1.1rem; height: 1.1rem; border: 1.5px solid var(--ds-faint); border-radius: 3px; }

    .doc hr.divider { border: 0; border-top: 1px solid var(--ds-line); margin: 3rem 0; }

    @media (max-width: 640px) {
        .doc { padding: 1rem 1rem 3rem; font-size: 15px; }
        .doc h2.chapter { font-size: 1.55rem; }
        .doc h3 { font-size: 1.1rem; }
    }
</style>
@endpush

@section('content')
<div class="doc">
    <div class="doc-hero">
        <div class="doc-hero-eyebrow"><i class="fas {{ $guide['icon'] }}"></i> Guide utilisateur OptimiZe</div>
        <h1 class="doc-hero-title">{{ $guide['title'] }}</h1>
        <p class="doc-hero-sub">{{ $guide['subtitle'] }}</p>
    </div>

    @include($body)
</div>
@endsection
