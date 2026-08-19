@extends('layouts.app')
@section('title', $organisation->nom)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.organisations.index') }}">Organisations</a></li>
    <li class="breadcrumb-item active">{{ $organisation->nom }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
<article class="contact-detail">

    <div class="contact-detail-header">
        @if($organisation->logo_url)
            <img src="{{ $organisation->logo_url }}" alt="" class="contact-detail-photo">
        @else
            <div class="contact-detail-photo contact-avatar-letters" style="background:#7C3AED;">{{ $organisation->initiales }}</div>
        @endif

        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                @if($organisation->est_client)<span class="badge-client">Client</span>@endif
                @if($organisation->est_prospect && !$organisation->est_client)<span class="badge-prospect">Prospect</span>@endif
                @if($organisation->etiquette === 'hot')<span class="contact-tag-lg hot">🔥 Hot</span>@endif
                @if($organisation->etiquette === 'warm')<span class="contact-tag-lg warm">☀️ Warm</span>@endif
                @if($organisation->etiquette === 'cold')<span class="contact-tag-lg cold">❄️ Cold</span>@endif
            </div>
            <h1 class="contact-detail-name">
                {{ $organisation->nom_affichage }}
                <span class="badge bg-{{ $organisation->type_couleur }} ms-2" style="font-size:.55em;vertical-align:middle;">
                    <i class="fas {{ $organisation->type_icone }} me-1"></i>{{ $organisation->type_libelle }}
                </span>
                @if($organisation->statut_relation_libelle)
                    <span class="badge bg-{{ $organisation->statut_relation_couleur }} ms-1" style="font-size:.55em;vertical-align:middle;">
                        {{ $organisation->statut_relation_libelle }}
                    </span>
                @endif
            </h1>
            @if($organisation->code)
                <div class="contact-detail-poste"><strong>Code :</strong> {{ $organisation->code }}</div>
            @endif
            @if($organisation->secteur)
                <div class="contact-detail-poste"><i class="fas {{ $organisation->secteur->icone }}"></i> {{ $organisation->secteur->nom }}</div>
            @endif
            @if($organisation->description)
                <div class="text-muted small mt-2 mb-0">{!! $organisation->description !!}</div>
            @endif
        </div>

        <div class="contact-detail-actions">
            @can('update:organisation_crm')
            <a href="{{ route('intranet.organisations.edit', $organisation) }}" class="btn btn-intranet">
                <i class="fas fa-pen-to-square me-2"></i> Modifier
            </a>
            @endcan
        </div>
    </div>

    <div class="contact-detail-grid">
        @if(in_array($organisation->type, ['client','fournisseur','investisseur','administration']))
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-file-invoice"></i> Informations légales & bancaires</h4>
            <ul class="contact-info-list">
                @if($organisation->raison_sociale)<li><strong>Raison sociale :</strong> {{ $organisation->raison_sociale }}</li>@endif
                @if($organisation->forme_juridique)<li><strong>Forme juridique :</strong> {{ $organisation->forme_juridique }}</li>@endif
                @if($organisation->nif)<li><strong>NIF :</strong> {{ $organisation->nif }}</li>@endif
                @if($organisation->rccm)<li><strong>RCCM :</strong> {{ $organisation->rccm }}</li>@endif
                @if($organisation->rib)<li><strong>RIB :</strong> <code>{{ $organisation->rib }}</code></li>@endif
                @if($organisation->banque)<li><strong>Banque :</strong> {{ $organisation->banque }}</li>@endif
                <li><strong>Statut :</strong>
                    @if($organisation->statut == 1)<span class="badge bg-success">Actif</span>
                    @else<span class="badge bg-secondary">Inactif</span>@endif
                </li>
            </ul>
            @if($organisation->contact_principal_nom || $organisation->contact_principal_email || $organisation->contact_principal_telephone)
                <hr>
                <h6 class="text-muted"><i class="fas fa-user-tie me-1"></i> Interlocuteur principal</h6>
                <ul class="contact-info-list">
                    @if($organisation->contact_principal_nom)<li>{{ $organisation->contact_principal_nom }}</li>@endif
                    @if($organisation->contact_principal_telephone)<li><i class="fas fa-phone"></i> {{ $organisation->contact_principal_telephone }}</li>@endif
                    @if($organisation->contact_principal_email)<li><i class="fas fa-envelope"></i> <a href="mailto:{{ $organisation->contact_principal_email }}">{{ $organisation->contact_principal_email }}</a></li>@endif
                </ul>
            @endif
        </div>
        @endif

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-address-card"></i> Coordonnées</h4>
            <ul class="contact-info-list">
                @if($organisation->email)<li><i class="fas fa-envelope"></i> {{ $organisation->email }}</li>@endif
                @if($organisation->telephone)<li><i class="fas fa-phone"></i> {{ $organisation->telephone }}</li>@endif
                @if($organisation->site_web)<li><i class="fas fa-globe"></i> <a href="{{ $organisation->site_web }}" target="_blank">{{ $organisation->site_web }}</a></li>@endif
                @if($organisation->linkedin)<li><i class="fab fa-linkedin"></i> <a href="{{ $organisation->linkedin }}" target="_blank">LinkedIn</a></li>@endif
            </ul>
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-location-dot"></i> Adresse</h4>
            <ul class="contact-info-list">
                @if($organisation->adresse)<li>{{ $organisation->adresse }}</li>@endif
                @if($organisation->code_postal || $organisation->ville)<li>{{ $organisation->code_postal }} {{ $organisation->ville }}</li>@endif
                @if($organisation->pays)<li>{{ $organisation->pays->drapeau_emoji }} {{ $organisation->pays->nom }}</li>@endif
            </ul>
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-file-invoice-dollar"></i> Informations</h4>
            <ul class="contact-info-list">
                @if($organisation->taille)<li><strong>Taille :</strong> {{ $organisation->taille }}</li>@endif
                @if($organisation->effectif)<li><strong>Effectif :</strong> {{ number_format($organisation->effectif, 0, ',', ' ') }}</li>@endif
                @if($organisation->chiffre_affaires)<li><strong>CA :</strong> {{ number_format($organisation->chiffre_affaires, 0, ',', ' ') }} €</li>@endif
                @if($organisation->siret)<li><strong>SIRET :</strong> {{ $organisation->siret }}</li>@endif
                @if($organisation->numero_tva)<li><strong>TVA :</strong> {{ $organisation->numero_tva }}</li>@endif
            </ul>
        </div>
    </div>

    @if($organisation->contacts->count())
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-users"></i> Collaborateurs ({{ $organisation->contacts->count() }})</h4>
        <div class="contacts-mini-grid">
            @foreach($organisation->contacts as $c)
            <a href="{{ route('intranet.contacts.show', $c) }}" class="contact-mini">
                @if($c->photo_url)
                    <img src="{{ $c->photo_url }}" alt="" class="contact-mini-avatar">
                @else
                    <div class="contact-mini-avatar contact-avatar-letters" style="background:#7C3AED;">{{ $c->initiales }}</div>
                @endif
                <div>
                    <div class="contact-mini-name">{{ $c->nom_complet }}</div>
                    @if($c->poste)<div class="contact-mini-poste">{{ $c->poste }}</div>@endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @include('intranet.organisations._partials.documents-attendus')

    @if($organisation->opportunites->count())
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-fire-flame-curved"></i> Opportunités ({{ $organisation->opportunites->count() }})</h4>
        <div class="opportunites-mini-list">
            @foreach($organisation->opportunites as $opp)
            <a href="{{ route('intranet.opportunites.show', $opp) }}" class="opp-mini-card">
                <span class="opp-mini-stage" style="background:{{ $opp->etape?->couleur ?? '#94A3B8' }};">{{ $opp->etape?->nom }}</span>
                <div class="opp-mini-title">{{ $opp->titre }}</div>
                @if($opp->valeur)<div class="opp-mini-amount">{{ number_format($opp->valeur, 0, ',', ' ') }} {{ $opp->devise }}</div>@endif
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($organisation->notes)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-note-sticky"></i> Notes</h4>
        <p class="mb-0">{!! nl2br(e($organisation->notes)) !!}</p>
    </div>
    @endif

    @include('intranet._partials.media-display', ['entity' => $organisation])

</article>

@include('intranet._partials.lightbox')
</div>
@endsection
