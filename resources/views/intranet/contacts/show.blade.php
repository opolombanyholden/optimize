@extends('layouts.app')
@section('title', $contact->nom_complet)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.contacts.index') }}">Contacts</a></li>
    <li class="breadcrumb-item active">{{ $contact->nom_complet }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
<article class="contact-detail">

    <div class="contact-detail-header">
        @if($contact->photo_url)
            <img src="{{ $contact->photo_url }}" alt="" class="contact-detail-photo">
        @else
            <div class="contact-detail-photo contact-avatar-letters" style="background:#7C3AED;">
                {{ $contact->initiales }}
            </div>
        @endif

        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                @if($contact->est_favori)<span class="contact-fav-large">⭐ Favori</span>@endif
                @if($contact->etiquette === 'hot')   <span class="contact-tag-lg hot">🔥 Hot</span>@endif
                @if($contact->etiquette === 'warm')  <span class="contact-tag-lg warm">☀️ Warm</span>@endif
                @if($contact->etiquette === 'cold')  <span class="contact-tag-lg cold">❄️ Cold</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $contact->nom_complet }}</h1>
            @if($contact->poste)<div class="contact-detail-poste">{{ $contact->poste }}</div>@endif
            @if($contact->organisation)
                <a href="{{ route('intranet.organisations.show', $contact->organisation) }}" class="contact-detail-orga">
                    <i class="fas fa-building"></i> {{ $contact->organisation->nom }}
                </a>
            @endif
        </div>

        <div class="contact-detail-actions">
            @can('update:contact')
            <form action="{{ route('intranet.contacts.favori', $contact) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-light" title="Favori">
                    @if($contact->est_favori) ⭐ @else ☆ @endif
                </button>
            </form>
            <a href="{{ route('intranet.contacts.edit', $contact) }}" class="btn btn-intranet">
                <i class="fas fa-pen-to-square me-2"></i> Modifier
            </a>
            @endcan
        </div>
    </div>

    <div class="contact-detail-grid">

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-address-card"></i> Coordonnées</h4>
            <ul class="contact-info-list">
                @if($contact->email)<li><i class="fas fa-envelope"></i> <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></li>@endif
                @if($contact->mobile)<li><i class="fas fa-mobile-screen"></i> <a href="tel:{{ $contact->mobile }}">{{ $contact->mobile }}</a></li>@endif
                @if($contact->telephone)<li><i class="fas fa-phone"></i> <a href="tel:{{ $contact->telephone }}">{{ $contact->telephone }}</a></li>@endif
                @if($contact->linkedin)<li><i class="fab fa-linkedin"></i> <a href="{{ $contact->linkedin }}" target="_blank">LinkedIn</a></li>@endif
                @if($contact->twitter)<li><i class="fab fa-x-twitter"></i> {{ $contact->twitter }}</li>@endif
                @if($contact->site_web)<li><i class="fas fa-globe"></i> <a href="{{ $contact->site_web }}" target="_blank">{{ $contact->site_web }}</a></li>@endif
            </ul>
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-location-dot"></i> Localisation</h4>
            @if($contact->adresse || $contact->ville || $contact->pays)
                <ul class="contact-info-list">
                    @if($contact->adresse)<li>{{ $contact->adresse }}</li>@endif
                    @if($contact->ville)<li>{{ $contact->ville }}</li>@endif
                    @if($contact->pays)<li>{{ $contact->pays->drapeau_emoji }} {{ $contact->pays->nom }}</li>@endif
                </ul>
            @else
                <p class="text-muted small">Aucune adresse renseignée</p>
            @endif
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-tags"></i> Métadonnées</h4>
            <ul class="contact-info-list">
                @if($contact->source)<li><strong>Source :</strong> {{ $contact->source }}</li>@endif
                @if($contact->langue)<li><strong>Langue :</strong> {{ strtoupper($contact->langue) }}</li>@endif
                @if($contact->date_naissance)<li><strong>Né(e) le :</strong> {{ $contact->date_naissance->translatedFormat('d F Y') }}</li>@endif
                @if(!empty($contact->tags))
                <li>
                    <strong>Tags :</strong>
                    @foreach($contact->tags as $t)
                        <span class="news-tag">#{{ $t }}</span>
                    @endforeach
                </li>
                @endif
                <li><strong>Vues :</strong> {{ $contact->vues_count }}</li>
            </ul>
        </div>
    </div>

    @if($contact->notes)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-note-sticky"></i> Notes</h4>
        <div class="annonce-content p-0 border-0">{!! $contact->notes !!}</div>
    </div>
    @endif

    @include('intranet._partials.media-display', ['entity' => $contact])

    @if($contact->opportunites->count())
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-fire-flame-curved"></i> Opportunités liées ({{ $contact->opportunites->count() }})</h4>
        <div class="opportunites-mini-list">
            @foreach($contact->opportunites as $opp)
            <a href="{{ route('intranet.opportunites.show', $opp) }}" class="opp-mini-card">
                <span class="opp-mini-stage" style="background:{{ $opp->etape?->couleur ?? '#94A3B8' }};">{{ $opp->etape?->nom }}</span>
                <div class="opp-mini-title">{{ $opp->titre }}</div>
                @if($opp->valeur)
                    <div class="opp-mini-amount">{{ number_format($opp->valeur, 0, ',', ' ') }} {{ $opp->devise }}</div>
                @endif
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($contact->interactions->count())
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-clock-rotate-left"></i> Historique des interactions</h4>
        <div class="interactions-timeline">
            @foreach($contact->interactions as $i)
            <div class="interaction-item">
                <div class="interaction-icon" style="background:{{ $i->couleur }};"><i class="fas {{ $i->icone }}"></i></div>
                <div class="interaction-body">
                    <div class="interaction-header">
                        <strong>{{ $i->objet }}</strong>
                        <span class="text-muted small">{{ $i->date_interaction->diffForHumans() }}</span>
                    </div>
                    @if($i->description)<div class="interaction-desc">{{ $i->description }}</div>@endif
                    <div class="interaction-meta">
                        <span class="badge-soft">{{ ucfirst($i->type) }}</span>
                        @if($i->realisateur) · par {{ $i->realisateur->name }}@endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</article>

@include('intranet._partials.lightbox')
</div>
@endsection
