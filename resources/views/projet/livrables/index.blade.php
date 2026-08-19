@extends('layouts.app')
@section('title', $projet->nom . ' — Livrables')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">Livrables</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'livrables'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-box-open"></i></span>
                Livrables — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Suivi des deliverables du projet</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalLivrable">
            <i class="fas fa-plus me-2"></i> Nouveau livrable
        </button>
    </div>

    @if($projet->livrables->count())
    <div class="ged-container view-list">
        @foreach($projet->livrables->sortBy('date_prevue') as $l)
        <div class="ged-element is-fichier" style="border-left-color: {{ match($l->statut) {
            'accepte' => '#16A34A', 'refuse' => '#DC2626', 'livre' => '#0891B2',
            'en_cours' => '#F59E0B', default => '#94A3B8'
        } }};">
            <div class="ged-el-link" style="cursor:default;">
                <div class="ged-el-preview" style="--icon-color: {{ match($l->statut) {
                    'accepte' => '#16A34A', 'refuse' => '#DC2626', 'livre' => '#0891B2',
                    'en_cours' => '#F59E0B', default => '#94A3B8'
                } }};">
                    <i class="fas {{ match($l->statut) {
                        'accepte' => 'fa-check-circle', 'refuse' => 'fa-circle-xmark',
                        'livre' => 'fa-truck', 'en_cours' => 'fa-spinner', default => 'fa-box-open'
                    } }}"></i>
                </div>
                <div class="ged-el-info">
                    <div class="ged-el-name">
                        @if($l->estEnRetard())<i class="fas fa-triangle-exclamation text-danger me-1" style="font-size:.7rem;"></i>@endif
                        {{ $l->titre }}
                    </div>
                    <div class="ged-el-meta">
                        <span class="opp-stage-badge" style="background:{{ match($l->statut) {
                            'accepte' => '#16A34A', 'refuse' => '#DC2626', 'livre' => '#0891B2',
                            'en_cours' => '#F59E0B', default => '#94A3B8'
                        } }};font-size:.58rem;">{{ ucfirst(str_replace('_', ' ', $l->statut)) }}</span>
                        @if($l->phase) · <i class="fas fa-sitemap" style="color:#0D9488;"></i> {{ $l->phase->nom }} @endif
                        @if($l->responsable) · <i class="fas fa-user"></i> {{ $l->responsable->prenoms }} @endif
                    </div>
                </div>
                <div class="ged-el-right">
                    @if($l->date_prevue)
                    <span class="ged-date {{ $l->estEnRetard() ? 'text-danger fw-bold' : '' }}">
                        Prévu {{ $l->date_prevue->format('d/m/Y') }}
                    </span>
                    @endif
                    @if($l->date_livraison)
                    <span class="ged-date text-success">Livré {{ $l->date_livraison->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
            <div class="ged-el-actions">
                <div class="dropdown">
                    <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                        <li><form action="{{ route('projet.livrables.destroy', [$projet, $l]) }}" method="POST" onsubmit="return confirm('Supprimer ?');">
                            @csrf @method('DELETE')
                            <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                        </form></li>
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-box-open" style="color:#0D9488;"></i></div>
        <h3>Aucun livrable</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalLivrable">
            <i class="fas fa-plus me-2"></i> Créer le premier
        </button>
    </div>
    @endif
</div>

<div class="modal fade" id="modalLivrable" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('projet.livrables.store', $projet) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Nouveau livrable</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-control"></textarea></div>
                <div class="mb-3"><label class="form-label">Critères d'acceptation</label>
                    <textarea name="criteres_acceptation" rows="2" class="form-control" placeholder="Conditions que le livrable doit remplir…"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Phase</label>
                        <select name="phase_id" class="form-select">
                            <option value="">—</option>
                            @foreach($projet->phases as $ph)
                                <option value="{{ $ph->id }}">{{ $ph->nom }}</option>
                            @endforeach
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Tâche</label>
                        <select name="tache_id" class="form-select">
                            <option value="">—</option>
                            @foreach($projet->taches as $t)
                                <option value="{{ $t->id }}">{{ $t->titre }}</option>
                            @endforeach
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Date prévue</label>
                        <input type="date" name="date_prevue" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-box-open me-2"></i> Créer</button></div>
        </form>
    </div>
</div>
@endsection
