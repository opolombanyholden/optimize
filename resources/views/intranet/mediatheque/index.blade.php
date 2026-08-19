@extends('layouts.app')
@section('title', 'Médiathèque')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.mediatheque.index') }}">Médiathèque</a></li>
    @if($currentAlbum)
        @foreach($currentAlbum->chemin_complet as $anc)
            @if(!$loop->last)
            <li class="breadcrumb-item"><a href="{{ route('intranet.mediatheque.index', ['album_id' => $anc->id]) }}">{{ $anc->nom }}</a></li>
            @else
            <li class="breadcrumb-item active">{{ $anc->nom }}</li>
            @endif
        @endforeach
    @endif
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-photo-film"></i></span>
                {{ $currentAlbum ? $currentAlbum->nom : 'Médiathèque' }}
            </h1>
            <p class="page-subtitle">{{ $currentAlbum ? $currentAlbum->description ?? 'Contenu de l\'album' : 'Bibliothèque de médias organisée par albums' }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($currentAlbum)
            <a href="{{ route('intranet.mediatheque.index', ['album_id' => $currentAlbum->parent_id]) }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Remonter
            </a>
            @endif
            @can('create:media')
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalAlbum">
                <i class="fas fa-folder-plus me-2"></i> Album
            </button>
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalUploadMultiple">
                <i class="fas fa-layer-group me-2"></i> Lot multiple
            </button>
            <a href="{{ route('intranet.mediatheque.create', ['album_id' => $currentAlbum?->id]) }}" class="btn btn-intranet">
                <i class="fas fa-cloud-arrow-up me-2"></i> Média
            </a>
            @endcan
        </div>
    </div>

    {{-- Modale upload multiple --}}
    @can('create:media')
    <div class="modal fade" id="modalUploadMultiple" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('intranet.mediatheque.upload-multiple') }}" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
                    <h5 class="modal-title text-white"><i class="fas fa-layer-group me-2"></i> Upload multiple</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3" style="font-size:.8rem;">
                        <i class="fas fa-circle-info me-1"></i> Téléversez plusieurs fichiers en une fois. Le titre sera dérivé du nom de fichier ; les images héritent automatiquement de leurs dimensions.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Fichiers <span class="text-danger">*</span></label>
                            <input type="file" name="fichiers[]" class="form-control" multiple required accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                            <small class="form-hint">100 Mo par fichier · Ctrl/Cmd+clic pour sélection multiple</small>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Album</label>
                            <select name="album_id" class="form-select">
                                <option value="">— Aucun (racine) —</option>
                                @foreach(($albums ?? collect()) as $alb)
                                    <option value="{{ $alb->id }}" @selected($currentAlbum?->id === $alb->id)>{{ $alb->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Visibilité</label>
                        <select name="visibilite" class="form-select">
                            <option value="public">Public (tous les collaborateurs)</option>
                            <option value="prive">Privé (auteur seulement)</option>
                            <option value="groupes">Groupes ciblés</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn text-white" style="background:#F59E0B;"><i class="fas fa-cloud-arrow-up me-2"></i> Téléverser le lot</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    {{-- Stats --}}
    <div class="courrier-stats mb-4">
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#F59E0B;"><i class="fas fa-layer-group"></i></div>
            <div><div class="csc-value">{{ $stats['albums'] }}</div><div class="csc-label">Albums</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#7C3AED;"><i class="fas fa-image"></i></div>
            <div><div class="csc-value">{{ $stats['images'] }}</div><div class="csc-label">Images</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#DC2626;"><i class="fas fa-video"></i></div>
            <div><div class="csc-value">{{ $stats['videos'] }}</div><div class="csc-label">Vidéos</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#0891B2;"><i class="fas fa-photo-film"></i></div>
            <div><div class="csc-value">{{ $stats['total'] }}</div><div class="csc-label">Total</div></div>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="filters-bar mb-4">
        @if($currentAlbum)
        <input type="hidden" name="album_id" value="{{ $currentAlbum->id }}">
        @endif
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un média…">
        </div>
        <select name="type" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous types</option>
            <option value="image"    @selected(request('type')==='image')>🖼 Images</option>
            <option value="video"    @selected(request('type')==='video')>🎬 Vidéos</option>
            <option value="audio"    @selected(request('type')==='audio')>🎵 Audio</option>
            <option value="document" @selected(request('type')==='document')>📄 Documents</option>
        </select>
        @if(request('q') || request('type'))
        <a href="{{ route('intranet.mediatheque.index', ['album_id' => $currentAlbum?->id]) }}" class="btn-reset"><i class="fas fa-xmark"></i></a>
        @endif
    </form>

    {{-- ═══ ALBUMS ═══════════════════════════════════════ --}}
    @if($sousAlbums->count() && !request()->filled('q'))
    <h3 class="ged-section-title mb-3"><i class="fas fa-layer-group"></i> Albums</h3>
    <div class="media-albums-grid mb-4">
        @foreach($sousAlbums as $album)
        <div class="media-album-card" style="--album-color: {{ $album->couleur }};">
            <a href="{{ route('intranet.mediatheque.index', ['album_id' => $album->id]) }}" class="media-album-link">
                <div class="media-album-cover">
                    @if($album->couverture_url)
                        <img src="{{ $album->couverture_url }}" alt="{{ $album->nom }}">
                    @elseif($album->icone)
                        <i class="fas {{ $album->icone }}"></i>
                    @else
                        <i class="fas fa-layer-group"></i>
                    @endif
                </div>
                <div class="media-album-body">
                    <h4 class="media-album-name">{{ $album->nom }}</h4>
                    <span class="media-album-count">{{ $album->medias_count }} média{{ $album->medias_count > 1 ? 's' : '' }}</span>
                </div>
            </a>
            <div class="media-album-actions">
                <div class="dropdown">
                    <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                        <li><a class="dropdown-item" href="{{ route('intranet.mediatheque.index', ['album_id' => $album->id]) }}">
                            <i class="fas fa-folder-open"></i> Ouvrir</a></li>
                        @can('update:media')
                        <li><button class="dropdown-item" onclick="editAlbum({{ $album->id }}, '{{ addslashes($album->nom) }}', '{{ addslashes($album->description ?? '') }}', '{{ $album->icone }}', '{{ $album->couleur }}')">
                            <i class="fas fa-pen-to-square"></i> Modifier</button></li>
                        @endcan
                        @can('delete:media')
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('intranet.mediatheque.albums.destroy', $album) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cet album ? Les médias seront conservés.');">
                                @csrf @method('DELETE')
                                <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                            </form>
                        </li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ═══ MÉDIAS ═══════════════════════════════════════ --}}
    @if($medias->count())
    @if($sousAlbums->count() && !request()->filled('q'))
    <h3 class="ged-section-title mb-3"><i class="fas fa-photo-film"></i> Médias</h3>
    @endif
    <div class="media-gallery">
        @foreach($medias as $m)
        <div class="media-card" style="--media-color: {{ $m->couleur }};">
            @if($m->est_image && $m->fichier_url)
            {{-- Image : lightbox diapo --}}
            <button type="button" class="media-card-preview media-lightbox-trigger"
                    data-src="{{ $m->fichier_url }}"
                    data-title="{{ $m->titre }}"
                    data-desc="{{ $m->taille_humaine }}{{ $m->dimensions ? ' · ' . $m->dimensions : '' }}"
                    data-detail="{{ route('intranet.mediatheque.show', $m) }}">
                <img src="{{ $m->fichier_url }}" alt="{{ $m->titre }}" class="media-card-img" loading="lazy">
                <span class="media-card-type-badge"><i class="fas fa-image"></i></span>
                <span class="media-card-zoom-hint"><i class="fas fa-expand"></i></span>
            </button>
            @else
            {{-- Non-image : lien vers détail --}}
            <a href="{{ route('intranet.mediatheque.show', $m) }}" class="media-card-preview">
                @if($m->est_externe && $m->apercu_url)
                    {{-- Vidéo externe avec miniature --}}
                    <img src="{{ $m->apercu_url }}" alt="{{ $m->titre }}" class="media-card-img" loading="lazy">
                    <div class="media-card-play-overlay"><i class="fas fa-circle-play"></i></div>
                    @if($m->plateforme)
                    <span class="media-card-platform">
                        <i class="fab fa-{{ $m->plateforme === 'youtube' ? 'youtube' : ($m->plateforme === 'vimeo' ? 'vimeo-v' : 'dailymotion') }}"></i>
                    </span>
                    @endif
                @elseif($m->est_video && $m->fichier_url)
                    <div class="media-card-video-placeholder"><i class="fas fa-circle-play"></i></div>
                @else
                    <div class="media-card-icon-placeholder"><i class="fas {{ $m->icone }}"></i></div>
                @endif
                <span class="media-card-type-badge"><i class="fas {{ $m->icone }}"></i></span>
                @if($m->duree_humaine)<span class="media-card-duration">{{ $m->duree_humaine }}</span>@endif
            </a>
            @endif
            <div class="media-card-body">
                <h4 class="media-card-title"><a href="{{ route('intranet.mediatheque.show', $m) }}">{{ $m->titre }}</a></h4>
                @if($m->albumRelation)
                    <span class="media-card-album"><i class="fas fa-layer-group"></i> {{ $m->albumRelation->nom }}</span>
                @endif
                <div class="media-card-meta">
                    <span>{{ $m->taille_humaine }}</span>
                    @if($m->dimensions)<span>{{ $m->dimensions }}</span>@endif
                    <span><i class="fas fa-eye"></i> {{ $m->vues_count }}</span>
                </div>
            </div>
            <div class="media-card-actions">
                <div class="dropdown">
                    <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                        <li><a class="dropdown-item" href="{{ route('intranet.mediatheque.show', $m) }}"><i class="fas fa-eye"></i> Voir</a></li>
                        <li><a class="dropdown-item" href="{{ route('intranet.mediatheque.download', $m) }}"><i class="fas fa-download"></i> Télécharger</a></li>
                        @can('update:media')
                        <li><a class="dropdown-item" href="{{ route('intranet.mediatheque.edit', $m) }}"><i class="fas fa-pen-to-square"></i> Modifier</a></li>
                        @endcan
                        @can('delete:media')
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('intranet.mediatheque.destroy', $m) }}" method="POST" onsubmit="return confirm('Supprimer ?');">
                                @csrf @method('DELETE')
                                <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                            </form>
                        </li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $medias->links() }}</div>
    @elseif(!$sousAlbums->count())
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-photo-film"></i></div>
        <h3>{{ $currentAlbum ? 'Album vide' : 'Médiathèque vide' }}</h3>
        @can('create:media')
        <div class="d-flex gap-2 justify-content-center">
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalAlbum"><i class="fas fa-folder-plus me-2"></i> Album</button>
            <a href="{{ route('intranet.mediatheque.create', ['album_id' => $currentAlbum?->id]) }}" class="btn btn-intranet"><i class="fas fa-cloud-arrow-up me-2"></i> Média</a>
        </div>
        @endcan
    </div>
    @endif
</div>

{{-- ═══ LIGHTBOX DIAPORAMA ══════════════════════════════ --}}
<div id="mediaLightbox" class="lightbox" role="dialog" aria-hidden="true">
    <button type="button" class="lightbox-close" aria-label="Fermer"><i class="fas fa-xmark"></i></button>

    {{-- Contrôles du diaporama --}}
    <div class="diapo-controls">
        <button type="button" class="lightbox-zoom-in" title="Zoom +"><i class="fas fa-plus"></i></button>
        <button type="button" class="lightbox-zoom-out" title="Zoom -"><i class="fas fa-minus"></i></button>
        <button type="button" class="lightbox-zoom-reset" title="Reset"><i class="fas fa-rotate"></i></button>
        <div class="diapo-separator"></div>
        <button type="button" id="diapoPlayBtn" title="Diaporama"><i class="fas fa-play"></i></button>
        <a href="#" id="diapoDetailLink" class="diapo-detail-btn" title="Ouvrir le détail"><i class="fas fa-arrow-up-right-from-square"></i></a>
    </div>

    <button type="button" class="lightbox-prev" aria-label="Précédent"><i class="fas fa-chevron-left"></i></button>
    <button type="button" class="lightbox-next" aria-label="Suivant"><i class="fas fa-chevron-right"></i></button>

    <div class="lightbox-stage"><img src="" alt="" class="lightbox-img" id="mediaLbImg"></div>

    {{-- Barre d'info en bas --}}
    <div class="diapo-info-bar">
        <div class="diapo-counter" id="diapoCounter">1 / 1</div>
        <div class="diapo-title" id="diapoTitle"></div>
        <div class="diapo-desc" id="diapoDesc"></div>
    </div>

    {{-- Barre de miniatures --}}
    <div class="diapo-thumbs-bar" id="diapoThumbsBar"></div>
</div>

{{-- ═══ MODALE ALBUM ════════════════════════════════════ --}}
@can('create:media')
<div class="modal fade" id="modalAlbum" tabindex="-1">
    <div class="modal-dialog">
        <form id="albumForm" method="POST" enctype="multipart/form-data" class="modal-content"
              action="{{ route('intranet.mediatheque.albums.store') }}">
            @csrf
            <input type="hidden" name="_method" id="albumMethod" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="albumModalTitle">Nouvel album</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="parent_id" value="{{ $currentAlbum?->id }}">
                <div class="mb-3">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="albumNom" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="albumDesc" rows="2" class="form-control"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Icône</label>
                        <input type="text" name="icone" id="albumIcone" class="form-control" placeholder="fa-camera…">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Couleur</label>
                        <input type="color" name="couleur" id="albumCouleur" class="form-control form-control-color" value="#7C3AED">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Image de couverture</label>
                    <input type="file" name="couverture" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet"><i class="fas fa-folder-plus me-2"></i> <span id="albumSubmitText">Créer</span></button>
            </div>
        </form>
    </div>
</div>
@endcan

@push('scripts')
<script>
// ═══ LIGHTBOX DIAPORAMA ══════════════════════════════
(function () {
    const lightbox = document.getElementById('mediaLightbox');
    const lbImg    = document.getElementById('mediaLbImg');
    const counter  = document.getElementById('diapoCounter');
    const titleEl  = document.getElementById('diapoTitle');
    const descEl   = document.getElementById('diapoDesc');
    const detailLink = document.getElementById('diapoDetailLink');
    const thumbsBar  = document.getElementById('diapoThumbsBar');
    const playBtn    = document.getElementById('diapoPlayBtn');
    const triggers   = document.querySelectorAll('.media-lightbox-trigger');

    if (!lightbox || triggers.length === 0) return;

    const gallery = Array.from(triggers).map(el => ({
        src:    el.dataset.src,
        title:  el.dataset.title || '',
        desc:   el.dataset.desc || '',
        detail: el.dataset.detail || '#',
    }));

    let idx = 0, scale = 1, posX = 0, posY = 0;
    let isDragging = false, sX = 0, sY = 0;
    let autoplayTimer = null, isPlaying = false;

    function openAt(i) {
        idx = (i + gallery.length) % gallery.length;
        lbImg.src = gallery[idx].src;
        titleEl.textContent = gallery[idx].title;
        descEl.textContent  = gallery[idx].desc;
        detailLink.href     = gallery[idx].detail;
        counter.textContent = (idx + 1) + ' / ' + gallery.length;
        resetZoom();
        updateThumbs();
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function close() {
        stopAutoplay();
        lightbox.classList.remove('open');
        document.body.style.overflow = '';
        resetZoom();
    }
    function next() { openAt(idx + 1); }
    function prev() { openAt(idx - 1); }
    function resetZoom() { scale = 1; posX = 0; posY = 0; applyTransform(); }
    function applyTransform() {
        lbImg.style.transform = `translate(${posX}px,${posY}px) scale(${scale})`;
        lbImg.style.cursor = scale > 1 ? (isDragging ? 'grabbing' : 'grab') : 'zoom-in';
    }
    function zoom(d, cx, cy) {
        const ns = Math.min(Math.max(scale + d, 1), 5);
        if (ns === scale) return;
        if (cx !== undefined) {
            const r = lbImg.getBoundingClientRect();
            posX -= (cx - r.left - r.width / 2) * (ns / scale - 1);
            posY -= (cy - r.top - r.height / 2) * (ns / scale - 1);
        }
        scale = ns;
        if (scale === 1) { posX = 0; posY = 0; }
        applyTransform();
    }

    // Autoplay diaporama
    function toggleAutoplay() {
        if (isPlaying) stopAutoplay();
        else startAutoplay();
    }
    function startAutoplay() {
        isPlaying = true;
        playBtn.innerHTML = '<i class="fas fa-pause"></i>';
        playBtn.classList.add('active');
        autoplayTimer = setInterval(next, 3000);
    }
    function stopAutoplay() {
        isPlaying = false;
        playBtn.innerHTML = '<i class="fas fa-play"></i>';
        playBtn.classList.remove('active');
        if (autoplayTimer) clearInterval(autoplayTimer);
        autoplayTimer = null;
    }

    // Miniatures
    function buildThumbs() {
        thumbsBar.innerHTML = gallery.map((g, i) =>
            `<button class="diapo-thumb" data-i="${i}">
                <img src="${g.src}" alt="">
            </button>`
        ).join('');
        thumbsBar.querySelectorAll('.diapo-thumb').forEach(btn => {
            btn.addEventListener('click', () => openAt(parseInt(btn.dataset.i)));
        });
    }
    function updateThumbs() {
        thumbsBar.querySelectorAll('.diapo-thumb').forEach((btn, i) => {
            btn.classList.toggle('active', i === idx);
        });
        // Scroll la miniature active en vue
        const active = thumbsBar.querySelector('.diapo-thumb.active');
        if (active) active.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }
    buildThumbs();

    // Déclencheurs
    triggers.forEach((el, i) => el.addEventListener('click', e => { e.preventDefault(); openAt(i); }));

    // Boutons
    lightbox.querySelector('.lightbox-close').onclick = close;
    lightbox.querySelector('.lightbox-next').onclick = () => { stopAutoplay(); next(); };
    lightbox.querySelector('.lightbox-prev').onclick = () => { stopAutoplay(); prev(); };
    lightbox.querySelector('.lightbox-zoom-in').onclick = () => zoom(0.4);
    lightbox.querySelector('.lightbox-zoom-out').onclick = () => zoom(-0.4);
    lightbox.querySelector('.lightbox-zoom-reset').onclick = resetZoom;
    playBtn.onclick = toggleAutoplay;

    // Click image
    lbImg.addEventListener('click', e => {
        if (scale === 1) zoom(1, e.clientX, e.clientY);
        else resetZoom();
    });

    // Click extérieur
    lightbox.addEventListener('click', e => {
        if (e.target === lightbox || e.target.classList.contains('lightbox-stage')) close();
    });

    // Wheel
    lightbox.addEventListener('wheel', e => {
        if (lightbox.classList.contains('open')) {
            e.preventDefault();
            zoom(e.deltaY < 0 ? 0.2 : -0.2, e.clientX, e.clientY);
        }
    }, { passive: false });

    // Drag
    lbImg.addEventListener('mousedown', e => {
        if (scale > 1) { isDragging = true; sX = e.clientX - posX; sY = e.clientY - posY; e.preventDefault(); }
    });
    window.addEventListener('mousemove', e => {
        if (isDragging) { posX = e.clientX - sX; posY = e.clientY - sY; applyTransform(); }
    });
    window.addEventListener('mouseup', () => { if (isDragging) { isDragging = false; applyTransform(); } });

    // Clavier
    document.addEventListener('keydown', e => {
        if (!lightbox.classList.contains('open')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowRight') { stopAutoplay(); next(); }
        if (e.key === 'ArrowLeft')  { stopAutoplay(); prev(); }
        if (e.key === ' ') { e.preventDefault(); toggleAutoplay(); }
        if (e.key === '+') zoom(0.3);
        if (e.key === '-') zoom(-0.3);
        if (e.key === '0') resetZoom();
    });

    // Touch swipe
    let touchStartX = 0;
    lightbox.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
    lightbox.addEventListener('touchend', e => {
        const diff = e.changedTouches[0].screenX - touchStartX;
        if (Math.abs(diff) > 60) {
            stopAutoplay();
            diff > 0 ? prev() : next();
        }
    }, { passive: true });
})();

// ═══ ALBUM ═══════════════════════════════════════════
function editAlbum(id, nom, desc, icone, couleur) {
    document.getElementById('albumModalTitle').textContent = 'Modifier l\'album';
    document.getElementById('albumNom').value = nom;
    document.getElementById('albumDesc').value = desc;
    document.getElementById('albumIcone').value = icone || '';
    document.getElementById('albumCouleur').value = couleur || '#7C3AED';
    document.getElementById('albumMethod').value = 'PUT';
    document.getElementById('albumSubmitText').textContent = 'Enregistrer';
    document.getElementById('albumForm').action = '/intranet/mediatheque-albums/' + id;
    new bootstrap.Modal(document.getElementById('modalAlbum')).show();
}
document.getElementById('modalAlbum')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('albumModalTitle').textContent = 'Nouvel album';
    document.getElementById('albumNom').value = '';
    document.getElementById('albumDesc').value = '';
    document.getElementById('albumIcone').value = '';
    document.getElementById('albumCouleur').value = '#7C3AED';
    document.getElementById('albumMethod').value = 'POST';
    document.getElementById('albumSubmitText').textContent = 'Créer';
    document.getElementById('albumForm').action = '{{ route("intranet.mediatheque.albums.store") }}';
});
</script>
@endpush
@endsection
