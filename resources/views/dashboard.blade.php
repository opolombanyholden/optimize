@extends('layouts.app')

@section('title', 'Portail Intranet')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">Accueil</li>
    </ol>
@endsection

@push('styles')
<style>
/* ============================================================
   PORTAIL INTRANET — OPTIMIZE
   ============================================================ */
:root {
    --finance-color:   #4F46E5; --finance-light:  #EEF2FF; --finance-grd:   linear-gradient(135deg,#4F46E5,#7C3AED);
    --rh-color:        #059669; --rh-light:       #ECFDF5; --rh-grd:        linear-gradient(135deg,#059669,#0891B2);
    --appro-color:     #D97706; --appro-light:    #FFFBEB; --appro-grd:     linear-gradient(135deg,#D97706,#DC2626);
    --mg-color:        #0891B2; --mg-light:       #ECFEFF; --mg-grd:        linear-gradient(135deg,#0891B2,#0284C7);
    --intranet-color:  #7C3AED; --intranet-light: #F5F3FF; --intranet-grd:  linear-gradient(135deg,#7C3AED,#DB2777);
}

/* ── HERO ─────────────────────────────────────────────────── */
.portal-hero {
    background:#fff; border-radius:20px; padding:1rem 1.25rem;
    margin-bottom:1.25rem;
    box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.04);
    position:relative; overflow:hidden;
}
.portal-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px;
    background:radial-gradient(circle,rgba(124,58,237,.08) 0%,transparent 70%);
    border-radius:50%; pointer-events:none;
}
.portal-hero-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem; flex-wrap:wrap; gap:1rem; }
.portal-greeting { font-size:1.6rem; font-weight:700; color:#0F172A; margin:0 0 .3rem; line-height:1.2; }
.portal-subtitle  { color:#64748B; font-size:.9rem; margin:0; }
.portal-badge-erp {
    display:inline-flex; align-items:center; gap:.45rem;
    background:linear-gradient(135deg,#7C3AED,#DB2777); color:#fff;
    font-size:.7rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
    padding:.3rem .75rem; border-radius:20px; margin-top:.5rem;
}
.portal-date-block  { text-align:right; }
.portal-date-day    { font-size:2.5rem; font-weight:800; color:#1E293B; line-height:1; }
.portal-date-month  { font-size:.85rem; font-weight:600; color:#7C3AED; text-transform:capitalize; }
.portal-date-weekday{ font-size:.75rem; color:#94A3B8; text-transform:capitalize; }

/* ── QUICK STATS BAR ───────────────────────────────────────── */
.portal-quick-stats { display:flex; align-items:center; background:#F8FAFC; border-radius:14px; padding:.9rem 1.5rem; flex-wrap:wrap; margin-top:1.25rem; }
.portal-qs-item     { display:flex; align-items:center; gap:.65rem; flex:1; min-width:140px; }
.portal-qs-icon     { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.95rem; flex-shrink:0; }
.portal-qs-val      { font-size:1.05rem; font-weight:700; color:#1E293B; display:block; line-height:1.1; }
.portal-qs-lbl      { font-size:.7rem; color:#94A3B8; display:block; text-transform:uppercase; letter-spacing:.04em; font-weight:500; }
.portal-qs-divider  { width:1px; height:36px; background:#E2E8F0; margin:0 1.25rem; flex-shrink:0; }
@media(max-width:768px) { .portal-qs-divider{display:none;} .portal-qs-item{min-width:45%; padding:.4rem 0;} }

/* ── Carousel actualités dans le hero ─────────────────────── */
.portal-hero-carousel { border-radius:14px; overflow:hidden; background:#0F172A; }
.portal-hero-slide { display:grid; grid-template-columns:minmax(0, 45%) 1fr; min-height:280px; align-items:stretch; }
@media(max-width:768px) { .portal-hero-slide { grid-template-columns:1fr; } }
.portal-hero-slide-img { min-height:220px; overflow:hidden; display:flex; }
.portal-hero-slide-img > img { width:100%; height:100%; object-fit:cover; display:block; }
.portal-hero-slide-fallback { display:flex; align-items:center; justify-content:center; color:#fff; opacity:.25; font-size:4rem; background:linear-gradient(135deg,#4F46E5,#7C3AED); }
.portal-hero-slide-body { padding:1.5rem 1.75rem; color:#F1F5F9; display:flex; flex-direction:column; justify-content:center; }
.portal-hero-slide-meta { font-size:.72rem; color:#94A3B8; text-transform:uppercase; letter-spacing:.05em; font-weight:600; margin-bottom:.5rem; }
.portal-hero-slide-title { font-size:1.35rem; font-weight:800; color:#fff; margin:0 0 .6rem; line-height:1.25; }
.portal-hero-slide-extrait { font-size:.88rem; color:#CBD5E1; margin:0 0 1rem; line-height:1.5; }
.portal-hero-slide-cta { display:inline-flex; align-items:center; align-self:flex-start; padding:.5rem 1rem; background:rgba(255,255,255,.12); color:#fff; border-radius:8px; font-size:.78rem; font-weight:600; transition:background .15s; }
.portal-hero-slide:hover .portal-hero-slide-cta { background:rgba(255,255,255,.22); }
.portal-hero-carousel .carousel-indicators { margin-bottom:.75rem; }
.portal-hero-carousel .carousel-indicators [data-bs-target] { width:24px; height:3px; border-radius:2px; background:rgba(255,255,255,.4); }
.portal-hero-carousel .carousel-indicators .active { background:#fff; }
.portal-hero-carousel .carousel-control-prev, .portal-hero-carousel .carousel-control-next { width:5%; opacity:.7; }
.portal-hero-empty { padding:2.5rem 1rem; text-align:center; background:#F8FAFC; color:#94A3B8; }

/* ── SECTION LABELS ────────────────────────────────────────── */
.section-label { font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#94A3B8; margin-bottom:.3rem; }
.section-title { font-size:1.1rem; font-weight:700; color:#1E293B; margin:0; }

/* ── INTRANET CONTENT CARDS ────────────────────────────────── */
.ic-card {
    background:#fff; border-radius:18px; border:1.5px solid #F1F5F9;
    box-shadow:0 1px 3px rgba(0,0,0,.04); overflow:hidden;
}
.ic-card-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:1rem 1.25rem; border-bottom:1px solid #F8FAFC;
}
.ic-card-header h6 { font-size:.9rem; font-weight:700; color:#1E293B; margin:0; display:flex; align-items:center; gap:.5rem; }
.ic-color-dot { width:8px; height:8px; border-radius:50%; display:inline-block; flex-shrink:0; }
.ic-view-all  { font-size:.75rem; font-weight:600; padding:.3rem .8rem; border-radius:8px; text-decoration:none; transition:background .15s; }

/* Annonce items */
.annonce-item { display:flex; gap:1rem; padding:.85rem 1.25rem; border-bottom:1px solid #F8FAFC; transition:background .15s; }
.annonce-item:last-child { border-bottom:none; }
.annonce-item:hover { background:#FAFBFF; }
.annonce-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; margin-top:5px; }
.annonce-title { font-size:.83rem; font-weight:600; color:#1E293B; margin:0 0 .2rem; }
.annonce-meta  { font-size:.72rem; color:#94A3B8; }
.annonce-urgent-badge { font-size:.62rem; font-weight:700; background:#FEE2E2; color:#DC2626; padding:.15rem .5rem; border-radius:8px; margin-left:.4rem; }

/* Événements items */
.event-item { display:flex; gap:.9rem; padding:.8rem 1.25rem; border-bottom:1px solid #F8FAFC; align-items:flex-start; }
.event-item:last-child { border-bottom:none; }
.event-date-block { text-align:center; min-width:42px; background:#F8FAFC; border-radius:10px; padding:.4rem .5rem; }
.event-date-day   { font-size:1.1rem; font-weight:800; color:#1E293B; line-height:1; }
.event-date-month { font-size:.6rem; font-weight:600; color:#94A3B8; text-transform:uppercase; }
.event-title { font-size:.83rem; font-weight:600; color:#1E293B; margin:0 0 .2rem; }
.event-meta  { font-size:.72rem; color:#94A3B8; }
.event-type-pill { font-size:.62rem; font-weight:600; padding:.15rem .5rem; border-radius:8px; display:inline-block; margin-top:.25rem; }

/* Projets & tâches */
.projet-item { padding:.85rem 1.25rem; border-bottom:1px solid #F8FAFC; }
.projet-item:last-child { border-bottom:none; }
.projet-name { font-size:.83rem; font-weight:600; color:#1E293B; margin:0 0 .35rem; }
.projet-progress { height:4px; border-radius:4px; background:#F1F5F9; overflow:hidden; margin:.35rem 0; }
.projet-progress-bar { height:100%; border-radius:4px; background:linear-gradient(90deg,#4F46E5,#7C3AED); }
.projet-meta { display:flex; justify-content:space-between; font-size:.7rem; color:#94A3B8; }
.statut-pill { font-size:.65rem; font-weight:600; padding:.2rem .6rem; border-radius:10px; }

/* ERP modules band ─────────────────────────────────────────── */
.erp-modules-band { display:grid; grid-template-columns:repeat(5,1fr); gap:.6rem; margin-bottom:2rem; }
@media(max-width:991px) { .erp-modules-band { grid-template-columns:repeat(3,1fr); } }
@media(max-width:575px) {
    .erp-modules-band { grid-template-columns:repeat(2,1fr); gap:.35rem; }
    .erp-modules-band > a:last-child { grid-column:span 2; }
}

.erp-module-card {
    border-radius:8px; border:1px solid transparent;
    padding:.55rem .75rem; text-decoration:none;
    display:flex; align-items:center; gap:.55rem;
    transition:filter .15s, box-shadow .15s;
}
.erp-module-card:hover { text-decoration:none; filter:brightness(.97); box-shadow:0 1px 2px rgba(15,23,42,.06); }
.erp-mod-icon { width:26px; height:26px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:.78rem; color:#fff; flex-shrink:0; }
.erp-mod-name { font-size:.78rem; font-weight:600; margin:0; line-height:1.15; }
.erp-mod-arrow { display:none; }

.erp-module-card.mod-finance   { background:var(--finance-light); border-color:rgba(79,70,229,.18); }
.erp-module-card.mod-finance   .erp-mod-icon { background:var(--finance-color); }
.erp-module-card.mod-finance   .erp-mod-name { color:var(--finance-color); }

.erp-module-card.mod-rh        { background:var(--rh-light); border-color:rgba(5,150,105,.18); }
.erp-module-card.mod-rh        .erp-mod-icon { background:var(--rh-color); }
.erp-module-card.mod-rh        .erp-mod-name { color:var(--rh-color); }

.erp-module-card.mod-appro     { background:var(--appro-light); border-color:rgba(217,119,6,.18); }
.erp-module-card.mod-appro     .erp-mod-icon { background:var(--appro-color); }
.erp-module-card.mod-appro     .erp-mod-name { color:var(--appro-color); }

.erp-module-card.mod-projet    { background:#F0FDFA; border-color:rgba(13,148,136,.18); }
.erp-module-card.mod-projet    .erp-mod-icon { background:#0D9488; }
.erp-module-card.mod-projet    .erp-mod-name { color:#0D9488; }

.erp-module-card.mod-objectifs { background:#FDF2F8; border-color:rgba(219,39,119,.18); }
.erp-module-card.mod-objectifs .erp-mod-icon { background:#DB2777; }
.erp-module-card.mod-objectifs .erp-mod-name { color:#DB2777; }

/* ── Section Achats & MG (design pro) ────────────────────── */
.lg-subgroup { padding:.85rem 1.15rem; }
.lg-subgroup-title { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94A3B8; margin-bottom:.65rem; display:flex; align-items:center; gap:.4rem; }
.lg-subgroup-title i { font-size:.75rem; color:#D97706; }
.lg-kpis { display:flex; flex-direction:column; gap:.4rem; }
.lg-kpi {
    display:flex; align-items:center; gap:.75rem;
    padding:.55rem .75rem; border-radius:8px; border:1px solid #F1F5F9;
    background:#FAFBFC; text-decoration:none; color:inherit;
    transition:background .15s, border-color .15s, transform .15s;
}
.lg-kpi:hover { background:#F8FAFC; border-color:#E2E8F0; transform:translateX(2px); text-decoration:none; color:inherit; }
.lg-kpi-icon { width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.9rem; color:#94A3B8; background:#F1F5F9; flex-shrink:0; }
.lg-kpi-body { flex:1; min-width:0; display:flex; align-items:baseline; gap:.6rem; }
.lg-kpi-val { font-size:1.35rem; font-weight:800; line-height:1; color:#334155; }
.lg-kpi-lbl { font-size:.78rem; color:#64748B; font-weight:500; }
.lg-kpi-arrow { font-size:.7rem; opacity:.3; transition:opacity .15s, transform .15s; color:#64748B; }
.lg-kpi:hover .lg-kpi-arrow { opacity:.9; transform:translateX(2px); }

.lg-kpi.is-alert { border-color:rgba(220,38,38,.25); background:#FEF2F2; }
.lg-kpi.is-alert .lg-kpi-icon { background:#FEE2E2; color:#DC2626; }
.lg-kpi.is-alert .lg-kpi-val  { color:#DC2626; }
.lg-kpi.is-warn  { border-color:rgba(217,119,6,.25); background:#FFFBEB; }
.lg-kpi.is-warn  .lg-kpi-icon { background:#FEF3C7; color:#D97706; }
.lg-kpi.is-warn  .lg-kpi-val  { color:#D97706; }
.lg-kpi.is-info  { border-color:rgba(79,70,229,.25); background:#EEF2FF; }
.lg-kpi.is-info  .lg-kpi-icon { background:#E0E7FF; color:#4F46E5; }
.lg-kpi.is-info  .lg-kpi-val  { color:#4F46E5; }
.lg-kpi.is-cyan  { border-color:rgba(8,145,178,.25); background:#ECFEFF; }
.lg-kpi.is-cyan  .lg-kpi-icon { background:#CFFAFE; color:#0891B2; }
.lg-kpi.is-cyan  .lg-kpi-val  { color:#0891B2; }

/* ── Anniversaires : avatar + bouton Souhaiter ──────────── */
.bday-item { display:flex; align-items:center; gap:.65rem; }
.bday-avatar { width:36px; height:36px; border-radius:50%; object-fit:cover; flex-shrink:0; border:2px solid #FCE7F3; }
.bday-avatar-fallback { display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#DB2777,#BE185D); color:#fff; font-size:.75rem; font-weight:700; letter-spacing:.02em; }
.bday-wish-btn {
    display:inline-flex; align-items:center; gap:.3rem;
    background:#FCE7F3; color:#DB2777; border:1px solid rgba(219,39,119,.25);
    padding:.3rem .65rem; border-radius:8px; font-size:.72rem; font-weight:600;
    transition:background .15s, transform .15s; flex-shrink:0;
}
.bday-wish-btn:hover { background:#FBCFE8; transform:scale(1.03); }
.bday-wish-btn span { line-height:1; }
.bday-sent { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:50%; background:#DCFCE7; color:#059669; font-size:.72rem; flex-shrink:0; }

/* ── Grille médias en pied de dashboard ─────────────────── */
.dash-media-grid { display:grid; grid-template-columns:repeat(6, 1fr); gap:.5rem; padding:1rem 1.25rem; }
@media(max-width:1200px) { .dash-media-grid { grid-template-columns:repeat(4, 1fr); } }
@media(max-width:768px)  { .dash-media-grid { grid-template-columns:repeat(3, 1fr); } }
@media(max-width:480px)  { .dash-media-grid { grid-template-columns:repeat(2, 1fr); } }
.dash-media-tile { position:relative; display:block; aspect-ratio:1/1; overflow:hidden; border-radius:8px; background:#F1F5F9; text-decoration:none; }
.dash-media-tile img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .25s; }
.dash-media-tile:hover img { transform:scale(1.06); }
.dash-media-fb { display:flex; align-items:center; justify-content:center; height:100%; color:#94A3B8; font-size:1.5rem; }
.dash-media-overlay { position:absolute; left:0; right:0; bottom:0; padding:.5rem .6rem; background:linear-gradient(to top, rgba(0,0,0,.75), rgba(0,0,0,0)); color:#fff; opacity:0; transition:opacity .2s; }
.dash-media-tile:hover .dash-media-overlay { opacity:1; }
.dash-media-title { font-size:.72rem; font-weight:700; line-height:1.15; }
.dash-media-meta { font-size:.62rem; opacity:.85; margin-top:.15rem; }
.dash-media-badge { position:absolute; top:.3rem; left:.3rem; font-size:.6rem; font-weight:700; padding:.15rem .4rem; border-radius:6px; }
.dash-media-badge.mine { background:#7C3AED; color:#fff; }
.dash-media-badge.pub  { background:rgba(255,255,255,.9); color:#059669; }
.dash-media-video { position:absolute; top:.3rem; right:.3rem; width:22px; height:22px; border-radius:50%; background:rgba(0,0,0,.65); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.55rem; }
</style>
@endpush

@section('content')
<div class="portal-wrapper">

{{-- ════════════════════════════════════════════════════════════
     HERO — Salutation + métriques intranet
════════════════════════════════════════════════════════════ --}}
<div class="portal-hero">
    <div class="portal-hero-top">
        <div>
            @php $hour = now()->hour; $greet = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir'); @endphp
            <h1 class="portal-greeting">{{ $greet }}, {{ auth()->user()->prenoms ?? auth()->user()->name }}</h1>
            <p class="portal-subtitle">Bienvenue sur votre espace intranet OptimiZe — <strong>{{ now()->translatedFormat('l d F Y') }}</strong></p>
            <div class="portal-badge-erp">
                <i class="fas fa-circle" style="font-size:.4rem;"></i>
                Portail Intranet · Système opérationnel
            </div>
        </div>
        <div class="portal-date-block d-none d-md-block">
            <div class="portal-date-day">{{ now()->format('d') }}</div>
            <div class="portal-date-month">{{ now()->translatedFormat('F Y') }}</div>
            <div class="portal-date-weekday">{{ now()->translatedFormat('l') }}</div>
        </div>
    </div>

    {{-- Carousel Actualités --}}
    @if($dernieresNews->count())
    <div id="heroNewsCarousel" class="carousel slide portal-hero-carousel" data-bs-ride="carousel" data-bs-interval="6000">
        <div class="carousel-indicators">
            @foreach($dernieresNews as $i => $news)
            <button type="button" data-bs-target="#heroNewsCarousel" data-bs-slide-to="{{ $i }}"
                    class="{{ $i === 0 ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
            @endforeach
        </div>
        <div class="carousel-inner">
            @foreach($dernieresNews as $i => $news)
            <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                <a href="{{ route('intranet.news.show', $news) }}" class="portal-hero-slide" style="text-decoration:none; color:inherit;">
                    @if($news->media_url && $news->media_principal_type === 'image' && preg_match('#^https?://[^\s"\'()<>]+$#', $news->media_url))
                        <div class="portal-hero-slide-img">
                            <img src="{{ $news->media_url }}" alt="" loading="lazy">
                        </div>
                    @else
                        <div class="portal-hero-slide-img portal-hero-slide-fallback"><i class="fas fa-newspaper"></i></div>
                    @endif
                    <div class="portal-hero-slide-body">
                        <div class="portal-hero-slide-meta">
                            <i class="fas fa-clock me-1"></i>{{ $news->created_at?->diffForHumans() }}
                            @if($news->auteur) · <i class="fas fa-user ms-1 me-1"></i>{{ $news->auteur->prenoms }} {{ $news->auteur->name }}@endif
                        </div>
                        <h2 class="portal-hero-slide-title">{{ $news->title }}</h2>
                        @if($news->extrait)
                        <p class="portal-hero-slide-extrait">{{ \Illuminate\Support\Str::limit(strip_tags($news->extrait), 180) }}</p>
                        @endif
                        <span class="portal-hero-slide-cta">Lire l'article <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @if($dernieresNews->count() > 1)
        <button class="carousel-control-prev" type="button" data-bs-target="#heroNewsCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroNewsCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
        @endif
    </div>
    @else
    <div class="portal-hero-carousel portal-hero-empty">
        <i class="fas fa-newspaper" style="font-size:2rem; opacity:.35;"></i>
        <p style="margin:.75rem 0 0; color:#94A3B8; font-size:.9rem;">Aucune actualité à afficher</p>
    </div>
    @endif

    {{-- Quick stats intranet --}}
    <div class="portal-quick-stats">
        <div class="portal-qs-item">
            <div class="portal-qs-icon" style="background:#F5F3FF; color:#7C3AED;"><i class="fas fa-bullhorn"></i></div>
            <div>
                <span class="portal-qs-val">{{ $totalAnnonces }}</span>
                <span class="portal-qs-lbl">Annonces</span>
            </div>
        </div>
        <div class="portal-qs-divider"></div>
        <div class="portal-qs-item">
            <div class="portal-qs-icon" style="background:#ECFEFF; color:#0891B2;"><i class="fas fa-calendar-days"></i></div>
            <div>
                <span class="portal-qs-val">{{ $evenementsAVenir }}</span>
                <span class="portal-qs-lbl">Événements à venir</span>
            </div>
        </div>
        <div class="portal-qs-divider"></div>
        <div class="portal-qs-item">
            <div class="portal-qs-icon" style="background:#EEF2FF; color:#4F46E5;"><i class="fas fa-diagram-project"></i></div>
            <div>
                <span class="portal-qs-val">{{ $projetsEnCours }}</span>
                <span class="portal-qs-lbl">Projets en cours</span>
            </div>
        </div>
        <div class="portal-qs-divider"></div>
        <div class="portal-qs-item">
            <div class="portal-qs-icon" style="background:{{ $tachesEnCours > 0 ? '#FFFBEB' : '#F8FAFC' }}; color:{{ $tachesEnCours > 0 ? '#D97706' : '#94A3B8' }};"><i class="fas fa-list-check"></i></div>
            <div>
                <span class="portal-qs-val" style="{{ $tachesEnCours > 0 ? 'color:#D97706' : '' }}">{{ $tachesEnCours }}</span>
                <span class="portal-qs-lbl">Tâches actives</span>
            </div>
        </div>
        <div class="portal-qs-divider"></div>
        <div class="portal-qs-item">
            <div class="portal-qs-icon" style="background:{{ $courriersEnAttente > 0 ? '#FEE2E2' : '#F8FAFC' }}; color:{{ $courriersEnAttente > 0 ? '#DC2626' : '#94A3B8' }};"><i class="fas fa-envelope"></i></div>
            <div>
                <span class="portal-qs-val" style="{{ $courriersEnAttente > 0 ? 'color:#DC2626' : '' }}">{{ $courriersEnAttente }}</span>
                <span class="portal-qs-lbl">Courriers en attente</span>
            </div>
        </div>
    </div>
</div>

{{-- Bande d'accès rapide ERP retirée — les modules restent accessibles via
     le sélecteur d'espace (icône grille) et via la sidebar. --}}

{{-- ════════════════════════════════════════════════════════════
     ROW 1 : Annonces + Agenda à venir
════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-3">

    {{-- Annonces & Actualités --}}
    <div class="col-12 col-xl-6">
        <div class="ic-card h-100">
            <div class="ic-card-header">
                <h6>
                    <span class="ic-color-dot" style="background:#7C3AED; box-shadow:0 0 5px #7C3AED;"></span>
                    Annonces internes
                    @if($annoncesUrgentes > 0)
                        <span class="annonce-urgent-badge"><i class="fas fa-circle-exclamation me-1"></i>{{ $annoncesUrgentes }} urgente(s)</span>
                    @endif
                </h6>
                <a href="{{ route('intranet.annonces.index') }}" class="ic-view-all" style="background:#F5F3FF; color:#7C3AED;">Tout voir <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            @forelse($dernieresAnnonces as $annonce)
            <div class="annonce-item">
                <span class="annonce-dot" style="background:{{ $annonce->is_urgent ? '#DC2626' : '#7C3AED' }};"></span>
                <div style="flex:1; min-width:0;">
                    <div class="annonce-title">
                        {{ $annonce->title }}
                        @if($annonce->is_urgent)
                            <span class="annonce-urgent-badge">Urgent</span>
                        @endif
                    </div>
                    <div class="annonce-meta">
                        <i class="fas fa-user me-1"></i>{{ $annonce->auteur?->name }}
                        &nbsp;·&nbsp;
                        <i class="fas fa-clock me-1"></i>{{ $annonce->created_at?->diffForHumans() }}
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-bullhorn"></i>
                <p>Aucune annonce publiée</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Agenda — événements à venir --}}
    <div class="col-12 col-xl-6">
        <div class="ic-card h-100">
            <div class="ic-card-header">
                <h6>
                    <span class="ic-color-dot" style="background:#0891B2; box-shadow:0 0 5px #0891B2;"></span>
                    Agenda — Événements à venir
                </h6>
                <a href="{{ route('intranet.evenements.index') }}" class="ic-view-all" style="background:#ECFEFF; color:#0891B2;">Tout voir <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            @forelse($prochainsEvenements as $evt)
            <div class="event-item">
                <div class="event-date-block">
                    <div class="event-date-day">{{ $evt->date_debut->format('d') }}</div>
                    <div class="event-date-month">{{ $evt->date_debut->translatedFormat('M') }}</div>
                </div>
                <div style="flex:1; min-width:0;">
                    <div class="event-title">{{ $evt->titre }}</div>
                    <div class="event-meta">
                        <i class="fas fa-clock me-1"></i>{{ $evt->date_debut->format('H:i') }} – {{ $evt->date_fin->format('H:i') }}
                        @if($evt->lieu)
                            &nbsp;·&nbsp;<i class="fas fa-location-dot me-1"></i>{{ $evt->lieu }}
                        @endif
                    </div>
                    @if($evt->type)
                    <span class="event-type-pill" style="background:{{ $evt->type->couleur }}22; color:{{ $evt->type->couleur }};">
                        {{ $evt->type->nom }}
                    </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-calendar-check"></i>
                <p>Aucun événement à venir</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     ROW 2 : Projets + Actualités
════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Projets & Tâches --}}
    <div class="col-12 col-xl-4">
        <div class="ic-card h-100">
            <div class="ic-card-header">
                <h6>
                    <span class="ic-color-dot" style="background:#4F46E5; box-shadow:0 0 5px #4F46E5;"></span>
                    Projets &amp; Tâches
                    <span style="font-size:.72rem; background:#EEF2FF; color:#4F46E5; padding:.2rem .6rem; border-radius:8px; font-weight:600;">
                        {{ $projetsEnCours }} en cours
                    </span>
                </h6>
                <a href="{{ route('intranet.projets.index') }}" class="ic-view-all" style="background:#EEF2FF; color:#4F46E5;">Tout voir <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            @forelse($derniersProjets as $projet)
            <div class="projet-item">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="projet-name">{{ $projet->nom }}</div>
                    @if($projet->statut)
                    <span class="statut-pill" style="background:{{ $projet->statut->couleur }}22; color:{{ $projet->statut->couleur }};">
                        {{ $projet->statut->libelle }}
                    </span>
                    @endif
                </div>
                <div class="projet-progress">
                    <div class="projet-progress-bar" style="width:{{ $projet->statut?->libelle === 'Terminé' ? '100' : ($projet->statut?->libelle === 'En cours' ? '45' : '10') }}%"></div>
                </div>
                <div class="projet-meta">
                    <span>
                        @if($projet->priorite)
                        <i class="fas fa-flag me-1" style="color:{{ $projet->priorite->couleur }}"></i>{{ $projet->priorite->libelle }}
                        @endif
                    </span>
                    <span>
                        @if($projet->date_fin)
                        <i class="fas fa-calendar-xmark me-1"></i>{{ $projet->date_fin->format('d/m/Y') }}
                        @endif
                    </span>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-diagram-project"></i>
                <p>Aucun projet enregistré</p>
            </div>
            @endforelse

            @if($tachesEnCours > 0 || $tachesUrgentes > 0)
            <div style="padding:.75rem 1.25rem; background:#FAFBFF; border-top:1px solid #F1F5F9; display:flex; gap:.75rem; align-items:center; flex-wrap:wrap;">
                <span style="font-size:.75rem; color:#64748B; font-weight:600;"><i class="fas fa-list-check me-1"></i>Tâches :</span>
                <span style="font-size:.72rem; background:#EEF2FF; color:#4F46E5; padding:.2rem .65rem; border-radius:8px; font-weight:600;">
                    {{ $tachesEnCours }} active(s)
                </span>
                @if($tachesUrgentes > 0)
                <span style="font-size:.72rem; background:#FEE2E2; color:#DC2626; padding:.2rem .65rem; border-radius:8px; font-weight:600;">
                    <i class="fas fa-circle-exclamation me-1"></i>{{ $tachesUrgentes }} urgente(s)
                </span>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Courrier --}}
    <div class="col-12 col-xl-4">
        <div class="ic-card h-100">
            <div class="ic-card-header">
                <h6>
                    <span class="ic-color-dot" style="background:#D97706; box-shadow:0 0 5px #D97706;"></span>
                    Courrier
                    @if($courriersEnAttente > 0)
                    <span style="font-size:.72rem; background:#FFFBEB; color:#D97706; padding:.2rem .6rem; border-radius:8px; font-weight:600;">
                        {{ $courriersEnAttente }} en attente
                    </span>
                    @endif
                </h6>
                <a href="{{ route('intranet.courriers.index') }}" class="ic-view-all" style="background:#FFFBEB; color:#D97706;">Tout voir <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            @forelse($derniersCourriers as $courrier)
            @php
                $couleurStatut = match($courrier->statut) {
                    'recu' => '#D97706',
                    'en_traitement' => '#4F46E5',
                    'traite' => '#059669',
                    'archive' => '#94A3B8',
                    default => '#64748B',
                };
                $libelleStatut = match($courrier->statut) {
                    'recu' => 'Reçu',
                    'en_traitement' => 'En traitement',
                    'traite' => 'Traité',
                    'archive' => 'Archivé',
                    default => ucfirst($courrier->statut),
                };
            @endphp
            <a href="{{ route('intranet.courriers.show', $courrier) }}" class="projet-item" style="display:block; text-decoration:none; color:inherit;">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="projet-name">
                        @if($courrier->urgent)<i class="fas fa-circle-exclamation me-1" style="color:#DC2626;"></i>@endif
                        {{ \Illuminate\Support\Str::limit($courrier->objet, 42) }}
                    </div>
                    <span class="statut-pill" style="background:{{ $couleurStatut }}22; color:{{ $couleurStatut }};">
                        {{ $libelleStatut }}
                    </span>
                </div>
                <div class="projet-progress">
                    <div class="projet-progress-bar" style="width:{{ $courrier->statut === 'traite' ? '100' : ($courrier->statut === 'en_traitement' ? '50' : '15') }}%; background:{{ $couleurStatut }};"></div>
                </div>
                <div class="projet-meta">
                    <span>
                        <i class="fas fa-user me-1"></i>{{ \Illuminate\Support\Str::limit($courrier->expediteur ?? '—', 22) }}
                    </span>
                    <span>
                        @if($courrier->est_en_retard)
                        <i class="fas fa-triangle-exclamation me-1" style="color:#DC2626;"></i><span style="color:#DC2626;">En retard</span>
                        @elseif($courrier->date_reception)
                        <i class="fas fa-calendar-day me-1"></i>{{ $courrier->date_reception->format('d/m/Y') }}
                        @endif
                    </span>
                </div>
            </a>
            @empty
            <div class="empty-state">
                <i class="fas fa-envelope-open-text"></i>
                <p>Aucun courrier en attente</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- CRM — Opportunités --}}
    <div class="col-12 col-xl-4">
        <div class="ic-card h-100">
            <div class="ic-card-header">
                <h6>
                    <span class="ic-color-dot" style="background:#0891B2; box-shadow:0 0 5px #0891B2;"></span>
                    CRM — Opportunités
                    <span style="font-size:.72rem; background:#ECFEFF; color:#0891B2; padding:.2rem .6rem; border-radius:8px; font-weight:600;">
                        {{ $totalOpportunitesOuvertes }} ouverte(s)
                    </span>
                </h6>
                <a href="{{ route('intranet.pipeline') }}" class="ic-view-all" style="background:#ECFEFF; color:#0891B2;">Pipeline <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            @forelse($topOpportunites as $opp)
            @php
                $couleurEtape = $opp->etape?->couleur ?? '#0891B2';
                $libelleEtape = $opp->etape?->libelle ?? $opp->etape?->nom ?? '—';
                $orga = $opp->organisation?->raison_sociale ?? $opp->organisation?->nom ?? null;
            @endphp
            <a href="{{ route('intranet.opportunites.show', $opp) }}" class="projet-item" style="display:block; text-decoration:none; color:inherit;">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="projet-name">{{ \Illuminate\Support\Str::limit($opp->titre, 42) }}</div>
                    <span class="statut-pill" style="background:{{ $couleurEtape }}22; color:{{ $couleurEtape }};">
                        {{ $libelleEtape }}
                    </span>
                </div>
                <div class="projet-progress">
                    <div class="projet-progress-bar" style="width:{{ (int) ($opp->probabilite ?? 0) }}%; background:{{ $couleurEtape }};"></div>
                </div>
                <div class="projet-meta">
                    <span>
                        @if($orga)<i class="fas fa-building me-1"></i>{{ \Illuminate\Support\Str::limit($orga, 22) }}@endif
                    </span>
                    <span style="font-weight:700; color:#0891B2;">
                        {{ number_format($opp->valeur ?? 0, 0, ',', ' ') }} {{ $opp->devise ?? 'XAF' }}
                    </span>
                </div>
            </a>
            @empty
            <div class="empty-state">
                <i class="fas fa-handshake"></i>
                <p>Aucune opportunité ouverte</p>
            </div>
            @endforelse

            <div style="padding:.75rem 1.25rem; background:#F0FDFF; border-top:1px solid #F1F5F9; display:flex; gap:.75rem; align-items:center; flex-wrap:wrap;">
                <span style="font-size:.75rem; color:#64748B; font-weight:600;"><i class="fas fa-chart-line me-1"></i>Pipeline :</span>
                <span style="font-size:.72rem; background:#EEF2FF; color:#4F46E5; padding:.2rem .65rem; border-radius:8px; font-weight:600;">
                    {{ number_format($valeurPipeline, 0, ',', ' ') }} XAF
                </span>
                @if($opportunitesGagneesMois > 0)
                <span style="font-size:.72rem; background:#ECFDF5; color:#059669; padding:.2rem .65rem; border-radius:8px; font-weight:600;">
                    <i class="fas fa-trophy me-1"></i>{{ $opportunitesGagneesMois }} gagnée(s) ce mois
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     ROW 3 : Achats & MG + Pilotage stratégique
════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-xl-6">
        {{-- Achats & Moyens Généraux --}}
        <div class="ic-card mb-3 lg-card">
            <div class="ic-card-header">
                <h6>
                    <span class="ic-color-dot" style="background:#D97706; box-shadow:0 0 5px #D97706;"></span>
                    Achats & Moyens Généraux
                </h6>
                <a href="{{ route('appro.dashboard') }}" class="ic-view-all" style="background:#FFFBEB; color:#D97706;">Dashboard <i class="fas fa-arrow-right ms-1"></i></a>
            </div>

            {{-- Sous-groupe : ACHATS --}}
            <div class="lg-subgroup">
                <div class="lg-subgroup-title"><i class="fas fa-cart-shopping"></i> Achats &amp; approvisionnement</div>
                <div class="lg-kpis">
                    <a href="{{ route('appro.commandes-internes.index') }}" class="lg-kpi {{ $demandesInternesEnAttente > 0 ? 'is-alert' : '' }}">
                        <div class="lg-kpi-icon"><i class="fas fa-clipboard-list"></i></div>
                        <div class="lg-kpi-body">
                            <div class="lg-kpi-val">{{ $demandesInternesEnAttente }}</div>
                            <div class="lg-kpi-lbl">Demandes en attente</div>
                        </div>
                        <i class="fas fa-chevron-right lg-kpi-arrow"></i>
                    </a>
                    <a href="{{ route('appro.commandes.index') }}" class="lg-kpi {{ ($commandesEnCours ?? 0) > 0 ? 'is-warn' : '' }}">
                        <div class="lg-kpi-icon"><i class="fas fa-cart-flatbed"></i></div>
                        <div class="lg-kpi-body">
                            <div class="lg-kpi-val">{{ $commandesEnCours ?? 0 }}</div>
                            <div class="lg-kpi-lbl">Commandes en cours</div>
                        </div>
                        <i class="fas fa-chevron-right lg-kpi-arrow"></i>
                    </a>
                </div>
            </div>

            {{-- Sous-groupe : MOYENS GÉNÉRAUX --}}
            <div class="lg-subgroup" style="border-top:1px solid #F1F5F9;">
                <div class="lg-subgroup-title"><i class="fas fa-wrench"></i> Moyens généraux</div>
                <div class="lg-kpis">
                    <a href="{{ route('mg.dysfonctionnements.index') }}" class="lg-kpi {{ $dysfonctionnementsSignales > 0 ? 'is-alert' : '' }}">
                        <div class="lg-kpi-icon"><i class="fas fa-triangle-exclamation"></i></div>
                        <div class="lg-kpi-body">
                            <div class="lg-kpi-val">{{ $dysfonctionnementsSignales }}</div>
                            <div class="lg-kpi-lbl">Tickets signalés</div>
                        </div>
                        <i class="fas fa-chevron-right lg-kpi-arrow"></i>
                    </a>
                    <a href="{{ route('mg.dysfonctionnements.index') }}" class="lg-kpi {{ $dysfonctionnementsEnTraitement > 0 ? 'is-info' : '' }}">
                        <div class="lg-kpi-icon"><i class="fas fa-hand-holding"></i></div>
                        <div class="lg-kpi-body">
                            <div class="lg-kpi-val">{{ $dysfonctionnementsEnTraitement }}</div>
                            <div class="lg-kpi-lbl">En traitement</div>
                        </div>
                        <i class="fas fa-chevron-right lg-kpi-arrow"></i>
                    </a>
                    <a href="{{ route('mg.interventions.index') }}" class="lg-kpi {{ $interventionsEnCours > 0 ? 'is-cyan' : '' }}">
                        <div class="lg-kpi-icon"><i class="fas fa-tools"></i></div>
                        <div class="lg-kpi-body">
                            <div class="lg-kpi-val">{{ $interventionsEnCours }}</div>
                            <div class="lg-kpi-lbl">Interventions en cours</div>
                        </div>
                        <i class="fas fa-chevron-right lg-kpi-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        {{-- Pilotage stratégique --}}
        <div class="ic-card h-100">
            <div class="ic-card-header">
                <h6>
                    <span class="ic-color-dot" style="background:#DB2777; box-shadow:0 0 5px #DB2777;"></span>
                    Pilotage stratégique
                </h6>
                <a href="{{ route('objectifs.dashboard') }}" class="ic-view-all" style="background:#FCE7F3; color:#DB2777;">Tableau <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            @forelse($topObjectifs as $obj)
            <a href="{{ route('objectifs.objectifs.show', $obj) }}" class="projet-item" style="display:block; text-decoration:none; color:inherit;">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="projet-name">
                        <i class="fas {{ $obj->icone_affichage }} me-1" style="color:{{ $obj->couleur_affichage }};font-size:.72rem;"></i>
                        {{ \Illuminate\Support\Str::limit($obj->titre, 42) }}
                    </div>
                    <span class="statut-pill" style="background:{{ $obj->couleur_affichage }}22; color:{{ $obj->couleur_affichage }};">
                        {{ $obj->progression }}%
                    </span>
                </div>
                <div class="projet-progress">
                    <div class="projet-progress-bar" style="width:{{ $obj->progression }}%; background:{{ $obj->couleur_affichage }};"></div>
                </div>
                <div class="projet-meta">
                    <span>
                        @if($obj->responsable)<i class="fas fa-user me-1"></i>{{ $obj->responsable->prenoms }} {{ $obj->responsable->name }}@endif
                    </span>
                    <span>
                        @if($obj->date_echeance ?? null)<i class="fas fa-calendar-xmark me-1"></i>{{ \Carbon\Carbon::parse($obj->date_echeance)->format('d/m/Y') }}@endif
                    </span>
                </div>
            </a>
            @empty
            <div class="empty-state">
                <i class="fas fa-bullseye"></i>
                <p>Aucun objectif actif</p>
            </div>
            @endforelse

            @if($kpisCritiques->count())
            <div style="padding:.75rem 1.25rem; background:#FEF2F2; border-top:1px solid #F1F5F9; display:flex; gap:.75rem; align-items:center; flex-wrap:wrap;">
                <span style="font-size:.75rem; color:#DC2626; font-weight:600;"><i class="fas fa-bell me-1"></i>KPI en alerte :</span>
                <a href="{{ route('objectifs.kpi.index') }}" style="font-size:.72rem; background:#FEE2E2; color:#DC2626; padding:.2rem .65rem; border-radius:8px; font-weight:600; text-decoration:none;">
                    {{ $kpisCritiques->count() }} → voir
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     ROW 4 : Médias récents (publics + de l'utilisateur)
════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="ic-card">
            <div class="ic-card-header">
                <h6>
                    <span class="ic-color-dot" style="background:#7C3AED; box-shadow:0 0 5px #7C3AED;"></span>
                    Derniers médias
                    <span style="font-size:.68rem; color:#94A3B8; font-weight:500; margin-left:.25rem;">publics + vos uploads</span>
                </h6>
                <a href="{{ route('intranet.mediatheque.index') }}" class="ic-view-all" style="background:#F5F3FF; color:#7C3AED;">Médiathèque <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            @if($derniersMedias->count())
            <div class="dash-media-grid">
                @foreach($derniersMedias as $media)
                @php
                    $vignette = $media->thumbnail_url
                        ?: $media->url_externe
                        ?: ($media->est_image ? $media->fichier_url : ($media->apercu_url ?? null));
                    $urlOk = $vignette && preg_match('#^https?://[^\s"\'()<>]+$#', $vignette);
                    $isMine = $user && $media->created_by === $user->id;
                @endphp
                <a href="{{ route('intranet.mediatheque.show', $media) }}" class="dash-media-tile" title="{{ $media->titre }}@if($media->auteur) · par {{ $media->auteur->prenoms }} {{ $media->auteur->name }}@endif">
                    @if($urlOk)
                        <img src="{{ $vignette }}" alt="" loading="lazy">
                    @else
                        <div class="dash-media-fb"><i class="fas fa-{{ $media->est_video ? 'film' : 'image' }}"></i></div>
                    @endif
                    <div class="dash-media-overlay">
                        <div class="dash-media-title">{{ \Illuminate\Support\Str::limit($media->titre, 30) }}</div>
                        <div class="dash-media-meta">
                            @if($media->auteur)<i class="fas fa-user me-1"></i>{{ $media->auteur->prenoms }} {{ $media->auteur->name }}@endif
                        </div>
                    </div>
                    @if($isMine)
                        <span class="dash-media-badge mine" title="Votre média">Vous</span>
                    @elseif($media->is_public)
                        <span class="dash-media-badge pub" title="Public"><i class="fas fa-globe"></i></span>
                    @endif
                    @if($media->est_video)
                        <span class="dash-media-video"><i class="fas fa-play"></i></span>
                    @endif
                </a>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-images"></i>
                <p>Aucun média public ni personnel pour l'instant</p>
            </div>
            @endif
        </div>
    </div>
</div>


</div>


@endsection
