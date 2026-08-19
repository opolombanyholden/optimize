@extends('layouts.app')
@section('title', 'Opportunités')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Opportunités</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-fire-flame-curved"></i></span>
                Opportunités
            </h1>
            <p class="page-subtitle">Suivi commercial du pipeline de ventes</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('intranet.pipeline') }}" class="btn btn-light">
                <i class="fas fa-chart-gantt me-2"></i> Vue Kanban
            </a>
            @can('create:opportunite')
            <a href="{{ route('intranet.opportunites.create') }}" class="btn btn-intranet">
                <i class="fas fa-plus me-2"></i> Nouvelle opportunité
            </a>
            @endcan
        </div>
    </div>

    <form method="GET" class="filters-bar mb-4">
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher…">
        </div>
        <select name="etape" class="filters-select" onchange="this.form.submit()">
            <option value="">Toutes étapes</option>
            @foreach($etapes as $e)<option value="{{ $e->id }}" @selected(request('etape')==$e->id)>{{ $e->nom }}</option>@endforeach
        </select>
        <select name="responsable" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous responsables</option>
            @foreach($responsables as $r)<option value="{{ $r->id }}" @selected(request('responsable')==$r->id)>{{ $r->prenoms }} {{ $r->name }}</option>@endforeach
        </select>
        <select name="statut" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous statuts</option>
            <option value="ouvert"      @selected(request('statut')==='ouvert')>Ouvert</option>
            <option value="gagnee"      @selected(request('statut')==='gagnee')>Gagnée</option>
            <option value="perdue"      @selected(request('statut')==='perdue')>Perdue</option>
            <option value="abandonnee"  @selected(request('statut')==='abandonnee')>Abandonnée</option>
        </select>
    </form>

    @if($opportunites->count())
    <div class="form-card">
        <table class="opp-table">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Titre</th>
                    <th>Contact / Organisation</th>
                    <th>Étape</th>
                    <th>Montant</th>
                    <th>Probabilité</th>
                    <th>Échéance</th>
                    <th>Responsable</th>
                </tr>
            </thead>
            <tbody>
                @foreach($opportunites as $opp)
                <tr onclick="window.location='{{ route('intranet.opportunites.show', $opp) }}'">
                    <td><code>{{ $opp->reference }}</code></td>
                    <td><strong>{{ $opp->titre }}</strong></td>
                    <td>
                        @if($opp->contact)<div>{{ $opp->contact->nom_complet }}</div>@endif
                        @if($opp->organisation)<small class="text-muted">{{ $opp->organisation->nom }}</small>@endif
                    </td>
                    <td>
                        @if($opp->etape)
                            <span class="opp-stage-badge" style="background:{{ $opp->etape->couleur }};">{{ $opp->etape->nom }}</span>
                        @endif
                    </td>
                    <td><strong>{{ $opp->valeur ? number_format($opp->valeur, 0, ',', ' ') . ' ' . $opp->devise : '—' }}</strong></td>
                    <td>{{ $opp->probabilite }}%</td>
                    <td>{{ $opp->date_echeance?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $opp->responsable?->prenoms }} {{ $opp->responsable?->name ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $opportunites->links() }}</div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-fire-flame-curved"></i></div>
        <h3>Aucune opportunité</h3>
        @can('create:opportunite')
        <a href="{{ route('intranet.opportunites.create') }}" class="btn btn-intranet"><i class="fas fa-plus me-2"></i> Créer la première</a>
        @endcan
    </div>
    @endif
</div>
@endsection
