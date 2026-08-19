@extends('layouts.app')

@section('title', 'Actualités')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Actualités</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    {{-- En-tête --}}
    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-newspaper"></i></span>
                Actualités
            </h1>
            <p class="page-subtitle">Le magazine interne de l'entreprise</p>
        </div>
        @can('create:news')
        <a href="{{ route('intranet.news.create') }}" class="btn btn-intranet">
            <i class="fas fa-plus me-2"></i> Nouvel article
        </a>
        @endcan
    </div>

    {{-- Filtres --}}
    <form method="GET" class="filters-bar mb-4">
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un article…">
        </div>
        <select name="rubrique" class="filters-select" onchange="this.form.submit()">
            <option value="">Toutes rubriques</option>
            @foreach($rubriques as $r)
                <option value="{{ $r }}" @selected(request('rubrique') === $r)>{{ $r }}</option>
            @endforeach
        </select>
        <select name="statut" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous</option>
            <option value="aune"    @selected(request('statut') === 'aune')>À la une</option>
            <option value="actives" @selected(request('statut') === 'actives')>Actifs</option>
        </select>
        @if(request('q') || request('rubrique') || request('statut'))
        <a href="{{ route('intranet.news.index') }}" class="btn-reset">
            <i class="fas fa-xmark"></i> Réinitialiser
        </a>
        @endif
    </form>

    {{-- ───── ARTICLE À LA UNE ──────────────────────────── --}}
    @if($aLaUne)
    <article class="news-featured" style="--accent: {{ $aLaUne->couleur ?? '#7C3AED' }};">
        <a href="{{ route('intranet.news.show', $aLaUne) }}" class="news-featured-media">
            @if($aLaUne->media_principal_type === 'image' && $aLaUne->media_url)
                <img src="{{ $aLaUne->media_url }}" alt="{{ $aLaUne->title }}">
            @else
                <div class="news-featured-placeholder"><i class="fas fa-newspaper"></i></div>
            @endif
        </a>
        <div class="news-featured-body">
            <span class="news-featured-flag"><i class="fas fa-star"></i> À la une</span>
            @if($aLaUne->rubrique)
                <span class="news-rubrique">{{ $aLaUne->rubrique }}</span>
            @endif
            <h2 class="news-featured-title">
                <a href="{{ route('intranet.news.show', $aLaUne) }}">{{ $aLaUne->title }}</a>
            </h2>
            @if($aLaUne->extrait)
            <p class="news-featured-extrait">{{ $aLaUne->extrait }}</p>
            @endif
            <div class="news-featured-meta">
                <span class="news-author">
                    <span class="news-author-avatar">{{ strtoupper(substr($aLaUne->auteur->prenoms ?? $aLaUne->auteur->name, 0, 1)) }}</span>
                    {{ $aLaUne->auteur_signature ?: ($aLaUne->auteur->prenoms . ' ' . $aLaUne->auteur->name) }}
                </span>
                <span><i class="far fa-clock"></i> {{ $aLaUne->created_at->translatedFormat('d F Y') }}</span>
                @if($aLaUne->temps_lecture)
                    <span><i class="fas fa-book-open"></i> {{ $aLaUne->temps_lecture }} min de lecture</span>
                @endif
            </div>
        </div>
    </article>
    @endif

    {{-- ───── GRILLE DES ARTICLES ───────────────────────── --}}
    @if($news->count())
    <div class="news-grid">
        @foreach($news as $article)
        <article class="news-card" style="--accent: {{ $article->couleur ?? '#7C3AED' }};">
            <a href="{{ route('intranet.news.show', $article) }}" class="news-card-media">
                @if($article->media_principal_type === 'image' && $article->media_url)
                    <img src="{{ $article->media_url }}" alt="{{ $article->title }}">
                @else
                    <div class="news-card-placeholder"><i class="fas fa-newspaper"></i></div>
                @endif
                @if($article->rubrique)
                    <span class="news-card-rubrique">{{ $article->rubrique }}</span>
                @endif
            </a>
            <div class="news-card-body">
                <h3 class="news-card-title">
                    <a href="{{ route('intranet.news.show', $article) }}">{{ $article->title }}</a>
                </h3>
                @if($article->extrait)
                <p class="news-card-extrait">{{ $article->extrait }}</p>
                @endif

                @if(!empty($article->tags))
                <div class="news-tags">
                    @foreach(array_slice($article->tags, 0, 3) as $tag)
                        <span class="news-tag">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif

                <div class="news-card-meta">
                    <span class="news-author-mini">
                        <span class="news-author-avatar">{{ strtoupper(substr($article->auteur->prenoms ?? $article->auteur->name, 0, 1)) }}</span>
                        {{ $article->auteur_signature ?: ($article->auteur->prenoms . ' ' . $article->auteur->name) }}
                    </span>
                    <span class="news-card-date">{{ $article->created_at->diffForHumans() }}</span>
                </div>

                <div class="news-card-stats">
                    <span><i class="fas fa-eye"></i> {{ $article->vues_count }}</span>
                    <span><i class="fas fa-heart"></i> {{ $article->likes_count ?? 0 }}</span>
                    <span><i class="fas fa-comment"></i> {{ $article->commentaires_count ?? 0 }}</span>
                    @if($article->temps_lecture)
                        <span class="ms-auto"><i class="fas fa-book-open"></i> {{ $article->temps_lecture }} min</span>
                    @endif
                </div>
            </div>
        </article>
        @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $news->links() }}
    </div>

    @elseif(! $aLaUne)
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-newspaper"></i></div>
        <h3>Aucun article</h3>
        <p>Aucun article ne correspond à vos critères.</p>
        @can('create:news')
        <a href="{{ route('intranet.news.create') }}" class="btn btn-intranet">
            <i class="fas fa-plus me-2"></i> Rédiger le premier article
        </a>
        @endcan
    </div>
    @endif

</div>
@endsection
