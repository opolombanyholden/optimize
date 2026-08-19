@extends('layouts.app')

@section('title', 'Annonces')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Annonces</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    {{-- En-tête de page --}}
    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-bullhorn"></i></span>
                Annonces
            </h1>
            <p class="page-subtitle">Communiquez avec vos collaborateurs en temps réel</p>
        </div>
        @can('create:annonce')
        <a href="{{ route('intranet.annonces.create') }}" class="btn btn-intranet">
            <i class="fas fa-plus me-2"></i> Nouvelle annonce
        </a>
        @endcan
    </div>

    {{-- Filtres --}}
    <form method="GET" class="filters-bar mb-4">
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher une annonce…">
        </div>
        <select name="categorie" class="filters-select" onchange="this.form.submit()">
            <option value="">Toutes catégories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" @selected(request('categorie') === $cat)>{{ $cat }}</option>
            @endforeach
        </select>
        <select name="statut" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous statuts</option>
            <option value="actives"   @selected(request('statut') === 'actives')>Actives</option>
            <option value="urgentes"  @selected(request('statut') === 'urgentes')>Urgentes</option>
            <option value="epinglees" @selected(request('statut') === 'epinglees')>Épinglées</option>
        </select>
        @if(request('q') || request('categorie') || request('statut'))
        <a href="{{ route('intranet.annonces.index') }}" class="btn-reset">
            <i class="fas fa-xmark"></i> Réinitialiser
        </a>
        @endif
    </form>

    {{-- Grille des annonces --}}
    @if($annonces->count())
    <div class="annonces-grid">
        @foreach($annonces as $annonce)
        <article class="annonce-card" style="--accent: {{ $annonce->couleur ?? '#7C3AED' }};">

            {{-- Bandeau média --}}
            <a href="{{ route('intranet.annonces.show', $annonce) }}" class="annonce-card-media">
                @if($annonce->media_principal_type === 'image' && $annonce->media_url)
                    <img src="{{ $annonce->media_url }}" alt="{{ $annonce->title }}">
                @elseif($annonce->media_principal_type === 'video' && $annonce->media_url)
                    <div class="annonce-card-video">
                        <i class="fas fa-circle-play"></i>
                    </div>
                @else
                    <div class="annonce-card-placeholder">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                @endif

                {{-- Badges --}}
                <div class="annonce-card-badges">
                    @if($annonce->epingle)
                        <span class="badge-pin"><i class="fas fa-thumbtack"></i></span>
                    @endif
                    @if($annonce->is_urgent)
                        <span class="badge-urgent"><i class="fas fa-bolt"></i> URGENT</span>
                    @endif
                </div>
            </a>

            {{-- Corps --}}
            <div class="annonce-card-body">
                @if($annonce->categorie)
                <span class="annonce-card-cat">{{ $annonce->categorie }}</span>
                @endif

                <h3 class="annonce-card-title">
                    <a href="{{ route('intranet.annonces.show', $annonce) }}">{{ $annonce->title }}</a>
                </h3>

                @if($annonce->extrait)
                <p class="annonce-card-extrait">{{ $annonce->extrait }}</p>
                @endif

                <div class="annonce-card-meta">
                    <div class="annonce-card-author">
                        <span class="annonce-author-avatar">
                            {{ strtoupper(substr($annonce->auteur->prenoms ?? $annonce->auteur->name, 0, 1)) }}
                        </span>
                        <span>{{ $annonce->auteur->prenoms ?? '' }} {{ $annonce->auteur->name }}</span>
                    </div>
                    <span class="annonce-card-date">
                        <i class="fas fa-clock"></i>
                        {{ $annonce->created_at->diffForHumans() }}
                    </span>
                </div>

                <div class="annonce-card-stats">
                    <span><i class="fas fa-eye"></i> {{ $annonce->vues_count }}</span>
                    <span><i class="fas fa-heart"></i> {{ $annonce->likes_count ?? 0 }}</span>
                    <span><i class="fas fa-comment"></i> {{ $annonce->commentaires_count ?? 0 }}</span>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $annonces->links() }}
    </div>

    @else
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-bullhorn"></i></div>
        <h3>Aucune annonce</h3>
        <p>Aucune annonce ne correspond à vos critères de recherche.</p>
        @can('create:annonce')
        <a href="{{ route('intranet.annonces.create') }}" class="btn btn-intranet">
            <i class="fas fa-plus me-2"></i> Créer la première annonce
        </a>
        @endcan
    </div>
    @endif

</div>
@endsection
