@extends('layouts.app')
@section('title', $template->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active">{{ $template->titre }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
<article class="contact-detail">

    <div class="contact-detail-header" style="border-top-color: {{ $template->couleur }};">
        <div class="contact-detail-photo contact-avatar-letters" style="background: {{ $template->couleur }}; font-size: 1.4rem;">
            <i class="fas {{ $template->icone }}"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                @if($template->categorie)
                <span class="opp-stage-badge" style="background:{{ $template->categorie->couleur }};">{{ $template->categorie->nom }}</span>
                @endif
                <span class="badge-soft">{{ $template->est_html ? 'HTML' : 'Fichier' }}</span>
            </div>
            <h1 class="contact-detail-name">{{ $template->titre }}</h1>
            @if($template->description)
            <div class="contact-detail-poste">{{ $template->description }}</div>
            @endif
        </div>
        <div class="contact-detail-actions">
            <a href="{{ route('intranet.templates.utiliser', $template) }}" class="btn btn-intranet">
                <i class="fas fa-wand-magic-sparkles me-2"></i> Utiliser
            </a>
            @if($template->fichier_modele)
            <a href="{{ route('intranet.templates.download', $template) }}" class="btn btn-light">
                <i class="fas fa-download me-2"></i> Télécharger
            </a>
            @endif
            @can('update:template')
            <a href="{{ route('intranet.templates.edit', $template) }}" class="btn btn-light"><i class="fas fa-pen-to-square"></i></a>
            @endcan
        </div>
    </div>

    <div class="contact-detail-grid">
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-info-circle"></i> Infos</h4>
            <ul class="contact-info-list">
                <li><strong>Format :</strong> {{ $template->est_html ? 'Éditeur HTML' : 'Fichier (' . ($template->mime_type ?? '—') . ')' }}</li>
                @if($template->fichier_modele)<li><strong>Fichier :</strong> {{ $template->nom_original }}</li>@endif
                @if($template->taille)<li><strong>Taille :</strong> {{ $template->taille_humaine }}</li>@endif
                <li><strong>Utilisations :</strong> {{ $template->utilisations }}</li>
                <li><strong>Vues :</strong> {{ $template->vues_count }}</li>
                <li><strong>Auteur :</strong> {{ $template->auteur?->prenoms }} {{ $template->auteur?->name }}</li>
            </ul>
        </div>

        @if(!empty($template->variables))
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-wand-magic-sparkles"></i> Variables ({{ count($template->variables) }})</h4>
            <div class="tpl-card-vars" style="flex-wrap:wrap;">
                @foreach($template->variables as $v)
                    <span class="tpl-var">@{{ $v }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($template->tags))
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-tags"></i> Tags</h4>
            <div class="news-detail-tags" style="border:none;padding:0;">
                @foreach($template->tags as $t)<span class="news-tag">#{{ $t }}</span>@endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Aperçu du contenu HTML --}}
    @if($template->est_html && $template->contenu)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-eye"></i> Aperçu du template</h4>
        <div class="tpl-preview-box annonce-content p-0 border-0">{!! $template->contenu !!}</div>
    </div>
    @endif

    <footer class="annonce-footer">
        <div class="annonce-footer-stats">
            <div class="footer-stat"><i class="fas fa-copy"></i><span>{{ $template->utilisations }}</span><small>utilisations</small></div>
            <div class="footer-stat"><i class="fas fa-eye"></i><span>{{ $template->vues_count }}</span><small>vues</small></div>
        </div>
        <div class="annonce-footer-actions">
            <a href="{{ route('intranet.templates.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i> Retour</a>
        </div>
    </footer>

</article>
</div>
@endsection
