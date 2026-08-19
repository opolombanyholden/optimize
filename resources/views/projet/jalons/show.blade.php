@extends('layouts.app')
@section('title', $jalon->titre . ' — Jalon')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.jalons.index', $projet) }}">Jalons</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($jalon->titre, 30) }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'jalons'])

    @php
        $statutColor = match($jalon->statut) { 'atteint' => '#16A34A', 'manque' => '#DC2626', 'reporte' => '#F59E0B', default => '#0D9488' };
    @endphp

    {{-- En-tête jalon --}}
    <div class="contact-detail-header mb-4" style="border-top-color: {{ $statutColor }};">
        <div class="contact-detail-photo contact-avatar-letters" style="background: {{ $statutColor }}; font-size: 1.3rem;">
            <i class="fas fa-flag-checkered"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                <span class="opp-stage-badge" style="background:{{ $statutColor }};">{{ ucfirst($jalon->statut) }}</span>
                @if($jalon->statut_cloture !== 'ouvert')
                <span class="opp-stage-badge" style="background:{{ $jalon->cloture_couleur }};">
                    <i class="fas {{ $jalon->statut_cloture === 'approuve' ? 'fa-check-circle' : ($jalon->statut_cloture === 'soumis' ? 'fa-clock' : 'fa-circle-xmark') }} me-1"></i>{{ $jalon->cloture_libelle }}
                </span>
                @endif
                @if($jalon->estEnRetard())<span class="contact-tag-lg hot">En retard</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $jalon->titre }}</h1>
            <a href="{{ route('projet.overview', $projet) }}" class="contact-detail-orga" style="text-decoration:none;">
                <i class="fas fa-diagram-project"></i> {{ $projet->nom }}
                @if($jalon->phase) · Phase : {{ $jalon->phase->nom }} @endif
            </a>
        </div>
        <div class="contact-detail-actions">
            @if($jalon->aDesValideurs())
                @if(in_array($jalon->statut_cloture, ['ouvert', 'rejete', 'revisions']))
                <button class="btn btn-light" onclick="ouvrirModalCloture({{ $jalon->id }}, 'jalon', '{{ addslashes($jalon->titre) }}', '{{ $jalon->statut_cloture }}')">
                    <i class="fas fa-paper-plane me-2"></i> Soumettre clôture
                </button>
                @endif
                @if($jalon->statut_cloture === 'soumis' && $jalon->estValideur())
                <button class="btn text-white" style="background:#16A34A;" onclick="ouvrirModalDecision({{ $jalon->id }}, 'jalon', '{{ addslashes($jalon->titre) }}', 'approuver')"><i class="fas fa-check me-2"></i> Approuver</button>
                <button class="btn text-white" style="background:#DC2626;" onclick="ouvrirModalDecision({{ $jalon->id }}, 'jalon', '{{ addslashes($jalon->titre) }}', 'rejeter')"><i class="fas fa-times me-2"></i> Rejeter</button>
                @endif
            @endif
            @if($jalon->statut === 'prevu' && (!$jalon->aDesValideurs() || $jalon->statut_cloture === 'approuve'))
            <form action="{{ route('projet.jalons.atteint', [$projet, $jalon]) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn text-white" style="background:#16A34A;"><i class="fas fa-check me-2"></i> Marquer atteint</button>
            </form>
            @endif
            {{-- Modifier / Demander modification --}}
            @if($jalon->peutModifier())
            {{-- Le bouton "Modifier" renverra vers la page jalons index avec le modal pré-ouvert --}}
            @elseif($jalon->est_verrouille)
            <button class="btn btn-warning btn-sm" onclick="demanderModification({{ $jalon->id }}, 'jalon', '{{ addslashes($jalon->titre) }}')">
                <i class="fas fa-lock me-2"></i> Demander modification
            </button>
            <button class="btn btn-outline-danger btn-sm" onclick="demanderSuppression({{ $jalon->id }}, 'jalon', '{{ addslashes($jalon->titre) }}')">
                <i class="fas fa-trash me-1"></i> Demander suppression
            </button>
            @endif
            <a href="{{ route('projet.jalons.index', $projet) }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-arrow-left me-2"></i> Retour jalons</a>
        </div>
    </div>

    @if($jalon->aDemandeEnAttente())
    <div class="alert alert-warning mb-4" style="font-size:.82rem;border-radius:9px;">
        <i class="fas fa-clock me-2"></i> <strong>Demande en attente :</strong> Une demande est en cours d'examen par un administrateur.
    </div>
    @endif

    {{-- Message rejet --}}
    @if($jalon->statut_cloture === 'rejete' && $jalon->motif_rejet)
    <div class="alert alert-danger mb-4" style="font-size:.82rem;border-radius:9px;">
        <i class="fas fa-circle-xmark me-2"></i> <strong>Clôture rejetée :</strong> {{ $jalon->motif_rejet }}
    </div>
    @elseif($jalon->statut_cloture === 'revisions' && $jalon->motif_rejet)
    <div class="alert alert-warning mb-4" style="font-size:.82rem;border-radius:9px;">
        <i class="fas fa-arrows-rotate me-2"></i> <strong>Révisions demandées :</strong> {{ $jalon->motif_rejet }}
    </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Description --}}
            @if($jalon->description)
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
                <div class="content-body" style="font-size:.85rem;">{{ $jalon->description }}</div>
            </div>
            @endif

            {{-- Médias & Pièces jointes --}}
            @include('intranet._partials.media-display', ['entity' => $jalon])

            {{-- Timeline visuelle --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-timeline"></i> Timeline</h4>
                <div class="d-flex align-items-center gap-4 py-3">
                    {{-- Date prévue --}}
                    <div class="text-center">
                        <div style="width:70px;height:70px;border-radius:50%;background:{{ $jalon->estEnRetard() ? '#FEE2E2' : '#F0FDFA' }};display:flex;flex-direction:column;align-items:center;justify-content:center;">
                            <div style="font-size:1.3rem;font-weight:800;color:{{ $jalon->estEnRetard() ? '#DC2626' : '#0D9488' }};">{{ $jalon->date_prevue->format('d') }}</div>
                            <div style="font-size:.6rem;font-weight:600;color:#64748B;">{{ $jalon->date_prevue->translatedFormat('M Y') }}</div>
                        </div>
                        <small style="font-size:.68rem;color:#64748B;margin-top:4px;display:block;">Date prévue</small>
                    </div>

                    {{-- Flèche --}}
                    <div style="flex:1;height:3px;background:{{ $jalon->statut === 'atteint' ? '#16A34A' : '#E2E8F0' }};position:relative;">
                        @if($jalon->statut === 'atteint')
                        <i class="fas fa-check-circle" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#16A34A;font-size:1.2rem;background:#fff;border-radius:50%;"></i>
                        @else
                        <i class="fas fa-arrow-right" style="position:absolute;top:50%;right:0;transform:translateY(-50%);color:#94A3B8;"></i>
                        @endif
                    </div>

                    {{-- Date réelle --}}
                    <div class="text-center">
                        @if($jalon->date_reelle)
                        <div style="width:70px;height:70px;border-radius:50%;background:#DCFCE7;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                            <div style="font-size:1.3rem;font-weight:800;color:#16A34A;">{{ $jalon->date_reelle->format('d') }}</div>
                            <div style="font-size:.6rem;font-weight:600;color:#64748B;">{{ $jalon->date_reelle->translatedFormat('M Y') }}</div>
                        </div>
                        <small style="font-size:.68rem;color:#16A34A;margin-top:4px;display:block;">Date réelle</small>
                        @else
                        <div style="width:70px;height:70px;border-radius:50%;background:#F1F5F9;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-question" style="color:#94A3B8;font-size:1.2rem;"></i>
                        </div>
                        <small style="font-size:.68rem;color:#94A3B8;margin-top:4px;display:block;">Non atteint</small>
                        @endif
                    </div>
                </div>
                @if($jalon->date_reelle && $jalon->date_prevue)
                @php $diff = $jalon->date_prevue->diffInDays($jalon->date_reelle, false); @endphp
                <div class="text-center" style="font-size:.78rem;color:{{ $diff > 0 ? '#DC2626' : '#16A34A' }};">
                    @if($diff > 0) <i class="fas fa-triangle-exclamation me-1"></i> {{ $diff }} jour(s) de retard
                    @elseif($diff < 0) <i class="fas fa-check me-1"></i> {{ abs($diff) }} jour(s) d'avance
                    @else <i class="fas fa-check-circle me-1"></i> Dans les temps
                    @endif
                </div>
                @endif
            </div>

            {{-- Historique de validation --}}
            @if($jalon->historiqueValidation->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-clock-rotate-left"></i> Historique de validation</h4>
                <div class="d-flex flex-column gap-3">
                    @foreach($jalon->historiqueValidation as $h)
                    <div class="d-flex gap-2 align-items-start">
                        <div style="width:30px;height:30px;border-radius:50%;background:{{ $h->couleur }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas {{ $h->icone }} text-white" style="font-size:.65rem;"></i>
                        </div>
                        <div>
                            <div style="font-size:.8rem;font-weight:600;">{{ $h->libelle }}</div>
                            <div style="font-size:.7rem;color:#64748B;">
                                {{ $h->user ? trim(($h->user->prenoms ?? '') . ' ' . $h->user->name) : '' }} · {{ $h->created_at->format('d/m/Y H:i') }}
                            </div>
                            @if($h->justification)<div style="font-size:.75rem;color:#475569;margin-top:2px;"><i class="fas fa-quote-left me-1" style="font-size:.55rem;"></i> {{ $h->justification }}</div>@endif
                            @if($h->commentaire)<div style="font-size:.75rem;color:#475569;margin-top:2px;"><i class="fas fa-comment me-1" style="font-size:.55rem;"></i> {{ $h->commentaire }}</div>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Informations --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-info-circle"></i> Informations</h4>
                <ul class="contact-info-list">
                    <li><strong>Statut :</strong> <span class="opp-stage-badge" style="background:{{ $statutColor }};font-size:.7rem;">{{ ucfirst($jalon->statut) }}</span></li>
                    <li><strong>Date prévue :</strong> {{ $jalon->date_prevue->format('d/m/Y') }}</li>
                    @if($jalon->date_reelle)<li><strong>Date réelle :</strong> <span style="color:#16A34A;">{{ $jalon->date_reelle->format('d/m/Y') }}</span></li>@endif
                    @if($jalon->phase)<li><strong>Phase :</strong> <a href="{{ route('projet.wbs.show', [$projet, $jalon->phase]) }}">{{ $jalon->phase->nom }}</a></li>@endif
                    @if($jalon->auteur)<li><strong>Créé par :</strong> {{ $jalon->auteur->prenoms }} {{ $jalon->auteur->name }}</li>@endif
                    <li><strong>Créé le :</strong> {{ $jalon->created_at->format('d/m/Y H:i') }}</li>
                </ul>
            </div>

            {{-- Valideurs --}}
            @if($jalon->valideurs->count())
            <div class="contact-detail-card mb-4" style="border-left:4px solid #6366F1;">
                <h4 class="contact-detail-card-title"><i class="fas fa-user-check" style="color:#6366F1;"></i> Valideurs de clôture</h4>
                <div class="d-flex flex-column gap-1">
                    @foreach($jalon->valideurs as $v)
                    <span class="opp-stage-badge" style="background:#6366F1;font-size:.7rem;">
                        <i class="fas {{ $v->valideur_type === 'user' ? 'fa-user' : 'fa-users' }} me-1"></i>{{ $v->nom_affichage }}
                    </span>
                    @endforeach
                </div>
                @if($jalon->justification_cloture)
                <div class="mt-2 p-2" style="background:#F8FAFC;border-radius:6px;font-size:.75rem;">
                    <strong>Justification :</strong><br>{{ $jalon->justification_cloture }}
                </div>
                @endif
            </div>
            @endif

            {{-- Projet --}}
            <div class="contact-detail-card">
                <h4 class="contact-detail-card-title"><i class="fas fa-diagram-project"></i> Projet</h4>
                <a href="{{ route('projet.overview', $projet) }}" style="text-decoration:none;">
                    <div class="d-flex align-items-center gap-2 p-2" style="background:#F8FAFC;border-radius:8px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:{{ $projet->statut_couleur }};display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-diagram-project text-white" style="font-size:.8rem;"></i>
                        </div>
                        <div>
                            <div style="font-size:.82rem;font-weight:700;color:#0F172A;">{{ $projet->nom }}</div>
                            @if($projet->statut)<div style="font-size:.68rem;color:#64748B;">{{ $projet->statut->libelle }}</div>@endif
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Lightbox pour les images --}}
@include('intranet._partials.lightbox')

{{-- Modales clôture --}}
@include('projet._partials.modales-cloture')
@include('projet._partials.modales-protection')

@endsection
