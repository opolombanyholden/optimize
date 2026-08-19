@extends('layouts.app')
@section('title', $media->titre)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.mediatheque.index') }}">Médiathèque</a></li>
    <li class="breadcrumb-item active">{{ $media->titre }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
<article class="media-detail" style="--media-color: {{ $media->couleur }};">

    {{-- Aperçu plein ──────────────────────────────────── --}}
    <div class="media-detail-preview">
        @if($media->est_image && $media->fichier_url)
            <img src="{{ $media->fichier_url }}" alt="{{ $media->titre }}"
                 class="media-detail-img lightbox-trigger" data-name="{{ $media->titre }}">
        @elseif($media->est_externe && $media->embed_url)
            {{-- Vidéo externe embed (YouTube, Dailymotion, Vimeo) --}}
            <div class="media-embed-container">
                <iframe src="{{ $media->embed_url }}"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        class="media-embed-iframe"></iframe>
            </div>
        @elseif($media->est_video && $media->fichier_url)
            <video src="{{ $media->fichier_url }}" controls class="media-detail-img"></video>
        @elseif($media->type === 'audio' && $media->fichier_url)
            <div class="media-detail-audio">
                <i class="fas fa-music"></i>
                <audio src="{{ $media->fichier_url }}" controls class="w-100 mt-3"></audio>
            </div>
        @else
            <div class="media-detail-placeholder">
                <i class="fas {{ $media->icone }}"></i>
            </div>
        @endif
    </div>

    {{-- Infos ─────────────────────────────────────────── --}}
    <div class="contact-detail-header" style="border-top-color: var(--media-color);">
        <div class="contact-detail-photo contact-avatar-letters" style="background: var(--media-color); font-size: 1.4rem;">
            <i class="fas {{ $media->icone }}"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                <span class="opp-stage-badge" style="background: var(--media-color);">{{ ucfirst($media->type) }}</span>
                @if($media->album)
                    <span class="badge-soft"><i class="fas fa-layer-group"></i> {{ $media->album }}</span>
                @endif
            </div>
            <h1 class="contact-detail-name">{{ $media->titre }}</h1>
            <div class="contact-detail-poste">{{ $media->nom_original }}</div>
        </div>
        <div class="contact-detail-actions">
            {{-- Bouton embed --}}
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalEmbed" title="Intégrer">
                <i class="fas fa-code me-2"></i> Embed
            </button>
            @if(!$media->est_externe)
            <a href="{{ route('intranet.mediatheque.download', $media) }}" class="btn btn-intranet">
                <i class="fas fa-download me-2"></i> Télécharger
            </a>
            @endif
            @can('update:media')
            <a href="{{ route('intranet.mediatheque.edit', $media) }}" class="btn btn-light">
                <i class="fas fa-pen-to-square me-2"></i> Modifier
            </a>
            @endcan
            @can('delete:media')
            <form action="{{ route('intranet.mediatheque.destroy', $media) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Supprimer ?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger-soft"><i class="fas fa-trash"></i></button>
            </form>
            @endcan
        </div>
    </div>

    <div class="contact-detail-grid">
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-info-circle"></i> Propriétés</h4>
            <ul class="contact-info-list">
                <li><strong>Format :</strong> {{ $media->mime_type }}</li>
                <li><strong>Taille :</strong> {{ $media->taille_humaine }}</li>
                @if($media->dimensions)<li><strong>Dimensions :</strong> {{ $media->dimensions }} px</li>@endif
                @if($media->duree_humaine)<li><strong>Durée :</strong> {{ $media->duree_humaine }}</li>@endif
                <li><strong>Téléchargements :</strong> {{ $media->telechargements_count }}</li>
                <li><strong>Vues :</strong> {{ $media->vues_count }}</li>
            </ul>
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-user"></i> Auteur</h4>
            <ul class="contact-info-list">
                <li>{{ $media->auteur?->prenoms }} {{ $media->auteur?->name }}</li>
                <li>Ajouté le {{ $media->created_at->translatedFormat('d F Y à H:i') }}</li>
            </ul>
        </div>

        @if(!empty($media->tags))
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-tags"></i> Tags</h4>
            <div class="news-detail-tags" style="border:none;padding:0;">
                @foreach($media->tags as $t)
                    <span class="news-tag">#{{ $t }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    @if($media->description)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
        <div class="annonce-content p-0 border-0">{!! $media->description !!}</div>
    </div>
    @endif

    @if($media->commentairesActifs())
    <section class="annonce-comments contact-detail-card mt-3">
        <h4 class="contact-detail-card-title">
            <i class="fas fa-comment"></i> Commentaires ({{ $media->commentaires->count() }})
        </h4>
        @if($media->commentaires->where('parent_id', null)->count())
            @foreach($media->commentaires->where('parent_id', null) as $comment)
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
            <p class="text-muted small mb-0">Aucun commentaire.</p>
        @endif
    </section>
    @endif

    <footer class="annonce-footer">
        <div class="annonce-footer-stats">
            <div class="footer-stat"><i class="fas fa-eye"></i><span>{{ $media->vues_count }}</span><small>vues</small></div>
            <div class="footer-stat"><i class="fas fa-download"></i><span>{{ $media->telechargements_count }}</span><small>DL</small></div>
            @if($media->likesActifs())
            <div class="footer-stat"><i class="fas fa-heart"></i><span>{{ $media->totalLikes() }}</span><small>j'aime</small></div>
            @endif
        </div>
        <div class="annonce-footer-actions">
            <a href="{{ route('intranet.mediatheque.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i> Retour</a>
        </div>
    </footer>

</article>

{{-- ═══ MODALE EMBED ════════════════════════════════════ --}}
<div class="modal fade" id="modalEmbed" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-code me-2"></i> Intégrer ce média</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Aperçu de l'embed --}}
                <div class="embed-preview-box mb-3">
                    @if($media->est_image && $media->fichier_url)
                        <img src="{{ $media->fichier_url }}" alt="{{ $media->titre }}" style="max-width:100%;max-height:300px;border-radius:9px;">
                    @elseif($media->est_externe && $media->embed_url)
                        <div class="media-embed-container" style="max-width:560px;">
                            <iframe src="{{ $media->embed_url }}" frameborder="0" allowfullscreen class="media-embed-iframe"></iframe>
                        </div>
                    @elseif($media->est_video && $media->fichier_url)
                        <video src="{{ $media->fichier_url }}" controls style="max-width:100%;max-height:300px;border-radius:9px;"></video>
                    @elseif($media->type === 'audio' && $media->fichier_url)
                        <audio src="{{ $media->fichier_url }}" controls class="w-100"></audio>
                    @endif
                </div>

                {{-- Code iframe --}}
                <div class="mb-3">
                    <label class="form-label">Code d'intégration (iframe)</label>
                    @php
                        if ($media->est_externe && $media->embed_url) {
                            $embedCode = '<iframe src="' . $media->embed_url . '" width="560" height="315" frameborder="0" allowfullscreen></iframe>';
                        } elseif ($media->est_image && $media->fichier_url) {
                            $embedCode = '<img src="' . $media->fichier_url . '" alt="' . e($media->titre) . '" style="max-width:100%;">';
                        } elseif ($media->est_video && $media->fichier_url) {
                            $embedCode = '<video src="' . $media->fichier_url . '" controls width="560"></video>';
                        } elseif ($media->type === 'audio' && $media->fichier_url) {
                            $embedCode = '<audio src="' . $media->fichier_url . '" controls></audio>';
                        } else {
                            $embedCode = '<a href="' . route('intranet.mediatheque.show', $media) . '">' . e($media->titre) . '</a>';
                        }
                    @endphp
                    <div class="embed-code-box">
                        <code id="embedCode">{{ $embedCode }}</code>
                        <button type="button" class="embed-copy-btn" onclick="copyEmbed()" title="Copier">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                {{-- Lien direct --}}
                <div class="mb-3">
                    <label class="form-label">Lien direct</label>
                    <div class="embed-code-box">
                        <code id="embedLink">{{ $media->est_externe ? $media->url_externe : ($media->fichier_url ?? route('intranet.mediatheque.show', $media)) }}</code>
                        <button type="button" class="embed-copy-btn" onclick="copyLink()" title="Copier">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                {{-- Options de taille (pour iframe) --}}
                @if($media->est_externe || $media->est_video)
                <div class="mb-0">
                    <label class="form-label">Taille</label>
                    <div class="embed-size-btns">
                        <button type="button" class="embed-size-btn active" onclick="setEmbedSize(560,315)">560×315</button>
                        <button type="button" class="embed-size-btn" onclick="setEmbedSize(853,480)">853×480</button>
                        <button type="button" class="embed-size-btn" onclick="setEmbedSize(1280,720)">1280×720</button>
                        <button type="button" class="embed-size-btn" onclick="setEmbedSize('100%',450)">Responsive</button>
                    </div>
                </div>
                @endif

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

@include('intranet._partials.lightbox')

@push('scripts')
<script>
function copyEmbed() {
    navigator.clipboard.writeText(document.getElementById('embedCode').textContent);
    showCopyFeedback('embedCode');
}
function copyLink() {
    navigator.clipboard.writeText(document.getElementById('embedLink').textContent);
    showCopyFeedback('embedLink');
}
function showCopyFeedback(id) {
    const el = document.getElementById(id);
    const original = el.style.background;
    el.style.background = '#DCFCE7';
    setTimeout(() => el.style.background = original, 800);
}
@if($media->est_externe || $media->est_video)
function setEmbedSize(w, h) {
    const code = document.getElementById('embedCode');
    let html = code.textContent;
    html = html.replace(/width="[^"]*"/, 'width="' + w + '"');
    html = html.replace(/height="[^"]*"/, 'height="' + h + '"');
    code.textContent = html;
    document.querySelectorAll('.embed-size-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
}
@endif
</script>
@endpush
</div>
@endsection
