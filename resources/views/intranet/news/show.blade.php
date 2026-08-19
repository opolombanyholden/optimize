@extends('layouts.app')

@section('title', $news->title)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.news.index') }}">Actualités</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($news->title, 40) }}</li>
</ol>
@endsection

@section('content')
<article class="news-detail" style="--accent: {{ $news->couleur ?? '#7C3AED' }};">

    {{-- En-tête --}}
    <header class="news-detail-header">
        <div class="news-detail-rubrique-row">
            @if($news->rubrique)
                <span class="news-detail-rubrique">{{ $news->rubrique }}</span>
            @endif
            @if($news->a_la_une)
                <span class="news-detail-flag"><i class="fas fa-star"></i> À la une</span>
            @endif
        </div>

        <h1 class="news-detail-title">{{ $news->title }}</h1>

        @if($news->extrait)
        <p class="news-detail-extrait">{{ $news->extrait }}</p>
        @endif

        <div class="news-detail-byline">
            <div class="news-author-block">
                <div class="news-author-avatar-lg" style="background: var(--accent);">
                    {{ strtoupper(substr($news->auteur->prenoms ?? $news->auteur->name, 0, 1)) }}
                </div>
                <div>
                    <div class="news-author-name-lg">{{ $news->signature_affichage }}</div>
                    <div class="news-author-date">
                        {{ $news->created_at->translatedFormat('d F Y') }}
                        @if($news->temps_lecture) · {{ $news->temps_lecture }} min de lecture @endif
                        · {{ $news->vues_count }} vues
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Visuel principal --}}
    @if($news->media_url)
    <figure class="news-detail-media">
        @if($news->media_principal_type === 'image')
            <img src="{{ $news->media_url }}" alt="{{ $news->title }}"
                 class="lightbox-trigger" data-name="{{ $news->title }}">
        @elseif($news->media_principal_type === 'video')
            <video src="{{ $news->media_url }}" controls></video>
        @endif
    </figure>
    @endif

    {{-- Contenu --}}
    <div class="news-detail-content annonce-content">
        {!! $news->content !!}
    </div>

    {{-- Tags --}}
    @if(!empty($news->tags))
    <div class="news-detail-tags">
        @foreach($news->tags as $tag)
            <span class="news-tag">#{{ $tag }}</span>
        @endforeach
    </div>
    @endif

    {{-- Source --}}
    @if($news->source)
    <div class="news-detail-source">
        <i class="fas fa-link"></i> Source :
        @if(filter_var($news->source, FILTER_VALIDATE_URL))
            <a href="{{ $news->source }}" target="_blank" rel="noopener">{{ $news->source }}</a>
        @else
            <span>{{ $news->source }}</span>
        @endif
    </div>
    @endif

    {{-- Pièces jointes --}}
    @if($news->piecesJointes->count())
    <section class="annonce-attachments">
        <h3 class="annonce-section-title"><i class="fas fa-paperclip"></i> Pièces jointes</h3>
        <div class="attachments-grid">
            @foreach($news->piecesJointes as $pj)
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

    {{-- Articles connexes --}}
    @if($relatedNews->count())
    <section class="news-related">
        <h3 class="annonce-section-title"><i class="fas fa-newspaper"></i> À lire aussi</h3>
        <div class="news-related-grid">
            @foreach($relatedNews as $related)
            <a href="{{ route('intranet.news.show', $related) }}" class="news-related-item">
                @if($related->media_url)
                    <img src="{{ $related->media_url }}" alt="">
                @else
                    <div class="news-related-placeholder"><i class="fas fa-newspaper"></i></div>
                @endif
                <div class="news-related-body">
                    @if($related->rubrique)
                        <span class="news-related-cat">{{ $related->rubrique }}</span>
                    @endif
                    <div class="news-related-title">{{ $related->title }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Commentaires --}}
    @if($news->commentairesActifs())
    <section class="annonce-comments">
        <h3 class="annonce-section-title">
            <i class="fas fa-comment"></i> Commentaires ({{ $news->commentaires->count() }})
        </h3>
        @if($news->commentaires->where('parent_id', null)->count())
            @foreach($news->commentaires->where('parent_id', null) as $comment)
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

    {{-- Footer auteur + actions --}}
    <footer class="annonce-footer">
        <div class="annonce-footer-author">
            <div class="annonce-author-avatar-lg" style="background: var(--accent);">
                {{ strtoupper(substr($news->auteur->prenoms ?? $news->auteur->name, 0, 1)) }}{{ strtoupper(substr($news->auteur->name, 0, 1)) }}
            </div>
            <div>
                <div class="annonce-footer-label">Publié par</div>
                <div class="annonce-author-name">{{ $news->signature_affichage }}</div>
                <div class="annonce-author-date">
                    <i class="far fa-clock"></i>
                    {{ $news->created_at->translatedFormat('d F Y à H:i') }}
                    @if($news->updated_at->gt($news->created_at))
                        · modifié {{ $news->updated_at->diffForHumans() }}
                    @endif
                </div>
            </div>
        </div>

        <div class="annonce-footer-stats">
            <div class="footer-stat"><i class="fas fa-eye"></i><span>{{ $news->vues_count }}</span><small>vues</small></div>
            @if($news->likesActifs())
            <div class="footer-stat"><i class="fas fa-heart"></i><span>{{ $news->totalLikes() }}</span><small>j'aime</small></div>
            @endif
            @if($news->commentairesActifs())
            <div class="footer-stat"><i class="fas fa-comment"></i><span>{{ $news->totalCommentaires() }}</span><small>commentaires</small></div>
            @endif
        </div>

        <div class="annonce-footer-actions">
            <a href="{{ route('intranet.news.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Retour
            </a>
            @can('update:news')
            <a href="{{ route('intranet.news.edit', $news) }}" class="btn btn-intranet">
                <i class="fas fa-pen-to-square me-2"></i> Modifier
            </a>
            @endcan
            @can('delete:news')
            <form action="{{ route('intranet.news.destroy', $news) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Supprimer définitivement cet article ?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger-soft">
                    <i class="fas fa-trash me-2"></i> Supprimer
                </button>
            </form>
            @endcan
        </div>
    </footer>
</article>

{{-- Lightbox --}}
<div id="lightbox" class="lightbox" role="dialog" aria-hidden="true">
    <button type="button" class="lightbox-close" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
    <button type="button" class="lightbox-zoom-in" aria-label="Zoomer"><i class="fas fa-plus"></i></button>
    <button type="button" class="lightbox-zoom-out" aria-label="Dézoomer"><i class="fas fa-minus"></i></button>
    <button type="button" class="lightbox-zoom-reset" aria-label="Réinitialiser"><i class="fas fa-rotate"></i></button>
    <button type="button" class="lightbox-prev" aria-label="Précédent"><i class="fas fa-chevron-left"></i></button>
    <button type="button" class="lightbox-next" aria-label="Suivant"><i class="fas fa-chevron-right"></i></button>
    <div class="lightbox-stage"><img src="" alt="" class="lightbox-img" id="lightboxImg"></div>
    <div class="lightbox-caption" id="lightboxCaption"></div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.news-detail-content img').forEach(function (img) {
        img.classList.add('lightbox-trigger');
        img.dataset.src = img.src;
        img.dataset.name = img.alt || '';
        img.style.cursor = 'zoom-in';
    });

    const lightbox  = document.getElementById('lightbox');
    const lbImg     = document.getElementById('lightboxImg');
    const lbCaption = document.getElementById('lightboxCaption');
    const triggers  = document.querySelectorAll('.lightbox-trigger');
    if (!lightbox || triggers.length === 0) return;

    const gallery = Array.from(triggers).map(el => ({
        src: el.dataset.src || el.getAttribute('src'),
        name: el.dataset.name || el.getAttribute('alt') || '',
    }));
    let currentIndex = 0, scale = 1, posX = 0, posY = 0;
    let isDragging = false, startX = 0, startY = 0;

    function openAt(i) {
        currentIndex = (i + gallery.length) % gallery.length;
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
    function resetZoom() { scale = 1; posX = 0; posY = 0; applyTransform(); }
    function applyTransform() {
        lbImg.style.transform = `translate(${posX}px, ${posY}px) scale(${scale})`;
        lbImg.style.cursor = scale > 1 ? (isDragging ? 'grabbing' : 'grab') : 'zoom-in';
    }
    function zoom(d, cx, cy) {
        const ns = Math.min(Math.max(scale + d, 1), 5);
        if (ns === scale) return;
        if (cx !== undefined) {
            const r = lbImg.getBoundingClientRect();
            const x = cx - r.left - r.width / 2, y = cy - r.top - r.height / 2;
            posX -= x * (ns / scale - 1);
            posY -= y * (ns / scale - 1);
        }
        scale = ns;
        if (scale === 1) { posX = 0; posY = 0; }
        applyTransform();
    }

    triggers.forEach((el, i) => el.addEventListener('click', e => { e.preventDefault(); openAt(i); }));
    lightbox.querySelector('.lightbox-close').addEventListener('click', close);
    lightbox.querySelector('.lightbox-next').addEventListener('click', () => openAt(currentIndex + 1));
    lightbox.querySelector('.lightbox-prev').addEventListener('click', () => openAt(currentIndex - 1));
    lightbox.querySelector('.lightbox-zoom-in').addEventListener('click', () => zoom(0.4));
    lightbox.querySelector('.lightbox-zoom-out').addEventListener('click', () => zoom(-0.4));
    lightbox.querySelector('.lightbox-zoom-reset').addEventListener('click', resetZoom);
    lbImg.addEventListener('click', e => { if (scale === 1) zoom(1, e.clientX, e.clientY); else resetZoom(); });
    lightbox.addEventListener('click', e => { if (e.target === lightbox || e.target.classList.contains('lightbox-stage')) close(); });
    lightbox.addEventListener('wheel', e => { if (lightbox.classList.contains('open')) { e.preventDefault(); zoom(e.deltaY < 0 ? 0.2 : -0.2, e.clientX, e.clientY); } }, { passive: false });
    lbImg.addEventListener('mousedown', e => { if (scale > 1) { isDragging = true; startX = e.clientX - posX; startY = e.clientY - posY; e.preventDefault(); } });
    window.addEventListener('mousemove', e => { if (isDragging) { posX = e.clientX - startX; posY = e.clientY - startY; applyTransform(); } });
    window.addEventListener('mouseup', () => { if (isDragging) { isDragging = false; applyTransform(); } });
    document.addEventListener('keydown', e => {
        if (!lightbox.classList.contains('open')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowRight') openAt(currentIndex + 1);
        if (e.key === 'ArrowLeft') openAt(currentIndex - 1);
        if (e.key === '+') zoom(0.3);
        if (e.key === '-') zoom(-0.3);
        if (e.key === '0') resetZoom();
    });
});
</script>
@endpush
@endsection
