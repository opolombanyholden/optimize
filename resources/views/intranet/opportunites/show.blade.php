@extends('layouts.app')
@section('title', $opportunite->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.opportunites.index') }}">Opportunités</a></li>
    <li class="breadcrumb-item active">{{ $opportunite->titre }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
<article class="contact-detail">

    <div class="contact-detail-header">
        <div class="contact-detail-photo contact-avatar-letters" style="background:{{ $opportunite->etape?->couleur ?? '#7C3AED' }};">
            <i class="fas fa-fire-flame-curved"></i>
        </div>

        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                <code class="opp-ref">{{ $opportunite->reference }}</code>
                @if($opportunite->etape)
                    <span class="opp-stage-badge" style="background:{{ $opportunite->etape->couleur }};">{{ $opportunite->etape->nom }}</span>
                @endif
                @if($opportunite->statut === 'gagnee')<span class="badge-client">✅ Gagnée</span>
                @elseif($opportunite->statut === 'perdue')<span class="badge-prospect" style="background:#FEE2E2;color:#DC2626;">❌ Perdue</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $opportunite->titre }}</h1>
            @if($opportunite->valeur)
                <div class="opp-detail-amount">
                    {{ number_format($opportunite->valeur, 0, ',', ' ') }} {{ $opportunite->devise }}
                    <small class="text-muted">· {{ $opportunite->probabilite }}% probabilité</small>
                </div>
            @endif
        </div>

        <div class="contact-detail-actions">
            @can('update:opportunite')
            <a href="{{ route('intranet.opportunites.edit', $opportunite) }}" class="btn btn-intranet">
                <i class="fas fa-pen-to-square me-2"></i> Modifier
            </a>
            @endcan
        </div>
    </div>

    <div class="contact-detail-grid">
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-user"></i> Contact</h4>
            @if($opportunite->contact)
                <a href="{{ route('intranet.contacts.show', $opportunite->contact) }}" class="d-block">
                    <strong>{{ $opportunite->contact->nom_complet }}</strong>
                    @if($opportunite->contact->poste)<br><small class="text-muted">{{ $opportunite->contact->poste }}</small>@endif
                </a>
            @else
                <p class="text-muted small mb-0">Aucun contact associé</p>
            @endif
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-building"></i> Organisation</h4>
            @if($opportunite->organisation)
                <a href="{{ route('intranet.organisations.show', $opportunite->organisation) }}">
                    <strong>{{ $opportunite->organisation->nom }}</strong>
                </a>
            @else
                <p class="text-muted small mb-0">Aucune organisation</p>
            @endif
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-user-tie"></i> Responsable</h4>
            @if($opportunite->responsable)
                <strong>{{ $opportunite->responsable->prenoms }} {{ $opportunite->responsable->name }}</strong>
            @else
                <p class="text-muted small mb-0">Non assigné</p>
            @endif
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="far fa-calendar"></i> Dates</h4>
            <ul class="contact-info-list mb-0">
                @if($opportunite->date_creation_opp)<li><strong>Création :</strong> {{ $opportunite->date_creation_opp->format('d/m/Y') }}</li>@endif
                @if($opportunite->date_echeance)<li><strong>Échéance :</strong> {{ $opportunite->date_echeance->format('d/m/Y') }}</li>@endif
                @if($opportunite->date_cloture_reelle)<li><strong>Clôturée :</strong> {{ $opportunite->date_cloture_reelle->format('d/m/Y') }}</li>@endif
            </ul>
        </div>
    </div>

    @if($opportunite->description)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
        <div class="annonce-content p-0 border-0">{!! $opportunite->description !!}</div>
    </div>
    @endif

    @if($opportunite->raison_perte)
    <div class="contact-detail-card mt-3" style="border-left:4px solid #DC2626;">
        <h4 class="contact-detail-card-title text-danger"><i class="fas fa-circle-xmark"></i> Raison de la perte</h4>
        <p class="mb-0">{{ $opportunite->raison_perte }}</p>
    </div>
    @endif

    @if($opportunite->notes)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-note-sticky"></i> Notes</h4>
        <p class="mb-0">{!! nl2br(e($opportunite->notes)) !!}</p>
    </div>
    @endif

    @include('intranet._partials.media-display', ['entity' => $opportunite])

</article>

@include('intranet._partials.lightbox')
</div>
@endsection
