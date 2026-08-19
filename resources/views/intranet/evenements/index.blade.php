@extends('layouts.app')

@section('title', 'Événements')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Événements</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-calendar-check"></i></span>
                Événements
            </h1>
            <p class="page-subtitle">Tous les événements de l'entreprise</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('intranet.calendrier') }}" class="btn btn-light">
                <i class="fas fa-calendar-days me-2"></i> Vue calendrier
            </a>
            @can('create:evenement')
            <a href="{{ route('intranet.evenements.create') }}" class="btn btn-intranet">
                <i class="fas fa-plus me-2"></i> Nouvel événement
            </a>
            @endcan
        </div>
    </div>

    <form method="GET" class="filters-bar mb-4">
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un événement…">
        </div>
        <select name="type" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous types</option>
            @foreach($types as $t)
                <option value="{{ $t->id }}" @selected(request('type') == $t->id)>{{ $t->nom }}</option>
            @endforeach
        </select>
        <select name="statut" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous</option>
            <option value="avenir" @selected(request('statut') === 'avenir')>À venir</option>
            <option value="passes" @selected(request('statut') === 'passes')>Passés</option>
        </select>
        @if(request('q') || request('type') || request('statut'))
        <a href="{{ route('intranet.evenements.index') }}" class="btn-reset">
            <i class="fas fa-xmark"></i> Réinitialiser
        </a>
        @endif
    </form>

    @if($evenements->count())
    <div class="evenements-grid">
        @foreach($evenements as $ev)
        <article class="evt-card" style="--accent: {{ $ev->couleur_affichee }};">

            {{-- Date à gauche --}}
            <div class="evt-date">
                <div class="evt-date-month">{{ $ev->date_debut->translatedFormat('M') }}</div>
                <div class="evt-date-day">{{ $ev->date_debut->format('d') }}</div>
                <div class="evt-date-year">{{ $ev->date_debut->format('Y') }}</div>
            </div>

            {{-- Corps --}}
            <div class="evt-body">
                <div class="evt-meta-top">
                    @if($ev->type)
                        <span class="evt-type">{{ $ev->type->nom }}</span>
                    @endif
                    @if($ev->est_en_cours)
                        <span class="evt-badge-live"><i class="fas fa-circle"></i> En cours</span>
                    @elseif($ev->est_passe)
                        <span class="evt-badge-past">Passé</span>
                    @endif
                </div>

                <h3 class="evt-title">
                    <a href="{{ route('intranet.evenements.show', $ev) }}">{{ $ev->titre }}</a>
                </h3>

                @if($ev->extrait)
                <p class="evt-extrait">{{ $ev->extrait }}</p>
                @endif

                <div class="evt-info-line">
                    <span><i class="far fa-clock"></i>
                        @if($ev->journee_entiere)
                            Journée entière
                        @else
                            {{ $ev->date_debut->format('H:i') }} – {{ $ev->date_fin->format('H:i') }}
                        @endif
                    </span>
                    @if($ev->lieu)
                        <span><i class="fas fa-location-dot"></i> {{ $ev->lieu }}</span>
                    @endif
                    @if($ev->est_visio)
                        <span class="text-info"><i class="fas fa-video"></i> Visio</span>
                    @endif
                    <span><i class="fas fa-users"></i> {{ $ev->participants_count }} participant{{ $ev->participants_count > 1 ? 's' : '' }}</span>
                </div>
            </div>

            {{-- Action --}}
            <div class="evt-action">
                <a href="{{ route('intranet.evenements.show', $ev) }}" class="evt-action-btn">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </article>
        @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $evenements->links() }}
    </div>

    @else
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-calendar-check"></i></div>
        <h3>Aucun événement</h3>
        <p>Aucun événement ne correspond à vos critères.</p>
        @can('create:evenement')
        <a href="{{ route('intranet.evenements.create') }}" class="btn btn-intranet">
            <i class="fas fa-plus me-2"></i> Créer le premier événement
        </a>
        @endcan
    </div>
    @endif

</div>
@endsection
