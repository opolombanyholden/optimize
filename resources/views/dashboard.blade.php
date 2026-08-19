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
    background:#fff; border-radius:20px; padding:2rem 2.5rem;
    margin-bottom:1.75rem;
    box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.04);
    position:relative; overflow:hidden;
}
.portal-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px;
    background:radial-gradient(circle,rgba(124,58,237,.08) 0%,transparent 70%);
    border-radius:50%; pointer-events:none;
}
.portal-hero-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem; }
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
.portal-quick-stats { display:flex; align-items:center; background:#F8FAFC; border-radius:14px; padding:.9rem 1.5rem; flex-wrap:wrap; }
.portal-qs-item     { display:flex; align-items:center; gap:.65rem; flex:1; min-width:140px; }
.portal-qs-icon     { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.95rem; flex-shrink:0; }
.portal-qs-val      { font-size:1.05rem; font-weight:700; color:#1E293B; display:block; line-height:1.1; }
.portal-qs-lbl      { font-size:.7rem; color:#94A3B8; display:block; text-transform:uppercase; letter-spacing:.04em; font-weight:500; }
.portal-qs-divider  { width:1px; height:36px; background:#E2E8F0; margin:0 1.25rem; flex-shrink:0; }
@media(max-width:768px) { .portal-qs-divider{display:none;} .portal-qs-item{min-width:45%; padding:.4rem 0;} }

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
.erp-modules-band { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
@media(max-width:991px) { .erp-modules-band { grid-template-columns:repeat(2,1fr); } }
@media(max-width:575px) { .erp-modules-band { grid-template-columns:1fr; } }

.erp-module-card {
    background:#fff; border-radius:16px; border:1.5px solid #F1F5F9;
    padding:1.25rem; text-decoration:none; display:block; position:relative; overflow:hidden;
    transition:transform .2s, box-shadow .2s, border-color .2s;
}
.erp-module-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:16px 16px 0 0; }
.erp-module-card:hover { transform:translateY(-3px); text-decoration:none; }
.erp-mod-icon { width:40px; height:40px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1rem; color:#fff; margin-bottom:.8rem; }
.erp-mod-name { font-size:.88rem; font-weight:700; color:#1E293B; margin:0 0 .5rem; }
.erp-mod-stats { display:flex; gap:.5rem; flex-wrap:wrap; }
.erp-mod-stat  { font-size:.72rem; padding:.2rem .6rem; border-radius:8px; font-weight:600; }
.erp-mod-arrow { position:absolute; top:1rem; right:1rem; font-size:.75rem; opacity:.3; transition:opacity .2s, transform .2s; }
.erp-module-card:hover .erp-mod-arrow { opacity:1; transform:translateX(2px); }

.erp-module-card.mod-finance::before { background:var(--finance-grd); }
.erp-module-card.mod-finance:hover { border-color:rgba(79,70,229,.25); box-shadow:0 8px 24px rgba(79,70,229,.12); }
.erp-module-card.mod-finance .erp-mod-icon { background:var(--finance-grd); }
.erp-module-card.mod-finance .erp-mod-stat { background:var(--finance-light); color:var(--finance-color); }
.erp-module-card.mod-finance .erp-mod-arrow { color:var(--finance-color); }

.erp-module-card.mod-rh::before { background:var(--rh-grd); }
.erp-module-card.mod-rh:hover { border-color:rgba(5,150,105,.25); box-shadow:0 8px 24px rgba(5,150,105,.12); }
.erp-module-card.mod-rh .erp-mod-icon { background:var(--rh-grd); }
.erp-module-card.mod-rh .erp-mod-stat { background:var(--rh-light); color:var(--rh-color); }
.erp-module-card.mod-rh .erp-mod-arrow { color:var(--rh-color); }

.erp-module-card.mod-appro::before { background:var(--appro-grd); }
.erp-module-card.mod-appro:hover { border-color:rgba(217,119,6,.25); box-shadow:0 8px 24px rgba(217,119,6,.12); }
.erp-module-card.mod-appro .erp-mod-icon { background:var(--appro-grd); }
.erp-module-card.mod-appro .erp-mod-stat { background:var(--appro-light); color:var(--appro-color); }
.erp-module-card.mod-appro .erp-mod-arrow { color:var(--appro-color); }

.erp-module-card.mod-mg::before { background:var(--mg-grd); }
.erp-module-card.mod-mg:hover { border-color:rgba(8,145,178,.25); box-shadow:0 8px 24px rgba(8,145,178,.12); }
.erp-module-card.mod-mg .erp-mod-icon { background:var(--mg-grd); }
.erp-module-card.mod-mg .erp-mod-stat { background:var(--mg-light); color:var(--mg-color); }
.erp-module-card.mod-mg .erp-mod-arrow { color:var(--mg-color); }
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

    {{-- Projets en cours --}}
    <div class="col-12 col-xl-7">
        <div class="ic-card">
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

    {{-- Actualités + Courriers --}}
    <div class="col-12 col-xl-5">

        {{-- Actualités --}}
        <div class="ic-card mb-3">
            <div class="ic-card-header">
                <h6>
                    <span class="ic-color-dot" style="background:#059669; box-shadow:0 0 5px #059669;"></span>
                    Actualités
                </h6>
                <a href="{{ route('intranet.news.index') }}" class="ic-view-all" style="background:#ECFDF5; color:#059669;">Voir tout <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            @forelse($dernieresNews as $news)
            <div class="annonce-item">
                <span class="annonce-dot" style="background:#059669;"></span>
                <div style="flex:1; min-width:0;">
                    <div class="annonce-title">{{ $news->title }}</div>
                    <div class="annonce-meta">
                        <i class="fas fa-clock me-1"></i>{{ $news->created_at?->diffForHumans() }}
                    </div>
                </div>
            </div>
            @empty
            <div style="padding:1.5rem; text-align:center; color:#94A3B8; font-size:.82rem;">
                <i class="fas fa-newspaper d-block mb-2" style="font-size:1.5rem;"></i>
                Aucune actualité
            </div>
            @endforelse
        </div>

        {{-- Courrier --}}
        <div class="ic-card">
            <div class="ic-card-header">
                <h6>
                    <span class="ic-color-dot" style="background:#D97706;"></span>
                    Courrier
                    @if($courriersEnAttente > 0)
                    <span style="font-size:.65rem; background:#FEE2E2; color:#DC2626; padding:.15rem .5rem; border-radius:8px; font-weight:700;">
                        {{ $courriersEnAttente }} en attente
                    </span>
                    @endif
                </h6>
                <a href="{{ route('intranet.courriers.index') }}" class="ic-view-all" style="background:#FFFBEB; color:#D97706;">Voir tout <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div style="padding:1.1rem 1.25rem; display:flex; gap:1.25rem; justify-content:space-around;">
                <div style="text-align:center;">
                    <div style="font-size:1.3rem; font-weight:800; color:#4F46E5;">—</div>
                    <div style="font-size:.7rem; color:#94A3B8; text-transform:uppercase; letter-spacing:.04em; font-weight:600;">Entrant</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:1.3rem; font-weight:800; color:#059669;">—</div>
                    <div style="font-size:.7rem; color:#94A3B8; text-transform:uppercase; letter-spacing:.04em; font-weight:600;">Sortant</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:1.3rem; font-weight:800; color:#D97706; @if($courriersEnAttente > 0) color:#DC2626 !important; @endif">{{ $courriersEnAttente }}</div>
                    <div style="font-size:.7rem; color:#94A3B8; text-transform:uppercase; letter-spacing:.04em; font-weight:600;">En attente</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:1.3rem; font-weight:800; color:#94A3B8;">—</div>
                    <div style="font-size:.7rem; color:#94A3B8; text-transform:uppercase; letter-spacing:.04em; font-weight:600;">Documents</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     ACCÈS RAPIDE ERP — Bande modules
════════════════════════════════════════════════════════════ --}}
<div class="mb-2">
    <div class="section-label">Modules ERP</div>
    <div class="d-flex justify-content-between align-items-end mb-2">
        <div class="section-title">Accès rapide</div>
        <span style="font-size:.75rem; color:#94A3B8;">Cliquez pour accéder au module</span>
    </div>
</div>
<div class="erp-modules-band">

    @canany(['read:exercice','read:budget','read:grandlivre','read:compte'])
    <a href="{{ route('finance.exercices.index') }}" class="erp-module-card mod-finance">
        <i class="fas fa-chevron-right erp-mod-arrow"></i>
        <div class="erp-mod-icon"><i class="fas fa-coins"></i></div>
        <div class="erp-mod-name">Finance & Budget</div>
        <div class="erp-mod-stats">
            <span class="erp-mod-stat">{{ number_format($budgetGlobal, 0, ',', ' ') }} F</span>
            <span class="erp-mod-stat">{{ $exerciceActif?->libelle ?? '—' }}</span>
        </div>
    </a>
    @endcanany

    @canany(['read:employee','read:absence','read:paie','read:recrutement'])
    <a href="{{ route('rh.employees.index') }}" class="erp-module-card mod-rh">
        <i class="fas fa-chevron-right erp-mod-arrow"></i>
        <div class="erp-mod-icon"><i class="fas fa-users"></i></div>
        <div class="erp-mod-name">RH & Paiement</div>
        <div class="erp-mod-stats">
            <span class="erp-mod-stat">{{ $totalEmployees }} employés</span>
            @if($absencesEnCours > 0)
            <span class="erp-mod-stat" style="background:#FEF3C7; color:#D97706;">{{ $absencesEnCours }} absences</span>
            @endif
        </div>
    </a>
    @endcanany

    @canany(['read:fournisseur','read:produit','read:commande','read:immobilisation','read:dysfonctionnement','read:intervention'])
    <a href="{{ route('appro.commandes.index') }}" class="erp-module-card mod-appro">
        <i class="fas fa-chevron-right erp-mod-arrow"></i>
        <div class="erp-mod-icon"><i class="fas fa-cart-flatbed"></i></div>
        <div class="erp-mod-name">Achats & Moyens Généraux</div>
        <div class="erp-mod-stats">
            <span class="erp-mod-stat">{{ $commandesEnCours ?? 0 }} commandes</span>
            @if(($dysfonctionnementsOuverts ?? 0) > 0)
                <span class="erp-mod-stat" style="background:#FEE2E2; color:#DC2626;">{{ $dysfonctionnementsOuverts }} tickets</span>
            @endif
        </div>
    </a>
    @endcanany

    {{-- Projets / PMP --}}
    <a href="{{ route('projet.dashboard') }}" class="erp-module-card mod-projet" style="--mod-color:#0D9488;--mod-grd:linear-gradient(135deg,#0D9488,#0F766E);">
        <i class="fas fa-chevron-right erp-mod-arrow"></i>
        <div class="erp-mod-icon"><i class="fas fa-diagram-project"></i></div>
        <div class="erp-mod-name">Projets / Tâches</div>
        <div class="erp-mod-stats">
            <span class="erp-mod-stat">{{ $totalProjets }} projets</span>
            @if($projetsEnCours > 0)<span class="erp-mod-stat">{{ $projetsEnCours }} en cours</span>@endif
        </div>
    </a>

    {{-- Objectifs & KPI --}}
    <a href="{{ route('objectifs.dashboard') }}" class="erp-module-card mod-objectifs" style="--mod-color:#DB2777;--mod-grd:linear-gradient(135deg,#DB2777,#BE185D);">
        <i class="fas fa-chevron-right erp-mod-arrow"></i>
        <div class="erp-mod-icon"><i class="fas fa-bullseye"></i></div>
        <div class="erp-mod-name">Objectifs & KPI</div>
        <div class="erp-mod-stats">
            <span class="erp-mod-stat">{{ $objectifsActifs }} actifs</span>
            <span class="erp-mod-stat">{{ $kpiTotal }} KPI</span>
        </div>
    </a>

</div>

{{-- ════════════════════════════════════════════════════════════════
     ANNUAIRE & OBJECTIFS — Section transverse
═════════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mt-3">
    {{-- Annuaire --}}
    <div class="col-md-6">
        <div class="contact-detail-card h-100">
            <h4 class="contact-detail-card-title">
                <i class="fas fa-address-book" style="color:#0891B2;"></i> Annuaire
                <a href="{{ route('intranet.annuaire.collaborateurs.index') }}" class="ms-auto" style="font-size:.72rem;color:#0891B2;text-decoration:none;font-weight:600;">Voir tout →</a>
            </h4>
            <div class="row g-2">
                <div class="col-4">
                    <a href="{{ route('intranet.annuaire.collaborateurs.index') }}" style="text-decoration:none;color:inherit;">
                        <div style="background:#ECFEFF;padding:.85rem;border-radius:8px;text-align:center;">
                            <div style="font-size:1.6rem;font-weight:800;color:#0891B2;">{{ $totalCollaborateurs }}</div>
                            <div style="font-size:.7rem;color:#64748B;"><i class="fas fa-users me-1"></i>Collaborateurs</div>
                        </div>
                    </a>
                </div>
                <div class="col-4">
                    <a href="{{ route('intranet.annuaire.services.index') }}" style="text-decoration:none;color:inherit;">
                        <div style="background:#F0FDFA;padding:.85rem;border-radius:8px;text-align:center;">
                            <div style="font-size:1.6rem;font-weight:800;color:#0D9488;">{{ $totalServices }}</div>
                            <div style="font-size:.7rem;color:#64748B;"><i class="fas fa-building me-1"></i>Services</div>
                        </div>
                    </a>
                </div>
                <div class="col-4">
                    <a href="{{ route('intranet.annuaire.equipes.index') }}" style="text-decoration:none;color:inherit;">
                        <div style="background:#F5F3FF;padding:.85rem;border-radius:8px;text-align:center;">
                            <div style="font-size:1.6rem;font-weight:800;color:#7C3AED;">{{ $totalEquipes }}</div>
                            <div style="font-size:.7rem;color:#64748B;"><i class="fas fa-people-group me-1"></i>Équipes</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Objectifs & KPI --}}
    <div class="col-md-6">
        <div class="contact-detail-card h-100">
            <h4 class="contact-detail-card-title">
                <i class="fas fa-bullseye" style="color:#DB2777;"></i> Pilotage stratégique
                <a href="{{ route('objectifs.dashboard') }}" class="ms-auto" style="font-size:.72rem;color:#DB2777;text-decoration:none;font-weight:600;">Tableau de bord →</a>
            </h4>

            @if($topObjectifs->count())
            <div class="d-flex flex-column gap-2 mb-2">
                @foreach($topObjectifs as $obj)
                <a href="{{ route('objectifs.objectifs.show', $obj) }}" style="text-decoration:none;color:inherit;">
                    <div style="background:#F8FAFC;border-radius:8px;padding:.55rem .7rem;border-left:3px solid {{ $obj->couleur_affichage }};">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fas {{ $obj->icone_affichage }}" style="color:{{ $obj->couleur_affichage }};font-size:.75rem;"></i>
                            <strong style="font-size:.78rem;flex:1;">{{ Str::limit($obj->titre, 38) }}</strong>
                            <span style="font-size:.7rem;font-weight:700;color:{{ $obj->couleur_affichage }};">{{ $obj->progression }}%</span>
                        </div>
                        <div class="tache-progress-bar" style="height:5px;">
                            <div class="tache-progress-fill" style="width:{{ $obj->progression }}%;background:{{ $obj->couleur_affichage }};"></div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

            @if($kpisCritiques->count())
            <div style="font-size:.7rem;color:#DC2626;font-weight:600;margin-top:.5rem;">
                <i class="fas fa-bell me-1"></i> {{ $kpisCritiques->count() }} KPI en alerte
                <a href="{{ route('objectifs.kpi.index') }}" style="color:#DC2626;text-decoration:underline;">→ voir</a>
            </div>
            @endif
        </div>
    </div>
</div>

</div>
@endsection
