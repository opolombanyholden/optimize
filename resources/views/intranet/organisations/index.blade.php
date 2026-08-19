@extends('layouts.app')
@section('title', 'Organisations')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Organisations</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-building-user"></i></span>
                Organisations
            </h1>
            <p class="page-subtitle">Vos comptes clients et prospects</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
        @can('update:organisation_crm')
        <a href="{{ route('intranet.organisations.types-documents.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-folder-tree me-2"></i> Types de documents
        </a>
        @endcan
        @can('create:organisation_crm')
        <div class="btn-group">
            <a href="{{ route('intranet.organisations.create', ['type' => $typeActif ?: 'autre']) }}" class="btn btn-intranet">
                <i class="fas fa-plus me-2"></i> Nouveau {{ strtolower(\App\Models\Intranet\ContactOrganisation::TYPES[$typeActif ?? 'autre'] ?? 'organisation') }}
            </a>
            <button type="button" class="btn btn-intranet dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                <span class="visually-hidden">Autres types</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach(\App\Models\Intranet\ContactOrganisation::TYPES as $tk => $tv)
                    <li><a class="dropdown-item" href="{{ route('intranet.organisations.create', ['type' => $tk]) }}">
                        <i class="fas {{ \App\Models\Intranet\ContactOrganisation::TYPE_ICONES[$tk] }} me-2"></i> {{ $tv }}
                    </a></li>
                @endforeach
            </ul>
        </div>
        @endcan
        </div>
    </div>

    {{-- ═════════ ONGLETS PAR TYPE ═════════ --}}
    <ul class="nav nav-pills mb-3 flex-wrap" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ !$typeActif ? 'active' : '' }}"
               href="{{ route('intranet.organisations.index', array_filter(['q' => request('q')])) }}">
                <i class="fas fa-layer-group me-1"></i> Toutes
                <span class="badge bg-secondary ms-1">{{ $comptes->sum() }}</span>
            </a>
        </li>
        @foreach(\App\Models\Intranet\ContactOrganisation::TYPES as $tk => $tv)
            <li class="nav-item">
                <a class="nav-link {{ $typeActif === $tk ? 'active' : '' }}"
                   href="{{ route('intranet.organisations.index', array_filter(['type' => $tk, 'q' => request('q')])) }}">
                    <i class="fas {{ \App\Models\Intranet\ContactOrganisation::TYPE_ICONES[$tk] }} me-1"></i>
                    {{ $tv }}
                    <span class="badge bg-{{ \App\Models\Intranet\ContactOrganisation::TYPE_COULEURS[$tk] }} ms-1">{{ $comptes[$tk] ?? 0 }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <form method="GET" class="filters-bar mb-4">
        @if($typeActif)<input type="hidden" name="type" value="{{ $typeActif }}">@endif
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher…">
        </div>
        <select name="secteur" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous secteurs</option>
            @foreach($secteurs as $s)
                <option value="{{ $s->id }}" @selected(request('secteur') == $s->id)>{{ $s->nom }}</option>
            @endforeach
        </select>
        <select name="taille" class="filters-select" onchange="this.form.submit()">
            <option value="">Toutes tailles</option>
            @foreach(['TPE','PME','ETI','GE'] as $t)
                <option value="{{ $t }}" @selected(request('taille')===$t)>{{ $t }}</option>
            @endforeach
        </select>
        <select name="statut" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous</option>
            <option value="client"   @selected(request('statut')==='client')>Clients</option>
            <option value="prospect" @selected(request('statut')==='prospect')>Prospects</option>
        </select>
        @if(request('q') || request('secteur') || request('taille') || request('statut'))
        <a href="{{ route('intranet.organisations.index') }}" class="btn-reset"><i class="fas fa-xmark"></i></a>
        @endif
    </form>

    @if($organisations->count())
    <div class="orgs-grid">
        @foreach($organisations as $o)
        <a href="{{ route('intranet.organisations.show', $o) }}" class="org-card">
            <div class="org-card-logo">
                @if($o->logo_url)
                    <img src="{{ $o->logo_url }}" alt="">
                @else
                    <div class="org-card-letters">{{ $o->initiales }}</div>
                @endif
            </div>
            <div class="org-card-body">
                <div class="org-card-meta">
                    @if($o->secteur)
                        <span class="org-secteur" style="background: color-mix(in srgb, {{ $o->secteur->couleur }} 12%, #fff); color: {{ $o->secteur->couleur }};">
                            <i class="fas {{ $o->secteur->icone }}"></i> {{ $o->secteur->nom }}
                        </span>
                    @endif
                    @if($o->taille)<span class="org-taille">{{ $o->taille }}</span>@endif
                </div>
                <h3 class="org-card-name">{{ $o->nom }}</h3>
                <div class="org-card-loc">
                    @if($o->pays){{ $o->pays->drapeau_emoji }}@endif
                    {{ $o->ville ?: ($o->pays?->nom ?? '') }}
                </div>
                <div class="org-card-stats">
                    <span><i class="fas fa-users"></i> {{ $o->contacts_count }} contact{{ $o->contacts_count > 1 ? 's' : '' }}</span>
                    <span><i class="fas fa-fire-flame-curved"></i> {{ $o->opportunites_count }} opp.</span>
                    @if($o->est_client)<span class="badge-client">Client</span>
                    @elseif($o->est_prospect)<span class="badge-prospect">Prospect</span>@endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $organisations->links() }}</div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-building-user"></i></div>
        <h3>Aucune organisation</h3>
        @can('create:organisation_crm')
        <a href="{{ route('intranet.organisations.create') }}" class="btn btn-intranet"><i class="fas fa-plus me-2"></i> Créer la première</a>
        @endcan
    </div>
    @endif
</div>
@endsection
