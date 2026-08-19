@extends('layouts.app')
@section('title', 'Wiki')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.wiki.index') }}">Wiki</a></li>
    @if($categorie)
        @foreach($categorie->chemin_complet as $anc)
            @if(!$loop->last)
            <li class="breadcrumb-item"><a href="{{ route('intranet.wiki.index', ['categorie' => $anc->id]) }}">{{ $anc->nom }}</a></li>
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
                <span class="page-title-icon"><i class="fas fa-book-open"></i></span>
                {{ $categorie ? $categorie->nom : 'Base de connaissances' }}
            </h1>
            <p class="page-subtitle">{{ $categorie?->description ?? 'Wiki collaboratif de l\'entreprise' }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($categorie)
            <a href="{{ route('intranet.wiki.index', ['categorie' => $categorie->parent_id]) }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Remonter
            </a>
            @endif
            @can('create:wiki')
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalCategorie">
                <i class="fas fa-folder-plus me-2"></i> Catégorie
            </button>
            <a href="{{ route('intranet.wiki.create', ['categorie' => $categorie?->id]) }}" class="btn btn-intranet">
                <i class="fas fa-plus me-2"></i> Nouvel article
            </a>
            @endcan
        </div>
    </div>

    <div class="courrier-stats mb-4">
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#7C3AED;"><i class="fas fa-file-lines"></i></div>
            <div><div class="csc-value">{{ $stats['articles'] }}</div><div class="csc-label">Articles</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#0891B2;"><i class="fas fa-folder-tree"></i></div>
            <div><div class="csc-value">{{ $stats['categories'] }}</div><div class="csc-label">Catégories</div></div>
        </div>
    </div>

    <form method="GET" class="filters-bar mb-4">
        @if($categorie)<input type="hidden" name="categorie" value="{{ $categorie->id }}">@endif
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher dans le wiki…">
        </div>
        <label class="filters-toggle">
            <input type="checkbox" name="epingles" value="1" @checked(request()->boolean('epingles')) onchange="this.form.submit()">
            Épinglés
        </label>
    </form>

    {{-- Sous-catégories --}}
    @if($sousCategories->count() && !request()->filled('q'))
    <div class="media-albums-grid mb-4">
        @foreach($sousCategories as $sc)
        <div class="media-album-card" style="--album-color: {{ $sc->couleur ?? '#7C3AED' }};">
            <a href="{{ route('intranet.wiki.index', ['categorie' => $sc->id]) }}" class="media-album-link">
                <div class="media-album-cover">
                    <i class="fas {{ $sc->icone ?? 'fa-book-open' }}"></i>
                </div>
                <div class="media-album-body">
                    <h4 class="media-album-name">{{ $sc->nom }}</h4>
                    <span class="media-album-count">{{ $sc->articles_count }} article{{ $sc->articles_count > 1 ? 's' : '' }}</span>
                </div>
            </a>
            <div class="media-album-actions">
                <div class="dropdown">
                    <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                        @can('delete:wiki')
                        <li><form action="{{ route('intranet.wiki.categories.destroy', $sc) }}" method="POST" onsubmit="return confirm('Supprimer ?');">@csrf @method('DELETE')<button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button></form></li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Articles --}}
    @if($articles->count())
    <div class="wiki-articles-list">
        @foreach($articles as $a)
        <a href="{{ route('intranet.wiki.show', $a) }}" class="wiki-article-item">
            <div class="wiki-article-icon" style="background: color-mix(in srgb, {{ $a->categorie?->couleur ?? '#7C3AED' }} 12%, #fff); color: {{ $a->categorie?->couleur ?? '#7C3AED' }};">
                <i class="fas {{ $a->categorie?->icone ?? 'fa-file-lines' }}"></i>
            </div>
            <div class="wiki-article-body">
                <div class="wiki-article-title">
                    @if($a->is_epingle)<i class="fas fa-thumbtack text-warning me-1" style="font-size:.7rem;"></i>@endif
                    {{ $a->titre }}
                </div>
                @if($a->extrait)
                <div class="wiki-article-extrait">{{ $a->extrait }}</div>
                @endif
                <div class="wiki-article-meta">
                    @if($a->categorie)<span><i class="fas fa-folder"></i> {{ $a->categorie->nom }}</span>@endif
                    <span><i class="fas fa-user"></i> {{ $a->auteur?->prenoms }}</span>
                    <span><i class="fas fa-eye"></i> {{ $a->vues }}</span>
                    @if($a->temps_lecture)<span><i class="fas fa-book-open"></i> {{ $a->temps_lecture }} min</span>@endif
                    <span>v{{ $a->version }}</span>
                </div>
            </div>
            <div class="wiki-article-date">
                {{ $a->updated_at->diffForHumans() }}
            </div>
        </a>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $articles->links() }}</div>
    @elseif(!$sousCategories->count())
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-book-open"></i></div>
        <h3>{{ $categorie ? 'Catégorie vide' : 'Wiki vide' }}</h3>
        @can('create:wiki')
        <a href="{{ route('intranet.wiki.create', ['categorie' => $categorie?->id]) }}" class="btn btn-intranet"><i class="fas fa-plus me-2"></i> Rédiger le premier article</a>
        @endcan
    </div>
    @endif
</div>

{{-- Modale catégorie --}}
@can('create:wiki')
<div class="modal fade" id="modalCategorie" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.wiki.categories.store') }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Nouvelle catégorie</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="parent_id" value="{{ $categorie?->id }}">
                <div class="mb-3"><label class="form-label">Nom <span class="text-danger">*</span></label><input type="text" name="nom" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Icône</label><input type="text" name="icone" class="form-control" placeholder="fa-book-open"></div>
                    <div class="col-md-6"><label class="form-label">Couleur</label><input type="color" name="couleur" class="form-control form-control-color" value="#7C3AED"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-intranet"><i class="fas fa-folder-plus me-2"></i> Créer</button></div>
        </form>
    </div>
</div>
@endcan
@endsection
