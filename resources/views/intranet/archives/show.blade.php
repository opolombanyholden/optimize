@extends('layouts.app')
@section('title', $archive->reference . ' — ' . $archive->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.archives.index') }}">Archives</a></li>
    <li class="breadcrumb-item active">{{ $archive->reference }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
<article class="contact-detail">

    <div class="contact-detail-header" style="border-top-color: {{ $archive->statut_couleur }};">
        <div class="contact-detail-photo contact-avatar-letters" style="background: {{ $archive->couleur_icone }}; font-size: 1.6rem;">
            <i class="fas {{ $archive->icone }}"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                <code class="opp-ref">{{ $archive->reference }}</code>
                <span class="opp-stage-badge" style="background:{{ $archive->statut_couleur }};">{{ $archive->statut_libelle }}</span>
                @if($archive->is_confidentiel)<span class="badge-soft">🔒 Confidentiel</span>@endif
                @if($archive->nature)<span class="badge-soft">{{ $archive->nature }}</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $archive->titre }}</h1>
            @if($archive->nom_original)
            <div class="contact-detail-poste">{{ $archive->nom_original }} · {{ $archive->taille_humaine }}</div>
            @endif
        </div>
        <div class="contact-detail-actions">
            @if($archive->fichier)
            <a href="{{ route('intranet.archives.download', $archive) }}" class="btn btn-intranet"><i class="fas fa-download me-2"></i> Télécharger</a>
            @endif
            @can('update:archive')
            <a href="{{ route('intranet.archives.edit', $archive) }}" class="btn btn-light"><i class="fas fa-pen-to-square me-2"></i> Modifier</a>
            @endcan
            @can('delete:archive')
            <form action="{{ route('intranet.archives.destroy', $archive) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?');">
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
                <li><strong>Format :</strong> {{ $archive->mime_type ?? '—' }}</li>
                <li><strong>Taille :</strong> {{ $archive->taille_humaine }}</li>
                <li><strong>Téléchargements :</strong> {{ $archive->telechargements_count }}</li>
                <li><strong>Vues :</strong> {{ $archive->vues_count }}</li>
            </ul>
        </div>
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="far fa-calendar"></i> Dates & conservation</h4>
            <ul class="contact-info-list">
                @if($archive->date_document)<li><strong>Date document :</strong> {{ $archive->date_document->format('d/m/Y') }}</li>@endif
                <li><strong>Archivé le :</strong> {{ $archive->date_archivage?->format('d/m/Y') }}</li>
                @if($archive->duree_conservation_mois)<li><strong>Conservation :</strong> {{ $archive->duree_conservation_mois }} mois</li>@endif
                @if($archive->date_destruction_prevue)
                <li class="{{ $archive->date_destruction_prevue->lt(now()) ? 'text-danger fw-bold' : '' }}">
                    <strong>Destruction prévue :</strong> {{ $archive->date_destruction_prevue->format('d/m/Y') }}
                </li>
                @endif
            </ul>
        </div>
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-location-dot"></i> Localisation</h4>
            <ul class="contact-info-list">
                @if($archive->dossier)<li><strong>Dossier :</strong> {{ $archive->dossier->nom }}</li>@endif
                @if($archive->lieu_physique)<li><strong>Lieu :</strong> {{ $archive->lieu_physique }}</li>@endif
                @if($archive->code_barre)<li><strong>Code-barre :</strong> {{ $archive->code_barre }}</li>@endif
                <li><strong>Par :</strong> {{ $archive->auteur?->prenoms }} {{ $archive->auteur?->name }}</li>
            </ul>
        </div>
    </div>

    {{-- Aperçu PDF --}}
    @if($archive->fichier && $archive->mime_type === 'application/pdf')
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-file-pdf"></i> Aperçu</h4>
        <iframe src="{{ $archive->fichier_url }}" class="ged-pdf-preview"></iframe>
    </div>
    @elseif($archive->fichier && str_starts_with($archive->mime_type ?? '', 'image/'))
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-image"></i> Aperçu</h4>
        <img src="{{ $archive->fichier_url }}" alt="" class="entity-media-img lightbox-trigger">
    </div>
    @endif

    @if($archive->description)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
        <div class="annonce-content p-0 border-0">{!! $archive->description !!}</div>
    </div>
    @endif

    @if(!empty($archive->tags))
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-tags"></i> Tags</h4>
        <div class="news-detail-tags" style="border:none;padding:0;">
            @foreach($archive->tags as $t)<span class="news-tag">#{{ $t }}</span>@endforeach
        </div>
    </div>
    @endif

    @include('intranet._partials.media-display', ['entity' => $archive])

    @if($archive->commentairesActifs())
    <section class="annonce-comments contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-comment"></i> Commentaires ({{ $archive->commentaires->count() }})</h4>
        @forelse($archive->commentaires->where('parent_id', null) as $comment)
        <div class="comment-item">
            <div class="comment-avatar">{{ strtoupper(substr($comment->user->prenoms ?? $comment->user->name, 0, 1)) }}</div>
            <div class="comment-body">
                <div class="comment-header"><strong>{{ $comment->user->prenoms ?? '' }} {{ $comment->user->name }}</strong><span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span></div>
                <div class="comment-text">{{ $comment->contenu }}</div>
            </div>
        </div>
        @empty
        <p class="text-muted small mb-0">Aucun commentaire.</p>
        @endforelse
    </section>
    @endif

    <footer class="annonce-footer">
        <div class="annonce-footer-stats">
            <div class="footer-stat"><i class="fas fa-eye"></i><span>{{ $archive->vues_count }}</span><small>vues</small></div>
            <div class="footer-stat"><i class="fas fa-download"></i><span>{{ $archive->telechargements_count }}</span><small>DL</small></div>
        </div>
        <div class="annonce-footer-actions">
            <a href="{{ route('intranet.archives.index', ['dossier' => $archive->dossier_id]) }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i> Retour</a>
        </div>
    </footer>

</article>
@include('intranet._partials.lightbox')
</div>
@endsection
