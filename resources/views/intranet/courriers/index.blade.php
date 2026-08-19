@extends('layouts.app')
@section('title', 'Courriers')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Courriers</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-envelope-open-text"></i></span>
                Gestion du courrier
            </h1>
            <p class="page-subtitle">Suivi des courriers entrants, sortants et internes</p>
        </div>
        @can('create:courrier')
        <a href="{{ route('intranet.courriers.create') }}" class="btn btn-intranet">
            <i class="fas fa-plus me-2"></i> Nouveau courrier
        </a>
        @endcan
    </div>

    {{-- Cartes statistiques --}}
    <div class="courrier-stats mb-4">
        <a href="{{ route('intranet.courriers.index') }}" class="courrier-stat-card">
            <div class="csc-icon" style="background:#7C3AED;"><i class="fas fa-envelope"></i></div>
            <div>
                <div class="csc-value">{{ $stats['total'] }}</div>
                <div class="csc-label">Total</div>
            </div>
        </a>
        <a href="?statut=en_traitement" class="courrier-stat-card">
            <div class="csc-icon" style="background:#F59E0B;"><i class="fas fa-clock"></i></div>
            <div>
                <div class="csc-value">{{ $stats['a_traiter'] }}</div>
                <div class="csc-label">À traiter</div>
            </div>
        </a>
        <a href="?mes_courriers=1" class="courrier-stat-card highlight">
            <div class="csc-icon" style="background:#0891B2;"><i class="fas fa-user-check"></i></div>
            <div>
                <div class="csc-value">{{ $stats['mes_courriers'] }}</div>
                <div class="csc-label">Mes courriers</div>
            </div>
        </a>
        <a href="?en_retard=1" class="courrier-stat-card">
            <div class="csc-icon" style="background:#DC2626;"><i class="fas fa-triangle-exclamation"></i></div>
            <div>
                <div class="csc-value">{{ $stats['en_retard'] }}</div>
                <div class="csc-label">En retard</div>
            </div>
        </a>
    </div>

    <form method="GET" class="filters-bar mb-4">
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher (objet, référence, expéditeur…)">
        </div>
        <select name="type" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous types</option>
            <option value="entrant" @selected(request('type')==='entrant')>Entrant</option>
            <option value="sortant" @selected(request('type')==='sortant')>Sortant</option>
            <option value="interne" @selected(request('type')==='interne')>Interne</option>
        </select>
        <select name="statut" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous statuts</option>
            <option value="recu"          @selected(request('statut')==='recu')>Reçu</option>
            <option value="en_traitement" @selected(request('statut')==='en_traitement')>En traitement</option>
            <option value="traite"        @selected(request('statut')==='traite')>Traité</option>
            <option value="archive"       @selected(request('statut')==='archive')>Archivé</option>
        </select>
        <select name="assigne" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous responsables</option>
            @foreach($utilisateurs as $u)
                <option value="{{ $u->id }}" @selected(request('assigne')==$u->id)>{{ $u->prenoms }} {{ $u->name }}</option>
            @endforeach
        </select>
        <label class="filters-toggle">
            <input type="checkbox" name="urgents" value="1" @checked(request()->boolean('urgents')) onchange="this.form.submit()">
            🔥 Urgents
        </label>
    </form>

    @if($courriers->count())
    <div class="form-card">
        <table class="opp-table">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Type</th>
                    <th>Objet</th>
                    <th>Expéditeur / Destinataire</th>
                    <th>Statut</th>
                    <th>Assigné à</th>
                    <th>Échéance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courriers as $c)
                <tr onclick="window.location='{{ route('intranet.courriers.show', $c) }}'">
                    <td><code class="opp-ref">{{ $c->reference }}</code></td>
                    <td>
                        <span class="courrier-type-badge type-{{ $c->type }}">
                            @if($c->type === 'entrant')<i class="fas fa-arrow-down"></i>
                            @elseif($c->type === 'sortant')<i class="fas fa-arrow-up"></i>
                            @else<i class="fas fa-arrows-left-right"></i>@endif
                            {{ ucfirst($c->type) }}
                        </span>
                    </td>
                    <td>
                        @if($c->urgent)<span title="Urgent" class="text-danger me-1"><i class="fas fa-bolt"></i></span>@endif
                        @if($c->confidentiel)<span title="Confidentiel" class="text-warning me-1"><i class="fas fa-lock"></i></span>@endif
                        <strong>{{ Str::limit($c->objet, 45) }}</strong>
                    </td>
                    <td>
                        <small class="text-muted">
                            @if($c->type === 'sortant') → {{ $c->destinataire ?: '—' }}
                            @else ← {{ $c->expediteur ?: '—' }}
                            @endif
                        </small>
                    </td>
                    <td>
                        <span class="opp-stage-badge" style="background:{{ $c->statut_couleur }};">{{ $c->statut_libelle }}</span>
                    </td>
                    <td>
                        @if($c->assigne)
                            <small>{{ $c->assigne->prenoms }} {{ $c->assigne->name }}</small>
                        @else
                            <small class="text-muted">—</small>
                        @endif
                    </td>
                    <td>
                        @if($c->echeance_traitement)
                            <small class="{{ $c->est_en_retard ? 'text-danger fw-bold' : '' }}">
                                {{ $c->echeance_traitement->format('d/m/Y') }}
                            </small>
                        @else
                            <small class="text-muted">—</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $courriers->links() }}</div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-envelope-open-text"></i></div>
        <h3>Aucun courrier</h3>
        @can('create:courrier')
        <a href="{{ route('intranet.courriers.create') }}" class="btn btn-intranet"><i class="fas fa-plus me-2"></i> Enregistrer le premier</a>
        @endcan
    </div>
    @endif
</div>
@endsection
