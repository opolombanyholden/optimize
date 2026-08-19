@extends('layouts.app')
@section('title', 'Templates')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Templates</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-file-circle-plus"></i></span>
                Modèles de documents
            </h1>
            <p class="page-subtitle">Templates réutilisables avec variables de substitution</p>
        </div>
        @can('create:template')
        <a href="{{ route('intranet.templates.create') }}" class="btn btn-intranet">
            <i class="fas fa-plus me-2"></i> Nouveau template
        </a>
        @endcan
    </div>

    {{-- Catégories en chips --}}
    <div class="tpl-categories mb-4">
        <a href="{{ route('intranet.templates.index') }}" class="tpl-cat-chip {{ !request('categorie') ? 'active' : '' }}">
            Tous <span class="tpl-cat-count">{{ $templates->total() }}</span>
        </a>
        @foreach($categories as $cat)
        <a href="?categorie={{ $cat->id }}" class="tpl-cat-chip {{ request('categorie') == $cat->id ? 'active' : '' }}"
           style="--cat-color: {{ $cat->couleur }};">
            <i class="fas {{ $cat->icone }}"></i>
            {{ $cat->nom }}
            <span class="tpl-cat-count">{{ $cat->templates_count }}</span>
        </a>
        @endforeach
    </div>

    <form method="GET" class="filters-bar mb-4">
        @if(request('categorie'))<input type="hidden" name="categorie" value="{{ request('categorie') }}">@endif
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un template…">
        </div>
        <select name="format" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous formats</option>
            <option value="html"    @selected(request('format')==='html')>📝 HTML (éditeur)</option>
            <option value="fichier" @selected(request('format')==='fichier')>📄 Fichier (DOCX, XLSX…)</option>
        </select>
    </form>

    @if($templates->count())
    <div class="tpl-grid">
        @foreach($templates as $tpl)
        <div class="tpl-card" style="--tpl-color: {{ $tpl->couleur }};">
            <div class="tpl-card-header">
                <div class="tpl-card-icon">
                    <i class="fas {{ $tpl->icone }}"></i>
                </div>
                @if($tpl->categorie)
                <span class="tpl-card-cat">{{ $tpl->categorie->nom }}</span>
                @endif
                <span class="tpl-card-format">{{ $tpl->est_html ? 'HTML' : strtoupper(pathinfo($tpl->nom_original ?? '', PATHINFO_EXTENSION) ?: 'DOC') }}</span>
            </div>
            <div class="tpl-card-body">
                <h3 class="tpl-card-title">
                    <a href="{{ route('intranet.templates.show', $tpl) }}">{{ $tpl->titre }}</a>
                </h3>
                @if($tpl->description)
                <p class="tpl-card-desc">{{ Str::limit(strip_tags($tpl->description), 80) }}</p>
                @endif

                @if(!empty($tpl->variables))
                <div class="tpl-card-vars">
                    @foreach(array_slice($tpl->variables, 0, 4) as $v)
                        <span class="tpl-var">@{{ $v }}</span>
                    @endforeach
                    @if(count($tpl->variables) > 4)
                        <span class="tpl-var more">+{{ count($tpl->variables) - 4 }}</span>
                    @endif
                </div>
                @endif

                <div class="tpl-card-meta">
                    <span><i class="fas fa-copy"></i> {{ $tpl->utilisations }} utilisation{{ $tpl->utilisations > 1 ? 's' : '' }}</span>
                    <span><i class="fas fa-eye"></i> {{ $tpl->vues_count }}</span>
                </div>
            </div>
            <div class="tpl-card-actions">
                <a href="{{ route('intranet.templates.utiliser', $tpl) }}" class="tpl-use-btn" title="Utiliser ce template">
                    <i class="fas fa-wand-magic-sparkles"></i> Utiliser
                </a>
                <div class="dropdown">
                    <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                        <li><a class="dropdown-item" href="{{ route('intranet.templates.show', $tpl) }}"><i class="fas fa-eye"></i> Voir</a></li>
                        @if($tpl->fichier_modele)
                        <li><a class="dropdown-item" href="{{ route('intranet.templates.download', $tpl) }}"><i class="fas fa-download"></i> Télécharger</a></li>
                        @endif
                        @can('update:template')
                        <li><a class="dropdown-item" href="{{ route('intranet.templates.edit', $tpl) }}"><i class="fas fa-pen-to-square"></i> Modifier</a></li>
                        @endcan
                        @can('delete:template')
                        <li><hr class="dropdown-divider"></li>
                        <li><form action="{{ route('intranet.templates.destroy', $tpl) }}" method="POST" onsubmit="return confirm('Supprimer ?');">@csrf @method('DELETE')<button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button></form></li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $templates->links() }}</div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-file-circle-plus"></i></div>
        <h3>Aucun template</h3>
        @can('create:template')
        <a href="{{ route('intranet.templates.create') }}" class="btn btn-intranet"><i class="fas fa-plus me-2"></i> Créer le premier</a>
        @endcan
    </div>
    @endif
</div>
@endsection
