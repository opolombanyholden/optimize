@extends('layouts.app')
@section('title', 'Rapports & CR')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Gestion Projet</a></li>
    <li class="breadcrumb-item active">Rapports & CR</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-file-lines"></i></span>
                Rapports & Comptes rendus
            </h1>
            <p class="page-subtitle">Rédaction et suivi des rapports, PV et comptes rendus</p>
        </div>
        @can('create:rapport')
        <a href="{{ route('intranet.rapports.create') }}" class="btn btn-intranet">
            <i class="fas fa-plus me-2"></i> Nouveau rapport
        </a>
        @endcan
    </div>

    <div class="courrier-stats mb-4">
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#4F46E5;"><i class="fas fa-file-lines"></i></div>
            <div><div class="csc-value">{{ $stats['total'] }}</div><div class="csc-label">Total</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#94A3B8;"><i class="fas fa-pen-to-square"></i></div>
            <div><div class="csc-value">{{ $stats['brouillons'] }}</div><div class="csc-label">Brouillons</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#16A34A;"><i class="fas fa-check-circle"></i></div>
            <div><div class="csc-value">{{ $stats['publies'] }}</div><div class="csc-label">Publiés</div></div>
        </div>
    </div>

    <form method="GET" class="filters-bar mb-4">
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher (titre, référence…)">
        </div>
        <select name="type" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous types</option>
            <option value="rapport" @selected(request('type')==='rapport')>Rapport</option>
            <option value="cr"      @selected(request('type')==='cr')>Compte rendu</option>
            <option value="pv"      @selected(request('type')==='pv')>Procès-verbal</option>
            <option value="note"    @selected(request('type')==='note')>Note</option>
            <option value="memo"    @selected(request('type')==='memo')>Mémo</option>
        </select>
        <select name="statut" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous statuts</option>
            <option value="brouillon"   @selected(request('statut')==='brouillon')>Brouillon</option>
            <option value="en_revision" @selected(request('statut')==='en_revision')>En révision</option>
            <option value="valide"      @selected(request('statut')==='valide')>Validé</option>
            <option value="publie"      @selected(request('statut')==='publie')>Publié</option>
        </select>
        <label class="filters-toggle">
            <input type="checkbox" name="mes_rapports" value="1" @checked(request()->boolean('mes_rapports')) onchange="this.form.submit()">
            Mes rapports
        </label>
    </form>

    @if($rapports->count())
    <div class="ged-container view-list">
        @foreach($rapports as $r)
        <div class="ged-element is-fichier" style="border-left-color: {{ $r->type_couleur }};">
            <a href="{{ route('intranet.rapports.show', $r) }}" class="ged-el-link">
                <div class="ged-el-preview" style="--icon-color: {{ $r->type_couleur }};">
                    <i class="fas fa-file-lines"></i>
                </div>
                <div class="ged-el-info">
                    <div class="ged-el-name">{{ $r->titre }}</div>
                    <div class="ged-el-meta">
                        <code style="font-size:.62rem;color:{{ $r->type_couleur }};background:color-mix(in srgb, {{ $r->type_couleur }} 10%, #fff);padding:1px 6px;border-radius:3px;">{{ $r->reference }}</code>
                        · <span style="color:{{ $r->type_couleur }};font-weight:600;">{{ $r->type_libelle }}</span>
                        @if($r->projet) · <i class="fas fa-diagram-project" style="color:#7C3AED;"></i> {{ $r->projet->nom }} @endif
                        @if($r->evenement) · <i class="fas fa-calendar-check" style="color:#0891B2;"></i> {{ Str::limit($r->evenement->titre, 25) }} @endif
                    </div>
                </div>
                <div class="ged-el-right">
                    <span class="opp-stage-badge" style="background:{{ $r->statut_couleur }};font-size:.6rem;">{{ $r->statut_libelle }}</span>
                    <span class="ged-date">{{ $r->date_document?->format('d/m/Y') ?? $r->created_at->format('d/m/Y') }}</span>
                    <span class="ged-date">{{ $r->auteur?->prenoms }} {{ $r->auteur?->name }}</span>
                </div>
            </a>
            <div class="ged-el-actions">
                <div class="dropdown">
                    <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                        <li><a class="dropdown-item" href="{{ route('intranet.rapports.show', $r) }}"><i class="fas fa-eye"></i> Voir</a></li>
                        @can('update:rapport')<li><a class="dropdown-item" href="{{ route('intranet.rapports.edit', $r) }}"><i class="fas fa-pen-to-square"></i> Modifier</a></li>@endcan
                        @can('delete:rapport')
                        <li><hr class="dropdown-divider"></li>
                        <li><form action="{{ route('intranet.rapports.destroy', $r) }}" method="POST" onsubmit="return confirm('Supprimer ?');">@csrf @method('DELETE')<button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button></form></li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $rapports->links() }}</div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-file-lines"></i></div>
        <h3>Aucun rapport</h3>
        @can('create:rapport')
        <a href="{{ route('intranet.rapports.create') }}" class="btn btn-intranet"><i class="fas fa-plus me-2"></i> Rédiger le premier</a>
        @endcan
    </div>
    @endif
</div>
@endsection
