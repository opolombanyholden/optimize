@extends('layouts.app')
@section('title', $ressource->titre)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.ressources.index') }}">Ressources</a></li>
    @if($ressource->parent)
        @foreach($ressource->parent->chemin_complet as $anc)
        <li class="breadcrumb-item"><a href="{{ route('intranet.ressources.index', ['dossier' => $anc->id]) }}">{{ $anc->titre }}</a></li>
        @endforeach
    @endif
    <li class="breadcrumb-item active">{{ $ressource->titre }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
<article class="contact-detail">

    <div class="contact-detail-header" style="border-top-color: {{ $ressource->couleur_icone }};">
        <div class="contact-detail-photo contact-avatar-letters" style="background: {{ $ressource->couleur_icone }}; font-size: 2rem;">
            <i class="fas {{ $ressource->icone_affichee }}"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                @if($ressource->categorie)
                    <span class="badge-soft">{{ $ressource->categorie }}</span>
                @endif
                @if($ressource->acces_restreint)
                    <span class="badge-soft">🔒 Restreint</span>
                @endif
                @if($ressource->version > 1)
                    <span class="badge-soft">v{{ $ressource->version }}</span>
                @endif
            </div>
            <h1 class="contact-detail-name">{{ $ressource->titre }}</h1>
            @if($ressource->nom_original)
            <div class="contact-detail-poste">{{ $ressource->nom_original }} · {{ $ressource->taille_humaine }}</div>
            @endif
        </div>
        <div class="contact-detail-actions">
            @if($ressource->chemin)
            <a href="{{ route('intranet.ressources.download', $ressource) }}" class="btn btn-intranet">
                <i class="fas fa-download me-2"></i> Télécharger
            </a>
            @endif
            @can('update:ressource')
            <a href="{{ route('intranet.ressources.edit', $ressource) }}" class="btn btn-light">
                <i class="fas fa-pen-to-square me-2"></i> Modifier
            </a>
            @endcan
            @can('delete:ressource')
            <form action="{{ route('intranet.ressources.destroy', $ressource) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Supprimer ?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger-soft"><i class="fas fa-trash"></i></button>
            </form>
            @endcan
        </div>
    </div>

    <div class="contact-detail-grid">
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-info-circle"></i> Informations</h4>
            <ul class="contact-info-list">
                <li><strong>Type MIME :</strong> {{ $ressource->mime_type ?? '—' }}</li>
                <li><strong>Taille :</strong> {{ $ressource->taille_humaine }}</li>
                <li><strong>Version :</strong> {{ $ressource->version }}</li>
                <li><strong>Téléchargements :</strong> {{ $ressource->telechargements_count }}</li>
                <li><strong>Vues :</strong> {{ $ressource->vues_count }}</li>
                <li><strong>Créé le :</strong> {{ $ressource->created_at->translatedFormat('d F Y à H:i') }}</li>
                <li><strong>Par :</strong> {{ $ressource->auteur?->prenoms }} {{ $ressource->auteur?->name }}</li>
            </ul>
        </div>

        @if($ressource->parent)
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-folder-tree"></i> Emplacement</h4>
            <div class="ged-breadcrumb-path">
                <a href="{{ route('intranet.ressources.index') }}">📁 Racine</a>
                @foreach($ressource->parent->chemin_complet as $anc)
                    <span>/</span>
                    <a href="{{ route('intranet.ressources.index', ['dossier' => $anc->id]) }}">{{ $anc->titre }}</a>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($ressource->tags))
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-tags"></i> Tags</h4>
            <div class="news-detail-tags" style="border:none;padding:0;">
                @foreach($ressource->tags as $t)
                    <span class="news-tag">#{{ $t }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Aperçu si image ou PDF --}}
    @if($ressource->chemin)
        @if(str_starts_with($ressource->mime_type ?? '', 'image/'))
        <div class="contact-detail-card mt-3">
            <h4 class="contact-detail-card-title"><i class="fas fa-image"></i> Aperçu</h4>
            <img src="{{ $ressource->fichier_url }}" alt="{{ $ressource->titre }}"
                 class="entity-media-img lightbox-trigger">
        </div>
        @elseif($ressource->mime_type === 'application/pdf')
        <div class="contact-detail-card mt-3">
            <h4 class="contact-detail-card-title"><i class="fas fa-file-pdf"></i> Aperçu PDF</h4>
            <iframe src="{{ $ressource->fichier_url }}" class="ged-pdf-preview"></iframe>
        </div>
        @endif
    @endif

    @if($ressource->description)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
        <div class="annonce-content p-0 border-0">{!! $ressource->description !!}</div>
    </div>
    @endif

    @include('intranet._partials.media-display', ['entity' => $ressource])

    {{-- Commentaires --}}
    @if($ressource->commentairesActifs())
    <section class="annonce-comments contact-detail-card mt-3">
        <h4 class="contact-detail-card-title">
            <i class="fas fa-comment"></i> Commentaires ({{ $ressource->commentaires->count() }})
        </h4>
        @if($ressource->commentaires->where('parent_id', null)->count())
            @foreach($ressource->commentaires->where('parent_id', null) as $comment)
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

    {{-- Footer --}}
    <footer class="annonce-footer">
        <div class="annonce-footer-author">
            <div class="annonce-author-avatar-lg" style="background: {{ $ressource->couleur_icone }};">
                <i class="fas {{ $ressource->icone_affichee }}"></i>
            </div>
            <div>
                <div class="annonce-footer-label">Déposé par</div>
                <div class="annonce-author-name">{{ $ressource->auteur?->prenoms }} {{ $ressource->auteur?->name }}</div>
                <div class="annonce-author-date"><i class="far fa-clock"></i> {{ $ressource->created_at->translatedFormat('d F Y') }}</div>
            </div>
        </div>
        <div class="annonce-footer-stats">
            <div class="footer-stat"><i class="fas fa-eye"></i><span>{{ $ressource->vues_count }}</span><small>vues</small></div>
            <div class="footer-stat"><i class="fas fa-download"></i><span>{{ $ressource->telechargements_count }}</span><small>DL</small></div>
        </div>
        <div class="annonce-footer-actions">
            <a href="{{ route('intranet.ressources.index', ['dossier' => $ressource->parent_id]) }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Retour
            </a>
        </div>
    </footer>

</article>

@include('intranet._partials.lightbox')
</div>
@endsection
