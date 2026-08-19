@extends('layouts.app')
@section('title', 'Archives')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.archives.index') }}">Archives</a></li>
    @if($dossier)
        @foreach($dossier->chemin_complet as $anc)
            @if(!$loop->last)
            <li class="breadcrumb-item"><a href="{{ route('intranet.archives.index', ['dossier' => $anc->id]) }}">{{ $anc->nom }}</a></li>
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
                <span class="page-title-icon"><i class="fas fa-box-archive"></i></span>
                {{ $dossier ? $dossier->nom : 'Archives documentaires' }}
            </h1>
            <p class="page-subtitle">Conservation et gestion du cycle de vie des documents</p>
        </div>
        <div class="d-flex gap-2">
            @if($dossier)
            <a href="{{ route('intranet.archives.index', ['dossier' => $dossier->parent_id]) }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Remonter
            </a>
            @endif
            @can('create:archive')
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalDossier">
                <i class="fas fa-folder-plus me-2"></i> Dossier
            </button>
            <a href="{{ route('intranet.archives.create', ['dossier' => $dossier?->id]) }}" class="btn btn-intranet">
                <i class="fas fa-box-archive me-2"></i> Archiver un document
            </a>
            @endcan
        </div>
    </div>

    <div class="courrier-stats mb-4">
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#64748B;"><i class="fas fa-folder"></i></div>
            <div><div class="csc-value">{{ $stats['dossiers'] }}</div><div class="csc-label">Dossiers</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#7C3AED;"><i class="fas fa-box-archive"></i></div>
            <div><div class="csc-value">{{ $stats['archives'] }}</div><div class="csc-label">Archives</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#DC2626;"><i class="fas fa-calendar-xmark"></i></div>
            <div><div class="csc-value">{{ $stats['a_detruire'] }}</div><div class="csc-label">À détruire</div></div>
        </div>
    </div>

    <form method="GET" class="filters-bar mb-4">
        @if($dossier)<input type="hidden" name="dossier" value="{{ $dossier->id }}">@endif
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher (titre, référence, code-barre…)">
        </div>
        <select name="nature" class="filters-select" onchange="this.form.submit()">
            <option value="">Toutes natures</option>
            @foreach($natures as $n)<option value="{{ $n }}" @selected(request('nature')===$n)>{{ $n }}</option>@endforeach
        </select>
        <select name="statut" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous statuts</option>
            <option value="actif"      @selected(request('statut')==='actif')>Actif</option>
            <option value="semi_actif" @selected(request('statut')==='semi_actif')>Semi-actif</option>
            <option value="inactif"    @selected(request('statut')==='inactif')>Inactif</option>
            <option value="a_detruire" @selected(request('statut')==='a_detruire')>À détruire</option>
        </select>
    </form>

    {{-- Dossiers --}}
    @if($sousDossiers->count() && !request()->filled('q'))
    <h3 class="ged-section-title mb-3"><i class="fas fa-folder"></i> Dossiers d'archives</h3>
    <div class="media-albums-grid mb-4">
        @foreach($sousDossiers as $sd)
        <div class="media-album-card" style="--album-color: {{ $sd->couleur ?? '#64748B' }};">
            <a href="{{ route('intranet.archives.index', ['dossier' => $sd->id]) }}" class="media-album-link">
                <div class="media-album-cover">
                    @if($sd->couverture_url)
                        <img src="{{ $sd->couverture_url }}" alt="">
                    @elseif($sd->icone)
                        <i class="fas {{ $sd->icone }}"></i>
                    @else
                        <i class="fas fa-box-archive"></i>
                    @endif
                </div>
                <div class="media-album-body">
                    <h4 class="media-album-name">{{ $sd->nom }}</h4>
                    <span class="media-album-count">{{ $sd->archives_count }} archive{{ $sd->archives_count > 1 ? 's' : '' }}</span>
                </div>
            </a>
            <div class="media-album-actions">
                <div class="dropdown">
                    <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                        <li><a class="dropdown-item" href="{{ route('intranet.archives.index', ['dossier' => $sd->id]) }}"><i class="fas fa-folder-open"></i> Ouvrir</a></li>
                        @can('update:archive')
                        <li><button class="dropdown-item" onclick="editDossier({{ $sd->id }},'{{ addslashes($sd->nom) }}','{{ addslashes($sd->description ?? '') }}','{{ $sd->icone }}','{{ $sd->couleur }}')"><i class="fas fa-pen-to-square"></i> Modifier</button></li>
                        @endcan
                        @can('delete:archive')
                        <li><hr class="dropdown-divider"></li>
                        <li><form action="{{ route('intranet.archives.dossiers.destroy', $sd) }}" method="POST" onsubmit="return confirm('Supprimer ?');">@csrf @method('DELETE')<button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button></form></li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Archives --}}
    @if($archives->count())
    <div class="ged-container view-list">
        @foreach($archives as $a)
        <div class="ged-element is-fichier">
            <a href="{{ route('intranet.archives.show', $a) }}" class="ged-el-link">
                <div class="ged-el-preview" style="--icon-color: {{ $a->couleur_icone }};">
                    <i class="fas {{ $a->icone }}"></i>
                </div>
                <div class="ged-el-info">
                    <div class="ged-el-name">
                        @if($a->is_confidentiel)<i class="fas fa-lock text-warning me-1" style="font-size:.7rem;"></i>@endif
                        {{ $a->titre }}
                    </div>
                    <div class="ged-el-meta">
                        <code style="font-size:.65rem;color:#7C3AED;background:#F5F3FF;padding:1px 5px;border-radius:3px;">{{ $a->reference }}</code>
                        · {{ $a->taille_humaine }}
                        @if($a->nature) · {{ $a->nature }} @endif
                    </div>
                </div>
                <div class="ged-el-right">
                    <span class="opp-stage-badge" style="background:{{ $a->statut_couleur }};font-size:.6rem;">{{ $a->statut_libelle }}</span>
                    <span class="ged-date">{{ $a->date_archivage?->format('d/m/Y') ?? $a->created_at->format('d/m/Y') }}</span>
                    @if($a->date_destruction_prevue)
                    <span class="ged-date {{ $a->date_destruction_prevue->lt(now()) ? 'text-danger fw-bold' : '' }}">
                        <i class="fas fa-calendar-xmark"></i> {{ $a->date_destruction_prevue->format('d/m/Y') }}
                    </span>
                    @endif
                </div>
            </a>
            <div class="ged-el-actions">
                <div class="dropdown">
                    <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                        <li><a class="dropdown-item" href="{{ route('intranet.archives.show', $a) }}"><i class="fas fa-eye"></i> Voir</a></li>
                        <li><a class="dropdown-item" href="{{ route('intranet.archives.download', $a) }}"><i class="fas fa-download"></i> Télécharger</a></li>
                        @can('update:archive')<li><a class="dropdown-item" href="{{ route('intranet.archives.edit', $a) }}"><i class="fas fa-pen-to-square"></i> Modifier</a></li>@endcan
                        @can('delete:archive')
                        <li><hr class="dropdown-divider"></li>
                        <li><form action="{{ route('intranet.archives.destroy', $a) }}" method="POST" onsubmit="return confirm('Supprimer ?');">@csrf @method('DELETE')<button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button></form></li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $archives->links() }}</div>
    @elseif(!$sousDossiers->count())
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-box-archive"></i></div>
        <h3>{{ $dossier ? 'Dossier vide' : 'Aucune archive' }}</h3>
        @can('create:archive')
        <div class="d-flex gap-2 justify-content-center">
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalDossier"><i class="fas fa-folder-plus me-2"></i> Dossier</button>
            <a href="{{ route('intranet.archives.create', ['dossier' => $dossier?->id]) }}" class="btn btn-intranet"><i class="fas fa-box-archive me-2"></i> Archiver</a>
        </div>
        @endcan
    </div>
    @endif
</div>

@can('create:archive')
<div class="modal fade" id="modalDossier" tabindex="-1">
    <div class="modal-dialog">
        <form id="dossierForm" method="POST" enctype="multipart/form-data" class="modal-content" action="{{ route('intranet.archives.dossiers.store') }}">
            @csrf
            <input type="hidden" name="_method" id="dossierMethod" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="dossierModalTitle">Nouveau dossier d'archives</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="parent_id" value="{{ $dossier?->id }}">
                <div class="mb-3"><label class="form-label">Nom <span class="text-danger">*</span></label><input type="text" name="nom" id="dossierNom" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="dossierDesc" rows="2" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Icône</label><input type="text" name="icone" id="dossierIcone" class="form-control" placeholder="fa-box-archive…"></div>
                    <div class="col-md-6"><label class="form-label">Couleur</label><input type="color" name="couleur" id="dossierCouleur" class="form-control form-control-color" value="#64748B"></div>
                </div>
                <div class="mt-3"><label class="form-label">Couverture</label><input type="file" name="couverture" class="form-control" accept="image/*"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet"><i class="fas fa-folder-plus me-2"></i> <span id="dossierSubmitText">Créer</span></button>
            </div>
        </form>
    </div>
</div>
@endcan

@push('scripts')
<script>
function editDossier(id, nom, desc, icone, couleur) {
    document.getElementById('dossierModalTitle').textContent = 'Modifier le dossier';
    document.getElementById('dossierNom').value = nom;
    document.getElementById('dossierDesc').value = desc;
    document.getElementById('dossierIcone').value = icone || '';
    document.getElementById('dossierCouleur').value = couleur || '#64748B';
    document.getElementById('dossierMethod').value = 'PUT';
    document.getElementById('dossierSubmitText').textContent = 'Enregistrer';
    document.getElementById('dossierForm').action = '/intranet/archives-dossiers/' + id;
    new bootstrap.Modal(document.getElementById('modalDossier')).show();
}
document.getElementById('modalDossier')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('dossierModalTitle').textContent = 'Nouveau dossier d\'archives';
    document.getElementById('dossierNom').value = '';
    document.getElementById('dossierDesc').value = '';
    document.getElementById('dossierIcone').value = '';
    document.getElementById('dossierCouleur').value = '#64748B';
    document.getElementById('dossierMethod').value = 'POST';
    document.getElementById('dossierSubmitText').textContent = 'Créer';
    document.getElementById('dossierForm').action = '{{ route("intranet.archives.dossiers.store") }}';
});
</script>
@endpush
@endsection
