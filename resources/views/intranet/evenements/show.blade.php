@extends('layouts.app')
@section('title', $evenement->titre)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.evenements.index') }}">Événements</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($evenement->titre, 40) }}</li>
</ol>
@endsection

@section('content')
<article class="evt-detail" style="--accent: {{ $evenement->couleur_affichee }};">

    {{-- Bannière + en-tête --}}
    <header class="evt-detail-hero">
        @if($evenement->media_url)
            @if($evenement->media_principal_type === 'image')
                <img src="{{ $evenement->media_url }}" alt="{{ $evenement->titre }}"
                     class="evt-hero-img lightbox-trigger" data-name="{{ $evenement->titre }}">
            @elseif($evenement->media_principal_type === 'video')
                <video src="{{ $evenement->media_url }}" controls class="evt-hero-img"></video>
            @endif
        @else
            <div class="evt-hero-placeholder"><i class="fas fa-calendar-check"></i></div>
        @endif

        <div class="evt-hero-overlay">
            <div class="evt-hero-badges">
                @if($evenement->type)
                    <span class="evt-detail-type" style="background: rgba(255,255,255,.95); color: var(--accent);">{{ $evenement->type->nom }}</span>
                @endif
                @if($evenement->est_en_cours)
                    <span class="evt-badge-live"><i class="fas fa-circle"></i> En cours</span>
                @elseif($evenement->est_passe)
                    <span class="evt-badge-past">Événement passé</span>
                @endif
            </div>
            <h1 class="evt-detail-title">{{ $evenement->titre }}</h1>
            @if($evenement->extrait)
            <p class="evt-detail-extrait">{{ $evenement->extrait }}</p>
            @endif
        </div>
    </header>

    {{-- Infos clés --}}
    <div class="evt-info-grid">
        <div class="evt-info-card">
            <div class="evt-info-icon"><i class="far fa-calendar"></i></div>
            <div>
                <div class="evt-info-label">Date</div>
                <div class="evt-info-value">{{ $evenement->date_debut->translatedFormat('d F Y') }}</div>
                @if($evenement->date_debut->format('Y-m-d') !== $evenement->date_fin->format('Y-m-d'))
                    <div class="evt-info-sub">→ {{ $evenement->date_fin->translatedFormat('d F Y') }}</div>
                @endif
            </div>
        </div>

        <div class="evt-info-card">
            <div class="evt-info-icon"><i class="far fa-clock"></i></div>
            <div>
                <div class="evt-info-label">Horaire</div>
                <div class="evt-info-value">
                    @if($evenement->journee_entiere)
                        Journée entière
                    @else
                        {{ $evenement->date_debut->format('H:i') }} – {{ $evenement->date_fin->format('H:i') }}
                    @endif
                </div>
                <div class="evt-info-sub">Durée : {{ $evenement->duree_humain }}</div>
            </div>
        </div>

        @if($evenement->lieu || $evenement->est_visio)
        <div class="evt-info-card">
            <div class="evt-info-icon">
                <i class="fas {{ $evenement->est_visio ? 'fa-video' : 'fa-location-dot' }}"></i>
            </div>
            <div>
                <div class="evt-info-label">{{ $evenement->est_visio ? 'Visio' : 'Lieu' }}</div>
                @if($evenement->lieu)
                    <div class="evt-info-value">{{ $evenement->lieu }}</div>
                @endif
                @if($evenement->lieu_url)
                    <a href="{{ $evenement->lieu_url }}" target="_blank" class="evt-info-link">
                        <i class="fas fa-map-location-dot"></i> Voir le plan
                    </a>
                @endif
                @if($evenement->lien_visio)
                    <a href="{{ $evenement->lien_visio }}" target="_blank" class="evt-info-link">
                        <i class="fas fa-video"></i> Rejoindre la visio
                    </a>
                @endif
            </div>
        </div>
        @endif

        <div class="evt-info-card">
            <div class="evt-info-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="evt-info-label">Participants</div>
                <div class="evt-info-value">
                    {{ $evenement->participants->count() }}
                    @if($evenement->capacite_max)
                        / {{ $evenement->capacite_max }}
                    @endif
                </div>
                <div class="evt-info-sub">{{ $evenement->participantsConfirmes->count() ?? 0 }} confirmés</div>
            </div>
        </div>
    </div>

    {{-- RSVP --}}
    @auth
    @if($evenement->participants->contains('id', auth()->id()) || $evenement->inscription_requise || ! $evenement->est_passe)
    <div class="evt-rsvp">
        <div class="evt-rsvp-question">
            <strong>Allez-vous participer à cet événement ?</strong>
            @if($monStatut)
                <span class="evt-rsvp-current">
                    Votre réponse :
                    @if($monStatut === 'confirme') ✅ Confirmé
                    @elseif($monStatut === 'decline') ❌ Décliné
                    @elseif($monStatut === 'peut_etre') 🤔 Peut-être
                    @else ✉️ Invité @endif
                </span>
            @endif
        </div>
        <form action="{{ route('intranet.evenements.rsvp', $evenement) }}" method="POST" class="evt-rsvp-actions">
            @csrf
            <button type="submit" name="statut" value="confirme" class="rsvp-btn confirme">
                <i class="fas fa-check"></i> Je participe
            </button>
            <button type="submit" name="statut" value="peut_etre" class="rsvp-btn peut_etre">
                <i class="fas fa-question"></i> Peut-être
            </button>
            <button type="submit" name="statut" value="decline" class="rsvp-btn decline">
                <i class="fas fa-xmark"></i> Décliner
            </button>
        </form>
    </div>
    @endif
    @endauth

    {{-- Description --}}
    @if($evenement->description)
    <div class="annonce-content evt-description">
        {!! $evenement->description !!}
    </div>
    @endif

    {{-- Pièces jointes --}}
    @if($evenement->piecesJointes->count())
    <section class="annonce-attachments">
        <h3 class="annonce-section-title"><i class="fas fa-paperclip"></i> Documents</h3>
        <div class="attachments-grid">
            @foreach($evenement->piecesJointes as $pj)
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

    {{-- Liste des participants --}}
    @if($evenement->participants->count() || $evenement->invitesExternes->count())
    <section class="evt-participants-list">
        <h3 class="annonce-section-title">
            <i class="fas fa-users"></i> Participants
            ({{ $evenement->participants->count() + $evenement->invitesExternes->count() }})
        </h3>

        @if($evenement->participants->count())
        <div class="evt-participants-subtitle">
            <i class="fas fa-id-badge"></i> Collaborateurs internes ({{ $evenement->participants->count() }})
        </div>
        <div class="participants-grid">
            @foreach($evenement->participants as $p)
                <div class="participant-item statut-{{ $p->pivot->statut }}">
                    <div class="participant-avatar" style="background: {{ ['#7C3AED','#059669','#D97706','#0891B2','#0D9488','#4F46E5'][$loop->index % 6] }};">
                        {{ strtoupper(substr($p->prenoms ?? $p->name, 0, 1)) }}{{ strtoupper(substr($p->name, 0, 1)) }}
                    </div>
                    <div class="participant-info">
                        <div class="participant-name">{{ $p->prenoms }} {{ $p->name }}</div>
                        <div class="participant-status">
                            @if($p->pivot->statut === 'confirme') <i class="fas fa-check text-success"></i> Confirmé
                            @elseif($p->pivot->statut === 'decline') <i class="fas fa-xmark text-danger"></i> Décliné
                            @elseif($p->pivot->statut === 'peut_etre') <i class="fas fa-question text-warning"></i> Peut-être
                            @else <i class="far fa-envelope text-muted"></i> Invité @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        @if($evenement->invitesExternes->count())
        <div class="evt-participants-subtitle mt-3">
            <i class="fas fa-envelope"></i> Invités externes ({{ $evenement->invitesExternes->count() }})
        </div>
        <div class="participants-grid">
            @foreach($evenement->invitesExternes as $inv)
                <div class="participant-item statut-{{ $inv->statut }} externe">
                    <div class="participant-avatar" style="background:#94A3B8;">
                        @if($inv->contact)
                            {{ strtoupper(substr($inv->contact->prenoms ?? $inv->contact->nom, 0, 1)) }}{{ strtoupper(substr($inv->contact->nom, 0, 1)) }}
                        @else
                            <i class="fas fa-envelope" style="font-size:.7rem;"></i>
                        @endif
                    </div>
                    <div class="participant-info">
                        <div class="participant-name">
                            {{ $inv->nom_affichage }}
                            @if($inv->contact)
                                <i class="fas fa-address-card text-info ms-1" title="Contact CRM"></i>
                            @endif
                        </div>
                        <div class="participant-status">
                            <span class="text-muted">{{ $inv->email }}</span>
                            @if($inv->statut === 'confirme') · <i class="fas fa-check text-success"></i> Confirmé
                            @elseif($inv->statut === 'decline') · <i class="fas fa-xmark text-danger"></i> Décliné
                            @elseif($inv->statut === 'peut_etre') · <i class="fas fa-question text-warning"></i> Peut-être
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </section>
    @endif

    {{-- Commentaires --}}
    @if($evenement->commentairesActifs())
    <section class="annonce-comments">
        <h3 class="annonce-section-title">
            <i class="fas fa-comment"></i> Commentaires ({{ $evenement->commentaires->count() }})
        </h3>
        @if($evenement->commentaires->where('parent_id', null)->count())
            @foreach($evenement->commentaires->where('parent_id', null) as $comment)
            <div class="comment-item">
                <div class="comment-avatar">{{ strtoupper(substr($comment->user->prenoms ?? $comment->user->name, 0, 1)) }}</div>
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
            <p class="text-muted small">Aucun commentaire.</p>
        @endif
    </section>
    @endif

    {{-- Footer --}}
    <footer class="annonce-footer">
        <div class="annonce-footer-author">
            <div class="annonce-author-avatar-lg" style="background: var(--accent);">
                {{ strtoupper(substr($evenement->auteur->prenoms ?? $evenement->auteur->name, 0, 1)) }}{{ strtoupper(substr($evenement->auteur->name, 0, 1)) }}
            </div>
            <div>
                <div class="annonce-footer-label">Organisé par</div>
                <div class="annonce-author-name">{{ $evenement->auteur->prenoms ?? '' }} {{ $evenement->auteur->name }}</div>
                <div class="annonce-author-date">
                    <i class="far fa-clock"></i>
                    Créé {{ $evenement->created_at->translatedFormat('d F Y') }}
                </div>
            </div>
        </div>
        <div class="annonce-footer-stats">
            <div class="footer-stat"><i class="fas fa-eye"></i><span>{{ $evenement->vues_count }}</span><small>vues</small></div>
            @if($evenement->likesActifs())
            <div class="footer-stat"><i class="fas fa-heart"></i><span>{{ $evenement->totalLikes() }}</span><small>j'aime</small></div>
            @endif
            <div class="footer-stat"><i class="fas fa-users"></i><span>{{ $evenement->participants->count() }}</span><small>invités</small></div>
        </div>
        <div class="annonce-footer-actions">
            <a href="{{ route('intranet.evenements.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Retour
            </a>
            @can('update:evenement')
            <a href="{{ route('intranet.evenements.edit', $evenement) }}" class="btn btn-intranet">
                <i class="fas fa-pen-to-square me-2"></i> Modifier
            </a>
            @endcan
            @can('delete:evenement')
            <form action="{{ route('intranet.evenements.destroy', $evenement) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Supprimer cet événement ?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger-soft"><i class="fas fa-trash me-2"></i> Supprimer</button>
            </form>
            @endcan
        </div>
    </footer>
</article>

{{-- Lightbox --}}
<div id="lightbox" class="lightbox" role="dialog" aria-hidden="true">
    <button type="button" class="lightbox-close"><i class="fas fa-xmark"></i></button>
    <button type="button" class="lightbox-zoom-in"><i class="fas fa-plus"></i></button>
    <button type="button" class="lightbox-zoom-out"><i class="fas fa-minus"></i></button>
    <button type="button" class="lightbox-zoom-reset"><i class="fas fa-rotate"></i></button>
    <button type="button" class="lightbox-prev"><i class="fas fa-chevron-left"></i></button>
    <button type="button" class="lightbox-next"><i class="fas fa-chevron-right"></i></button>
    <div class="lightbox-stage"><img src="" alt="" class="lightbox-img" id="lightboxImg"></div>
    <div class="lightbox-caption" id="lightboxCaption"></div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lightbox = document.getElementById('lightbox');
    const lbImg    = document.getElementById('lightboxImg');
    const lbCap    = document.getElementById('lightboxCaption');
    const triggers = document.querySelectorAll('.lightbox-trigger');
    if (!lightbox || triggers.length === 0) return;

    const gallery = Array.from(triggers).map(el => ({
        src: el.dataset.src || el.getAttribute('src'),
        name: el.dataset.name || el.getAttribute('alt') || '',
    }));
    let idx = 0, scale = 1, posX = 0, posY = 0, drag = false, sX = 0, sY = 0;

    function open(i) { idx = (i + gallery.length) % gallery.length; lbImg.src = gallery[idx].src; lbCap.textContent = gallery[idx].name; reset(); lightbox.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function close() { lightbox.classList.remove('open'); document.body.style.overflow = ''; reset(); }
    function reset() { scale = 1; posX = 0; posY = 0; apply(); }
    function apply() { lbImg.style.transform = `translate(${posX}px,${posY}px) scale(${scale})`; lbImg.style.cursor = scale > 1 ? (drag ? 'grabbing' : 'grab') : 'zoom-in'; }
    function zoom(d, cx, cy) { const ns = Math.min(Math.max(scale + d, 1), 5); if (ns === scale) return; if (cx !== undefined) { const r = lbImg.getBoundingClientRect(); posX -= (cx - r.left - r.width/2) * (ns/scale - 1); posY -= (cy - r.top - r.height/2) * (ns/scale - 1); } scale = ns; if (scale === 1) { posX = 0; posY = 0; } apply(); }

    triggers.forEach((el, i) => el.addEventListener('click', e => { e.preventDefault(); open(i); }));
    lightbox.querySelector('.lightbox-close').onclick = close;
    lightbox.querySelector('.lightbox-next').onclick = () => open(idx + 1);
    lightbox.querySelector('.lightbox-prev').onclick = () => open(idx - 1);
    lightbox.querySelector('.lightbox-zoom-in').onclick = () => zoom(0.4);
    lightbox.querySelector('.lightbox-zoom-out').onclick = () => zoom(-0.4);
    lightbox.querySelector('.lightbox-zoom-reset').onclick = reset;
    lbImg.onclick = e => { if (scale === 1) zoom(1, e.clientX, e.clientY); else reset(); };
    lightbox.addEventListener('click', e => { if (e.target === lightbox || e.target.classList.contains('lightbox-stage')) close(); });
    lightbox.addEventListener('wheel', e => { if (lightbox.classList.contains('open')) { e.preventDefault(); zoom(e.deltaY < 0 ? 0.2 : -0.2, e.clientX, e.clientY); } }, { passive: false });
    lbImg.addEventListener('mousedown', e => { if (scale > 1) { drag = true; sX = e.clientX - posX; sY = e.clientY - posY; e.preventDefault(); } });
    window.addEventListener('mousemove', e => { if (drag) { posX = e.clientX - sX; posY = e.clientY - sY; apply(); } });
    window.addEventListener('mouseup', () => { if (drag) { drag = false; apply(); } });
    document.addEventListener('keydown', e => { if (!lightbox.classList.contains('open')) return; if (e.key === 'Escape') close(); if (e.key === 'ArrowRight') open(idx + 1); if (e.key === 'ArrowLeft') open(idx - 1); });
});
</script>
@endpush
@endsection
