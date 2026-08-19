<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cfg['site_titre'] ?? 'OptimiZe — La solution ERP intégrée' }}</title>
    <meta name="description" content="{{ $cfg['site_description'] ?? 'OptimiZe centralise Finance, RH, Achats, Projets et Objectifs dans une seule plateforme moderne et sécurisée.' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
    <link href="{{ asset('css/vitrine.css') }}" rel="stylesheet">
</head>
<body>

@php
    $contactEmail   = $cfg['contact_email'] ?? 'contact@yubile-tech.com';
    $marqueNom      = $cfg['marque_nom'] ?? 'OptimiZe';
    $marqueSlogan   = $cfg['marque_slogan'] ?? 'La solution ERP intégrée pour les organisations africaines ambitieuses.';
    $logosBarTitre  = $cfg['logos_titre'] ?? 'Conçu pour les organisations africaines ambitieuses';
    $partenaires    = json_decode($cfg['partenaires'] ?? '[]', true) ?: [];
    $sectionModulesTitre    = $cfg['modules_titre'] ?? 'Tout ce qu\'il faut pour piloter votre organisation';
    $sectionModulesSubtitle = $cfg['modules_subtitle'] ?? 'De la finance à la stratégie, OptimiZe couvre tous les besoins métier.';
    $sectionCapturesTitre    = $cfg['captures_titre'] ?? 'Une interface moderne, conçue pour la productivité';
    $sectionCapturesSubtitle = $cfg['captures_subtitle'] ?? 'Découvrez les écrans clés de la plateforme.';
    $sectionAtoutsTitre      = $cfg['atouts_titre'] ?? 'Conçu pour les entreprises africaines exigeantes';
    $sectionAtoutsSubtitle   = $cfg['atouts_subtitle'] ?? 'Une solution moderne combinant standards internationaux et compréhension fine du contexte local.';
    $ctaTitre   = $cfg['cta_titre'] ?? 'Prêt à transformer votre organisation ?';
    $ctaSubtitle = $cfg['cta_subtitle'] ?? 'Demandez une démo personnalisée et découvrez comment OptimiZe peut accélérer votre croissance.';
@endphp

{{-- NAVBAR --}}
<nav class="vit-navbar" id="vitNavbar">
    <div class="vit-nav-container">
        <a href="#" class="vit-logo">
            <span class="vit-logo-icon"><i class="fas fa-cube"></i></span>
            {{ $marqueNom }}
        </a>
        <ul class="vit-nav-links">
            <li><a href="#modules">Modules</a></li>
            <li><a href="#captures">Captures</a></li>
            <li><a href="#avantages">Avantages</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <div class="vit-nav-cta">
            <a href="{{ route('login') }}" class="vit-btn vit-btn-ghost">Connexion</a>
            <a href="#contact" class="vit-btn vit-btn-primary">Demander une démo</a>
        </div>
    </div>
</nav>

{{-- HERO ANIMÉ --}}
<section class="vit-hero">
    <div class="swiper hero-swiper">
        <div class="swiper-wrapper">
            @forelse($slides as $slide)
            <div class="swiper-slide hero-slide">
                <div class="hero-slide-container">
                    <div class="hero-content">
                        @if($slide->eyebrow)
                        <div class="hero-eyebrow" @if($slide->eyebrow_couleur) style="background:{{ $slide->eyebrow_couleur }}1a;color:{{ $slide->eyebrow_couleur }};border-color:{{ $slide->eyebrow_couleur }}33;" @endif>
                            @if($slide->eyebrow_icone)<i class="fas {{ $slide->eyebrow_icone }}"></i>@endif {{ $slide->eyebrow }}
                        </div>
                        @endif
                        <h1 class="hero-title">{!! $slide->titre !!}</h1>
                        @if($slide->sous_titre)<p class="hero-subtitle">{{ $slide->sous_titre }}</p>@endif
                        <div class="hero-actions">
                            <a href="{{ $slide->cta_url ?: '#contact' }}" class="vit-btn vit-btn-primary vit-btn-lg">{{ $slide->cta_texte ?: 'Demander une démo' }} <i class="fas fa-arrow-right"></i></a>
                            <a href="#modules" class="vit-btn vit-btn-ghost vit-btn-lg">Découvrir les modules</a>
                        </div>
                        @if(!empty($slide->stats))
                        <div class="hero-stats">
                            @foreach($slide->stats as $stat)
                            <div><div class="hero-stat-num">{{ $stat['num'] ?? '' }}</div><div class="hero-stat-label">{{ $stat['label'] ?? '' }}</div></div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="hero-visual">
                        <div class="hero-mockup hero-mockup-floating">
                            <div class="mockup-titlebar">
                                <span class="mockup-dot mockup-dot-r"></span>
                                <span class="mockup-dot mockup-dot-y"></span>
                                <span class="mockup-dot mockup-dot-g"></span>
                                <span class="mockup-url">optimize.local{{ $slide->mockup_type ? '/'.$slide->mockup_type : '' }}</span>
                            </div>
                            <div class="mockup-body" @if($slide->image_url) style="padding:0;min-height:380px;background:#FFF;" @else class="mock-screen" @endif>
                                @if($slide->image_url)
                                <img src="{{ $slide->image_url }}" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
                                @elseif($slide->mockup_type && view()->exists('vitrine.mockups.'.$slide->mockup_type))
                                @include('vitrine.mockups.'.$slide->mockup_type)
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="swiper-slide hero-slide">
                <div class="hero-slide-container">
                    <div class="hero-content">
                        <h1 class="hero-title">{{ $marqueNom }}</h1>
                        <p class="hero-subtitle">Aucun slide configuré. Allez sur <code>/admin/vitrine</code> pour gérer le contenu.</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>
        <div class="swiper-pagination" style="bottom:2rem !important;"></div>
    </div>
</section>

{{-- LOGOS BAR --}}
@if(!empty($partenaires))
<div class="logos-bar">
    <div class="vit-section-container">
        <div style="text-align:center;font-size:.78rem;color:var(--c-text-light);margin-bottom:1.5rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">{{ $logosBarTitre }}</div>
        <div class="logos-row">
            @foreach($partenaires as $p)
            <span>@if(!empty($p['icone']))<i class="fas {{ $p['icone'] }} me-2"></i>@endif{{ $p['nom'] ?? '' }}</span>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- MODULES --}}
@if($modules->count())
<section class="vit-section" id="modules">
    <div class="vit-section-container">
        <div class="vit-section-header">
            <span class="vit-section-eyebrow">Une plateforme, {{ $modules->count() }} modules</span>
            <h2 class="vit-section-title">{{ $sectionModulesTitre }}</h2>
            <p class="vit-section-subtitle">{{ $sectionModulesSubtitle }}</p>
        </div>
        <div class="modules-grid">
            @foreach($modules as $m)
            <div class="module-card" style="--mod-color:{{ $m->couleur }};">
                <div class="module-icon"><i class="fas {{ $m->icone }}"></i></div>
                <h3 class="module-name">{{ $m->nom }}</h3>
                @if($m->description)<p class="module-desc">{{ $m->description }}</p>@endif
                @if(!empty($m->features))
                <ul class="module-features">
                    @foreach($m->features as $f)
                    <li><i class="fas fa-check"></i> {{ $f }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CAPTURES --}}
@if($captures->count())
<section class="vit-section screenshots-section" id="captures">
    <div class="vit-section-container">
        <div class="vit-section-header">
            <span class="vit-section-eyebrow">Aperçu de l'interface</span>
            <h2 class="vit-section-title">{{ $sectionCapturesTitre }}</h2>
            <p class="vit-section-subtitle">{{ $sectionCapturesSubtitle }}</p>
        </div>
        <div class="swiper screenshots-swiper">
            <div class="swiper-wrapper">
                @foreach($captures as $cap)
                <div class="swiper-slide">
                    <div class="screenshot-card">
                        <div class="mockup-titlebar">
                            <span class="mockup-dot mockup-dot-r"></span>
                            <span class="mockup-dot mockup-dot-y"></span>
                            <span class="mockup-dot mockup-dot-g"></span>
                            <span class="mockup-url">{{ $cap->url_affichee ?: '/' }}</span>
                        </div>
                        @if($cap->image_url)
                        <img src="{{ $cap->image_url }}" alt="" style="width:100%;height:300px;object-fit:cover;display:block;">
                        @elseif($cap->mockup_type && view()->exists('vitrine.mockups.'.$cap->mockup_type))
                        <div class="mock-screen" style="min-height:300px;">@include('vitrine.mockups.'.$cap->mockup_type)</div>
                        @else
                        <div style="background:#F1F5F9;height:300px;display:flex;align-items:center;justify-content:center;color:#94A3B8;">📷 Aperçu</div>
                        @endif
                        <div class="screenshot-meta">
                            @if($cap->tag)<span class="screenshot-tag" @if($cap->tag_couleur) style="background:{{ $cap->tag_couleur }}1a;color:{{ $cap->tag_couleur }};" @endif>{{ $cap->tag }}</span>@endif
                            <div class="screenshot-title">{{ $cap->titre }}</div>
                            @if($cap->description)<div class="screenshot-desc">{{ $cap->description }}</div>@endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="swiper-pagination" style="position:relative;margin-top:2rem;"></div>
        </div>
    </div>
</section>
@endif

{{-- ATOUTS --}}
@if($atouts->count())
<section class="vit-section" id="avantages" style="background:#FFF;">
    <div class="vit-section-container">
        <div class="vit-section-header">
            <span class="vit-section-eyebrow">Pourquoi {{ $marqueNom }} ?</span>
            <h2 class="vit-section-title">{{ $sectionAtoutsTitre }}</h2>
            <p class="vit-section-subtitle">{{ $sectionAtoutsSubtitle }}</p>
        </div>
        <div class="why-grid">
            @foreach($atouts as $a)
            <div class="why-card">
                <div class="why-icon" style="background:linear-gradient(135deg,{{ $a->gradient_from }},{{ $a->gradient_to }});"><i class="fas {{ $a->icone }}"></i></div>
                <h3 class="why-title">{{ $a->titre }}</h3>
                @if($a->description)<p class="why-desc">{{ $a->description }}</p>@endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CTA --}}
<section class="vit-cta" id="contact">
    <div class="vit-cta-content">
        <h2>{{ $ctaTitre }}</h2>
        <p>{{ $ctaSubtitle }}</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="mailto:{{ $contactEmail }}?subject=Demande de démo {{ $marqueNom }}" class="vit-btn vit-btn-white vit-btn-lg">
                <i class="fas fa-envelope"></i> {{ $contactEmail }}
            </a>
            <a href="{{ route('login') }}" class="vit-btn vit-btn-lg" style="background:rgba(255,255,255,0.15);color:#FFF;border:1px solid rgba(255,255,255,0.3);">
                <i class="fas fa-right-to-bracket"></i> Accéder à la plateforme
            </a>
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer class="vit-footer">
    <div class="vit-section-container">
        <div class="vit-footer-grid">
            <div class="vit-footer-col vit-footer-brand">
                <a href="#" class="vit-logo" style="color:#FFF;">
                    <span class="vit-logo-icon"><i class="fas fa-cube"></i></span>
                    {{ $marqueNom }}
                </a>
                <p>{{ $marqueSlogan }}</p>
            </div>
            <div class="vit-footer-col">
                <h4>Produit</h4>
                <ul>
                    <li><a href="#modules">Modules</a></li>
                    <li><a href="#captures">Captures</a></li>
                    <li><a href="#avantages">Avantages</a></li>
                    <li><a href="{{ route('login') }}">Se connecter</a></li>
                </ul>
            </div>
            <div class="vit-footer-col">
                <h4>Solutions</h4>
                <ul>
                    @foreach($modules->take(4) as $m)
                    <li><a href="#modules">{{ $m->nom }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="vit-footer-col">
                <h4>Contact</h4>
                <ul>
                    <li><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></li>
                    @if(!empty($cfg['contact_adresse']))<li><span style="color:#94A3B8;">{{ $cfg['contact_adresse'] }}</span></li>@endif
                    <li><a href="#contact">Demander une démo</a></li>
                </ul>
            </div>
        </div>
        <div class="vit-footer-bottom">
            <div>© {{ date('Y') }} {{ $cfg['copyright_owner'] ?? 'Yubile Technologie' }}. Tous droits réservés.</div>
            <div style="display:flex;gap:1rem;">
                @if(!empty($cfg['social_linkedin']))<a href="{{ $cfg['social_linkedin'] }}" style="color:#94A3B8;"><i class="fab fa-linkedin"></i></a>@endif
                @if(!empty($cfg['social_twitter']))<a href="{{ $cfg['social_twitter'] }}" style="color:#94A3B8;"><i class="fab fa-twitter"></i></a>@endif
                @if(!empty($cfg['social_facebook']))<a href="{{ $cfg['social_facebook'] }}" style="color:#94A3B8;"><i class="fab fa-facebook"></i></a>@endif
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    new Swiper('.hero-swiper', {
        loop: true,
        autoplay: { delay: 6000, disableOnInteraction: false },
        effect: 'fade', fadeEffect: { crossFade: true }, speed: 1000,
        pagination: { el: '.hero-swiper .swiper-pagination', clickable: true },
    });
    new Swiper('.screenshots-swiper', {
        slidesPerView: 1, spaceBetween: 20, loop: true,
        autoplay: { delay: 4500, disableOnInteraction: false },
        pagination: { el: '.screenshots-swiper .swiper-pagination', clickable: true },
        breakpoints: { 640:{slidesPerView:1.5}, 768:{slidesPerView:2}, 1024:{slidesPerView:3,spaceBetween:24} },
    });
    const navbar = document.getElementById('vitNavbar');
    window.addEventListener('scroll', () => navbar.classList.toggle('scrolled', window.scrollY > 50));
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function (e) {
            const t = document.querySelector(this.getAttribute('href'));
            if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        });
    });
</script>

</body>
</html>
