@extends('layouts.app')
@section('title', $user->prenoms . ' ' . $user->name . ' — Annuaire')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item">Annuaire</li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.annuaire.collaborateurs.index') }}">Collaborateurs</a></li>
    <li class="breadcrumb-item active">{{ $user->prenoms }} {{ $user->name }}</li>
</ol>
@endsection

@section('content')
@php
    $palette = ['#7C3AED','#059669','#D97706','#0891B2','#0D9488','#4F46E5','#DC2626','#16A34A'];
    $color = $palette[$user->id % 8];
    $initials = strtoupper(substr($user->prenoms ?? $user->name, 0, 1)) . strtoupper(substr($user->name, 0, 1));
@endphp
<div class="page-intranet" style="--accent: {{ $color }};">

    <article class="contact-detail">
        <div class="contact-detail-header" style="border-top-color: {{ $color }};">
            @if($user->profile_photo_path)
            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="" class="contact-detail-photo" style="object-fit:cover;">
            @else
            <div class="contact-detail-photo contact-avatar-letters" style="background: {{ $color }}; font-size: 1.5rem;">{{ $initials }}</div>
            @endif
            <div class="contact-detail-identity">
                <div class="contact-detail-badges">
                    @if($user->matricule)<code class="opp-ref">{{ $user->matricule }}</code>@endif
                    @foreach($user->roles as $role)
                    <span class="opp-stage-badge" style="background:#7C3AED;">{{ $role->name }}</span>
                    @endforeach
                    @if($user->statut == 1)
                    <span class="opp-stage-badge" style="background:#16A34A;">Actif</span>
                    @else
                    <span class="opp-stage-badge" style="background:#94A3B8;">Inactif</span>
                    @endif
                </div>
                <h1 class="contact-detail-name">{{ $user->prenoms }} {{ $user->name }}</h1>
                @if($user->poste)<p class="contact-detail-poste">{{ $user->poste }}</p>@endif
            </div>
            <div class="contact-detail-actions">
                @if($user->email)
                <a href="mailto:{{ $user->email }}" class="btn btn-light"><i class="fas fa-envelope me-1"></i> Email</a>
                @endif
                @if($user->contact)
                <a href="tel:{{ $user->contact }}" class="btn btn-light"><i class="fas fa-phone me-1"></i> Appeler</a>
                @endif
            </div>
        </div>

        <div class="row g-4 mt-3">
            <div class="col-lg-8">
                {{-- Bio --}}
                @if($user->bio)
                <div class="contact-detail-card mb-4">
                    <h4 class="contact-detail-card-title"><i class="fas fa-id-card"></i> À propos</h4>
                    <p style="font-size:.85rem;color:#475569;">{{ $user->bio }}</p>
                </div>
                @endif

                {{-- Services --}}
                <div class="contact-detail-card mb-4">
                    <h4 class="contact-detail-card-title"><i class="fas fa-building"></i> Services ({{ $user->services->count() }})</h4>
                    @if($user->services->count())
                    <div class="d-flex flex-column gap-2">
                        @foreach($user->services as $s)
                        <a href="{{ route('intranet.annuaire.services.show', $s) }}" class="d-flex align-items-center gap-2 p-2" style="background:#F8FAFC;border-radius:8px;text-decoration:none;color:inherit;border-left:3px solid {{ $s->couleur ?? '#0D9488' }};">
                            <i class="fas {{ $s->icone ?? 'fa-building' }}" style="color:{{ $s->couleur ?? '#0D9488' }};"></i>
                            <div style="flex:1;">
                                <strong style="font-size:.85rem;">{{ $s->nom }}</strong>
                                @if($s->pivot->poste)<div style="font-size:.7rem;color:#64748B;">{{ $s->pivot->poste }}</div>@endif
                            </div>
                            @if($s->pivot->est_principal)<span class="opp-stage-badge" style="background:#16A34A;font-size:.6rem;">Principal</span>@endif
                            @if($s->pivot->date_arrivee)<span style="font-size:.65rem;color:#64748B;">depuis {{ \Carbon\Carbon::parse($s->pivot->date_arrivee)->format('m/Y') }}</span>@endif
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted small mb-0">Pas encore affecté à un service.</p>
                    @endif
                </div>

                {{-- Lien Employee si existe --}}
                @if($user->employee)
                <div class="contact-detail-card mb-4">
                    <h4 class="contact-detail-card-title"><i class="fas fa-briefcase"></i> Dossier RH</h4>
                    <p style="font-size:.85rem;">Dossier RH lié — voir module RH pour le détail (carrière, paie, formations).</p>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="contact-detail-card mb-4">
                    <h4 class="contact-detail-card-title"><i class="fas fa-address-card"></i> Coordonnées</h4>
                    <ul class="contact-info-list">
                        @if($user->email)<li><i class="fas fa-envelope"></i> <a href="mailto:{{ $user->email }}">{{ $user->email }}</a></li>@endif
                        @if($user->email_interne)<li><i class="fas fa-at"></i> {{ $user->email_interne }}</li>@endif
                        @if($user->contact)<li><i class="fas fa-phone"></i> {{ $user->contact }}</li>@endif
                        @if($user->bureau)<li><i class="fas fa-door-open"></i> Bureau {{ $user->bureau }}</li>@endif
                        @if($user->linkedin)<li><i class="fab fa-linkedin"></i> <a href="{{ $user->linkedin }}" target="_blank">LinkedIn</a></li>@endif
                    </ul>
                </div>

                <div class="contact-detail-card">
                    <h4 class="contact-detail-card-title"><i class="far fa-calendar"></i> Carrière</h4>
                    <ul class="contact-info-list">
                        @if($user->date_embauche)<li><strong>Embauche :</strong> {{ $user->date_embauche->format('d/m/Y') }} ({{ $user->date_embauche->diffForHumans(['parts' => 1]) }})</li>@endif
                        @if($user->date_naissance)<li><strong>Date de naissance :</strong> {{ $user->date_naissance->format('d/m/Y') }} ({{ $user->date_naissance->age }} ans)</li>@endif
                        <li><strong>Compte créé :</strong> {{ $user->created_at->format('d/m/Y') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </article>
</div>
@endsection
