@extends('layouts.app')

@section('title', $annonce->title)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.annonces.index') }}">Annonces</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($annonce->title, 40) }}</li>
</ol>
@endsection

@section('content')
<article class="annonce-detail" style="--accent: {{ $annonce->couleur ?? '#7C3AED' }};">

    {{-- En-tête : badges + titre + extrait --}}
    <header class="annonce-header">
        <div class="annonce-header-badges">
            @if($annonce->categorie)
                <span class="annonce-badge-cat" style="background: color-mix(in srgb, var(--accent) 12%, #fff); color: var(--accent);">
                    {{ $annonce->categorie }}
                </span>
            @endif
            @if($annonce->epingle)
                <span class="annonce-badge-soft pin"><i class="fas fa-thumbtack"></i> Épinglée</span>
            @endif
            @if($annonce->is_urgent)
                <span class="annonce-badge-soft urgent"><i class="fas fa-bolt"></i> URGENT</span>
            @endif
        </div>
        <h1 class="annonce-header-title">{{ $annonce->title }}</h1>
        @if($annonce->extrait)
        <p class="annonce-header-extrait">{{ $annonce->extrait }}</p>
        @endif
    </header>

    {{-- Média principal (sous le chapeau) --}}
    @if($annonce->media_url)
    <figure class="annonce-media">
        @if($annonce->media_principal_type === 'image')
            <img src="{{ $annonce->media_url }}" alt="{{ $annonce->title }}"
                 class="annonce-media-img lightbox-trigger" data-name="{{ $annonce->title }}">
        @elseif($annonce->media_principal_type === 'video')
            <video src="{{ $annonce->media_url }}" controls class="annonce-media-img"></video>
        @endif
    </figure>
    @endif

    {{-- Contenu --}}
    <div class="annonce-content">
        {!! $annonce->content !!}
    </div>

    {{-- Pièces jointes --}}
    @if($annonce->piecesJointes->count())
    <section class="annonce-attachments">
        <h3 class="annonce-section-title"><i class="fas fa-paperclip"></i> Pièces jointes</h3>
        <div class="attachments-grid">
            @foreach($annonce->piecesJointes as $pj)
                @if($pj->categorie === 'image')
                <button type="button" class="attachment-card lightbox-trigger"
                        data-src="{{ $pj->url }}" data-name="{{ $pj->nom_original }}">
                    <img src="{{ $pj->url }}" alt="" class="attachment-thumb">
                    <div class="attachment-info">
                        <div class="attachment-name">{{ $pj->nom_original }}</div>
                        <div class="attachment-size">{{ $pj->taille_humaine }}</div>
                    </div>
                </button>
                @else
                <a href="{{ $pj->url }}" target="_blank" class="attachment-card">
                    <div class="attachment-icon"><i class="fas {{ $pj->icone }}"></i></div>
                    <div class="attachment-info">
                        <div class="attachment-name">{{ $pj->nom_original }}</div>
                        <div class="attachment-size">{{ $pj->taille_humaine }}</div>
                    </div>
                </a>
                @endif
            @endforeach
        </div>
    </section>
    @endif

    {{-- Cibles (si privé) --}}
    @if($annonce->publication && $annonce->publication->visibilite === 'prive')
    <section class="annonce-cibles">
        <h3 class="annonce-section-title"><i class="fas fa-bullseye"></i> Visibilité restreinte</h3>
        <p class="text-muted small mb-0">
            Cette annonce est diffusée à {{ $annonce->publication->cibles->count() }} cible(s) spécifique(s).
        </p>
    </section>
    @endif

    {{-- Commentaires --}}
    @if($annonce->commentairesActifs())
    <section class="annonce-comments">
        <h3 class="annonce-section-title">
            <i class="fas fa-comment"></i> Commentaires ({{ $annonce->commentaires->count() }})
        </h3>

        @if($annonce->commentaires->where('parent_id', null)->count())
            @foreach($annonce->commentaires->where('parent_id', null) as $comment)
            <div class="comment-item">
                <div class="comment-avatar">
                    {{ strtoupper(substr($comment->user->prenoms ?? $comment->user->name, 0, 1)) }}
                </div>
                <div class="comment-body">
                    <div class="comment-header">
                        <strong>{{ $comment->user->prenoms ?? '' }} {{ $comment->user->name }}</strong>
                        <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="comment-text">{{ $comment->contenu }}</div>
                </div>
            </div>
            @endforeach
        @else
            <p class="text-muted small">Aucun commentaire pour le moment.</p>
        @endif
    </section>
    @endif

    {{-- Pied de page : auteur + stats + actions --}}
    <footer class="annonce-footer">
        <div class="annonce-footer-author">
            <div class="annonce-author-avatar-lg" style="background: var(--accent);">
                {{ strtoupper(substr($annonce->auteur->prenoms ?? $annonce->auteur->name, 0, 1)) }}{{ strtoupper(substr($annonce->auteur->name, 0, 1)) }}
            </div>
            <div>
                <div class="annonce-footer-label">Publié par</div>
                <div class="annonce-author-name">{{ $annonce->auteur->prenoms ?? '' }} {{ $annonce->auteur->name }}</div>
                <div class="annonce-author-date">
                    <i class="far fa-clock"></i>
                    {{ $annonce->created_at->translatedFormat('d F Y à H:i') }}
                    @if($annonce->updated_at->gt($annonce->created_at))
                        · modifié {{ $annonce->updated_at->diffForHumans() }}
                    @endif
                </div>
            </div>
        </div>

        <div class="annonce-footer-stats">
            <div class="footer-stat">
                <i class="fas fa-eye"></i>
                <span>{{ $annonce->vues_count }}</span>
                <small>vues</small>
            </div>
            @if($annonce->likesActifs())
            <div class="footer-stat">
                <i class="fas fa-heart"></i>
                <span>{{ $annonce->totalLikes() }}</span>
                <small>j'aime</small>
            </div>
            @endif
            @if($annonce->commentairesActifs())
            <div class="footer-stat">
                <i class="fas fa-comment"></i>
                <span>{{ $annonce->totalCommentaires() }}</span>
                <small>commentaires</small>
            </div>
            @endif
        </div>

        <div class="annonce-footer-actions">
            <a href="{{ route('intranet.annonces.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Retour
            </a>
            @can('update:annonce')
            <a href="{{ route('intranet.annonces.edit', $annonce) }}" class="btn btn-intranet">
                <i class="fas fa-pen-to-square me-2"></i> Modifier
            </a>
            @endcan
            @can('delete:annonce')
            <form action="{{ route('intranet.annonces.destroy', $annonce) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Supprimer définitivement cette annonce ?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger-soft">
                    <i class="fas fa-trash me-2"></i> Supprimer
                </button>
            </form>
            @endcan
        </div>
    </footer>

</article>

{{-- ════════════════════════════════════════════════════
     LIGHTBOX
════════════════════════════════════════════════════ --}}
<div id="lightbox" class="lightbox" role="dialog" aria-hidden="true">
    <button type="button" class="lightbox-close" aria-label="Fermer">
        <i class="fas fa-xmark"></i>
    </button>
    <button type="button" class="lightbox-zoom-in" aria-label="Zoomer"><i class="fas fa-plus"></i></button>
    <button type="button" class="lightbox-zoom-out" aria-label="Dézoomer"><i class="fas fa-minus"></i></button>
    <button type="button" class="lightbox-zoom-reset" aria-label="Réinitialiser"><i class="fas fa-rotate"></i></button>
    <button type="button" class="lightbox-prev" aria-label="Précédent"><i class="fas fa-chevron-left"></i></button>
    <button type="button" class="lightbox-next" aria-label="Suivant"><i class="fas fa-chevron-right"></i></button>
    <div class="lightbox-stage">
        <img src="" alt="" class="lightbox-img" id="lightboxImg">
    </div>
    <div class="lightbox-caption" id="lightboxCaption"></div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Marquer toutes les images du contenu de l'annonce comme zoomables ──
    document.querySelectorAll('.annonce-content img').forEach(function (img) {
        img.classList.add('lightbox-trigger');
        img.dataset.src  = img.src;
        img.dataset.name = img.alt || '';
        img.style.cursor = 'zoom-in';
    });

    const lightbox  = document.getElementById('lightbox');
    const lbImg     = document.getElementById('lightboxImg');
    const lbCaption = document.getElementById('lightboxCaption');
    const triggers  = document.querySelectorAll('.lightbox-trigger');
    if (!lightbox || triggers.length === 0) return;

    // Construire la galerie
    const gallery = Array.from(triggers).map(el => ({
        src:  el.dataset.src || el.getAttribute('src'),
        name: el.dataset.name || el.getAttribute('alt') || '',
    }));
    let currentIndex = 0;
    let scale = 1;
    let posX = 0, posY = 0;
    let isDragging = false, startX = 0, startY = 0;

    function openAt(index) {
        currentIndex = (index + gallery.length) % gallery.length;
        lbImg.src = gallery[currentIndex].src;
        lbCaption.textContent = gallery[currentIndex].name || '';
        resetZoom();
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function close() {
        lightbox.classList.remove('open');
        document.body.style.overflow = '';
        resetZoom();
    }
    function next() { openAt(currentIndex + 1); }
    function prev() { openAt(currentIndex - 1); }

    function resetZoom() {
        scale = 1; posX = 0; posY = 0;
        applyTransform();
    }
    function applyTransform() {
        lbImg.style.transform = `translate(${posX}px, ${posY}px) scale(${scale})`;
        lbImg.style.cursor = scale > 1 ? (isDragging ? 'grabbing' : 'grab') : 'zoom-in';
    }
    function zoom(delta, centerX, centerY) {
        const newScale = Math.min(Math.max(scale + delta, 1), 5);
        if (newScale === scale) return;
        // Zoom autour du curseur
        if (centerX !== undefined && centerY !== undefined) {
            const rect = lbImg.getBoundingClientRect();
            const cx = centerX - rect.left - rect.width / 2;
            const cy = centerY - rect.top  - rect.height / 2;
            posX -= cx * (newScale / scale - 1);
            posY -= cy * (newScale / scale - 1);
        }
        scale = newScale;
        if (scale === 1) { posX = 0; posY = 0; }
        applyTransform();
    }

    // Triggers
    triggers.forEach((el, i) => {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            openAt(i);
        });
    });

    // Boutons
    lightbox.querySelector('.lightbox-close').addEventListener('click', close);
    lightbox.querySelector('.lightbox-next').addEventListener('click', next);
    lightbox.querySelector('.lightbox-prev').addEventListener('click', prev);
    lightbox.querySelector('.lightbox-zoom-in').addEventListener('click', () => zoom(0.4));
    lightbox.querySelector('.lightbox-zoom-out').addEventListener('click', () => zoom(-0.4));
    lightbox.querySelector('.lightbox-zoom-reset').addEventListener('click', resetZoom);

    // Click sur image : toggle zoom
    lbImg.addEventListener('click', function (e) {
        if (scale === 1) zoom(1, e.clientX, e.clientY);
        else resetZoom();
    });

    // Click extérieur ferme
    lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox || e.target.classList.contains('lightbox-stage')) close();
    });

    // Wheel : zoom in/out
    lightbox.addEventListener('wheel', function (e) {
        if (!lightbox.classList.contains('open')) return;
        e.preventDefault();
        zoom(e.deltaY < 0 ? 0.2 : -0.2, e.clientX, e.clientY);
    }, { passive: false });

    // Drag pour panner quand zoomé
    lbImg.addEventListener('mousedown', function (e) {
        if (scale <= 1) return;
        isDragging = true;
        startX = e.clientX - posX;
        startY = e.clientY - posY;
        lbImg.style.cursor = 'grabbing';
        e.preventDefault();
    });
    window.addEventListener('mousemove', function (e) {
        if (!isDragging) return;
        posX = e.clientX - startX;
        posY = e.clientY - startY;
        applyTransform();
    });
    window.addEventListener('mouseup', function () {
        if (isDragging) { isDragging = false; applyTransform(); }
    });

    // Clavier
    document.addEventListener('keydown', function (e) {
        if (!lightbox.classList.contains('open')) return;
        if (e.key === 'Escape')    close();
        if (e.key === 'ArrowRight') next();
        if (e.key === 'ArrowLeft')  prev();
        if (e.key === '+') zoom(0.3);
        if (e.key === '-') zoom(-0.3);
        if (e.key === '0') resetZoom();
    });
});
</script>
@endpush
@endsection
