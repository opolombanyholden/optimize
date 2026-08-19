@extends('layouts.app')
@section('title', 'Ressources documentaires')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.ressources.index') }}">Ressources</a></li>
    @if($parent)
        @foreach($parent->chemin_complet as $ancestor)
            @if(!$loop->last)
            <li class="breadcrumb-item"><a href="{{ route('intranet.ressources.index', ['dossier' => $ancestor->id]) }}">{{ $ancestor->titre }}</a></li>
            @else
            <li class="breadcrumb-item active">{{ $ancestor->titre }}</li>
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
                <span class="page-title-icon"><i class="fas fa-folder-open"></i></span>
                {{ $parent ? $parent->titre : 'Ressources documentaires' }}
            </h1>
            <p class="page-subtitle">{{ $parent ? 'Contenu du dossier' : 'GED — Gestion électronique des documents' }}</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            {{-- Mode d'affichage --}}
            <div class="ged-view-toggle" id="gedViewToggle">
                <button class="ged-view-btn active" data-view="list" title="Liste"><i class="fas fa-list"></i></button>
                <button class="ged-view-btn" data-view="grid" title="Grille"><i class="fas fa-grip"></i></button>
                <button class="ged-view-btn" data-view="folder" title="Dossiers"><i class="fas fa-folder"></i></button>
            </div>
            @can('create:ressource')
            <a href="{{ route('intranet.ressources.create', ['type' => 'dossier', 'parent' => $parent?->id]) }}" class="btn btn-light">
                <i class="fas fa-folder-plus me-2"></i> Dossier
            </a>
            <a href="{{ route('intranet.ressources.create', ['type' => 'fichier', 'parent' => $parent?->id]) }}" class="btn btn-intranet">
                <i class="fas fa-file-arrow-up me-2"></i> Fichier
            </a>
            @endcan
        </div>
    </div>

    {{-- Stats --}}
    <div class="courrier-stats mb-4">
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#F59E0B;"><i class="fas fa-folder"></i></div>
            <div><div class="csc-value">{{ $stats['dossiers'] }}</div><div class="csc-label">Dossiers</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#7C3AED;"><i class="fas fa-file"></i></div>
            <div><div class="csc-value">{{ $stats['fichiers'] }}</div><div class="csc-label">Fichiers</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#0891B2;"><i class="fas fa-database"></i></div>
            <div><div class="csc-value">{{ $stats['taille'] }}</div><div class="csc-label">Taille totale</div></div>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="filters-bar mb-4">
        @if($parent)
        <input type="hidden" name="dossier" value="{{ $parent->id }}">
        @endif
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un document…">
        </div>
        <select name="categorie" class="filters-select" onchange="this.form.submit()">
            <option value="">Toutes catégories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" @selected(request('categorie') === $cat)>{{ $cat }}</option>
            @endforeach
        </select>
        @if($parent)
        <a href="{{ route('intranet.ressources.index', ['dossier' => $parent->parent_id]) }}" class="btn btn-light">
            <i class="fas fa-arrow-left me-2"></i> Remonter
        </a>
        @endif
    </form>

    {{-- Conteneur d'éléments — vues switchables --}}
    @if($items->count())
    <div id="gedContainer" class="ged-container view-list" data-parent="{{ $parent?->id ?? '' }}">

        @foreach($items as $item)
        <div class="ged-element {{ $item->est_dossier ? 'is-dossier' : 'is-fichier' }}"
             data-id="{{ $item->id }}"
             data-type="{{ $item->type }}"
             draggable="{{ $item->est_dossier ? 'false' : 'true' }}">

            {{-- LIEN PRINCIPAL --}}
            <a href="{{ $item->est_dossier ? route('intranet.ressources.index', ['dossier' => $item->id]) : route('intranet.ressources.show', $item) }}"
               class="ged-el-link">

                {{-- Aperçu / icône --}}
                <div class="ged-el-preview" style="--icon-color: {{ $item->couleur_icone }};">
                    @if($item->est_dossier && $item->icone && str_starts_with($item->icone, 'fa-'))
                        <i class="fas {{ $item->icone }}" style="color: {{ $item->couleur_icone }};"></i>
                    @elseif($item->est_dossier && $item->chemin)
                        <img src="{{ asset('storage/' . $item->chemin) }}" alt="" class="ged-el-thumb">
                    @elseif(!$item->est_dossier && str_starts_with($item->mime_type ?? '', 'image/') && $item->chemin)
                        <img src="{{ asset('storage/' . $item->chemin) }}" alt="" class="ged-el-thumb">
                    @else
                        <i class="fas {{ $item->icone_affichee }}"></i>
                    @endif
                </div>

                {{-- Infos --}}
                <div class="ged-el-info">
                    <div class="ged-el-name">{{ $item->titre }}</div>
                    <div class="ged-el-meta">
                        @if($item->est_dossier)
                            {{ $item->enfants_count ?? $item->enfants->count() }} éléments
                        @else
                            {{ $item->taille_humaine }}
                            @if($item->version > 1) · v{{ $item->version }} @endif
                        @endif
                    </div>
                    @if($item->categorie)
                    <span class="ged-item-cat">{{ $item->categorie }}</span>
                    @endif
                </div>

                {{-- Colonne droite (vue liste) --}}
                <div class="ged-el-right">
                    @if(!$item->est_dossier)
                        <span class="ged-download"><i class="fas fa-download"></i> {{ $item->telechargements_count }}</span>
                    @endif
                    <span class="ged-date">{{ $item->created_at->format('d/m/Y') }}</span>
                </div>
            </a>

            {{-- Actions contextuelles --}}
            <div class="ged-el-actions">
                <div class="dropdown">
                    <button class="ged-action-btn" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                        @if($item->est_dossier)
                        <li><a class="dropdown-item" href="{{ route('intranet.ressources.index', ['dossier' => $item->id]) }}">
                            <i class="fas fa-folder-open"></i> Ouvrir
                        </a></li>
                        @else
                        <li><a class="dropdown-item" href="{{ route('intranet.ressources.show', $item) }}">
                            <i class="fas fa-eye"></i> Voir
                        </a></li>
                        @if($item->chemin)
                        <li><a class="dropdown-item" href="{{ route('intranet.ressources.download', $item) }}">
                            <i class="fas fa-download"></i> Télécharger
                        </a></li>
                        @endif
                        @endif
                        @can('update:ressource')
                        <li><a class="dropdown-item" href="{{ route('intranet.ressources.edit', $item) }}">
                            <i class="fas fa-pen-to-square"></i> Modifier
                        </a></li>
                        @endcan
                        @can('delete:ressource')
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('intranet.ressources.destroy', $item) }}" method="POST"
                                  onsubmit="return confirm('Supprimer {{ $item->est_dossier ? 'ce dossier et tout son contenu' : 'ce fichier' }} ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </li>
                        @endcan
                    </ul>
                </div>
            </div>

        </div>
        @endforeach

    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $items->links() }}</div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas {{ $parent ? 'fa-folder-open' : 'fa-folder-tree' }}"></i></div>
        <h3>{{ $parent ? 'Dossier vide' : 'Aucun document' }}</h3>
        <p>{{ $parent ? 'Ce dossier ne contient aucun fichier.' : 'Commencez par créer un dossier ou ajouter un fichier.' }}</p>
        @can('create:ressource')
        <div class="d-flex gap-2 justify-content-center">
            <a href="{{ route('intranet.ressources.create', ['type' => 'dossier', 'parent' => $parent?->id]) }}" class="btn btn-light">
                <i class="fas fa-folder-plus me-2"></i> Dossier
            </a>
            <a href="{{ route('intranet.ressources.create', ['type' => 'fichier', 'parent' => $parent?->id]) }}" class="btn btn-intranet">
                <i class="fas fa-file-arrow-up me-2"></i> Fichier
            </a>
        </div>
        @endcan
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('gedContainer');
    const csrf      = document.querySelector('meta[name="csrf-token"]')?.content;

    // ── Toggle vue : list / grid / folder ──
    const viewBtns = document.querySelectorAll('.ged-view-btn');
    const savedView = localStorage.getItem('ged-view') || 'list';

    function setView(mode) {
        if (!container) return;
        container.className = 'ged-container view-' + mode;
        viewBtns.forEach(b => b.classList.toggle('active', b.dataset.view === mode));
        localStorage.setItem('ged-view', mode);
    }
    setView(savedView);
    viewBtns.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));

    // ── Drag & drop : déplacer un fichier dans un dossier ──
    if (!container) return;

    const dossierEls = container.querySelectorAll('.ged-element.is-dossier');
    const fichierEls = container.querySelectorAll('.ged-element.is-fichier');

    fichierEls.forEach(el => {
        el.addEventListener('dragstart', function (e) {
            e.dataTransfer.setData('text/plain', el.dataset.id);
            el.classList.add('ged-dragging');
        });
        el.addEventListener('dragend', function () {
            el.classList.remove('ged-dragging');
            dossierEls.forEach(d => d.classList.remove('ged-drop-target'));
        });
    });

    dossierEls.forEach(d => {
        d.addEventListener('dragover', function (e) {
            e.preventDefault();
            d.classList.add('ged-drop-target');
        });
        d.addEventListener('dragleave', function () {
            d.classList.remove('ged-drop-target');
        });
        d.addEventListener('drop', function (e) {
            e.preventDefault();
            d.classList.remove('ged-drop-target');
            const fileId   = e.dataTransfer.getData('text/plain');
            const folderId = d.dataset.id;
            if (!fileId || !folderId || fileId === folderId) return;

            fetch('/intranet/ressources/' + fileId + '/deplacer', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ parent_id: folderId }),
            }).then(r => {
                if (r.ok) {
                    // Retirer l'élément de l'affichage
                    const movedEl = container.querySelector('.ged-element[data-id="' + fileId + '"]');
                    if (movedEl) movedEl.style.display = 'none';
                    // Flash feedback
                    d.classList.add('ged-drop-success');
                    setTimeout(() => d.classList.remove('ged-drop-success'), 1200);
                }
            });
        });
    });
});
</script>
@endpush
