@extends('layouts.app')

@section('title', 'Contacts')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Contacts</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-address-card"></i></span>
                Contacts
            </h1>
            <p class="page-subtitle">Carnet d'adresses & gestion de la relation</p>
        </div>
        @can('create:contact')
        <a href="{{ route('intranet.contacts.create') }}" class="btn btn-intranet">
            <i class="fas fa-plus me-2"></i> Nouveau contact
        </a>
        @endcan
    </div>

    <form method="GET" class="filters-bar mb-4">
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un contact…">
        </div>
        <select name="organisation" class="filters-select" onchange="this.form.submit()">
            <option value="">Toutes organisations</option>
            @foreach($organisations as $o)
                <option value="{{ $o->id }}" @selected(request('organisation') == $o->id)>{{ $o->nom }}</option>
            @endforeach
        </select>
        <select name="etiquette" class="filters-select" onchange="this.form.submit()">
            <option value="">Toutes étiquettes</option>
            <option value="hot"  @selected(request('etiquette')==='hot')>🔥 Hot</option>
            <option value="warm" @selected(request('etiquette')==='warm')>☀️ Warm</option>
            <option value="cold" @selected(request('etiquette')==='cold')>❄️ Cold</option>
        </select>
        <label class="filters-toggle">
            <input type="checkbox" name="favoris" value="1" @checked(request()->boolean('favoris')) onchange="this.form.submit()">
            ⭐ Favoris
        </label>
        @if(request('q') || request('organisation') || request('etiquette') || request()->boolean('favoris'))
        <a href="{{ route('intranet.contacts.index') }}" class="btn-reset"><i class="fas fa-xmark"></i> Réinitialiser</a>
        @endif
    </form>

    @if($contacts->count())
    <div class="contacts-grid">
        @foreach($contacts as $c)
        <a href="{{ route('intranet.contacts.show', $c) }}" class="contact-card">
            <div class="contact-card-header">
                @if($c->photo_url)
                    <img src="{{ $c->photo_url }}" alt="" class="contact-card-avatar">
                @else
                    <div class="contact-card-avatar contact-avatar-letters" style="background:{{ ['#7C3AED','#059669','#D97706','#0891B2','#0D9488','#4F46E5'][$loop->index % 6] }};">
                        {{ $c->initiales }}
                    </div>
                @endif
                <div class="contact-card-badges">
                    @if($c->est_favori)<span class="contact-fav">⭐</span>@endif
                    @if($c->etiquette === 'hot')   <span class="contact-tag hot">🔥</span>@endif
                    @if($c->etiquette === 'warm')  <span class="contact-tag warm">☀️</span>@endif
                    @if($c->etiquette === 'cold')  <span class="contact-tag cold">❄️</span>@endif
                </div>
            </div>
            <div class="contact-card-body">
                <h3 class="contact-card-name">{{ $c->nom_complet }}</h3>
                @if($c->poste)<div class="contact-card-poste">{{ $c->poste }}</div>@endif
                @if($c->organisation)
                    <div class="contact-card-orga"><i class="fas fa-building"></i> {{ $c->organisation->nom }}</div>
                @endif

                <div class="contact-card-info">
                    @if($c->email)<div><i class="fas fa-envelope"></i> {{ Str::limit($c->email, 26) }}</div>@endif
                    @if($c->mobile || $c->telephone)<div><i class="fas fa-phone"></i> {{ $c->mobile ?: $c->telephone }}</div>@endif
                    @if($c->pays)<div><i class="fas fa-location-dot"></i> {{ $c->pays->drapeau_emoji }} {{ $c->ville ?: $c->pays->nom }}</div>@endif
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $contacts->links() }}
    </div>

    @else
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-address-card"></i></div>
        <h3>Aucun contact</h3>
        <p>Commencez à constituer votre carnet d'adresses.</p>
        @can('create:contact')
        <a href="{{ route('intranet.contacts.create') }}" class="btn btn-intranet">
            <i class="fas fa-plus me-2"></i> Créer le premier contact
        </a>
        @endcan
    </div>
    @endif

</div>
@endsection
