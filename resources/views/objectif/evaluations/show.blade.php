@extends('layouts.app')
@section('title', $evaluation->titre)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('objectifs.dashboard') }}">Objectifs & KPI</a></li>
    <li class="breadcrumb-item"><a href="{{ route('objectifs.evaluations.index') }}">Évaluations</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($evaluation->titre, 35) }}</li>
</ol>
@endsection

@section('content')
@php $color = $evaluation->score >= 70 ? '#16A34A' : ($evaluation->score >= 50 ? '#F59E0B' : '#DC2626'); @endphp
<div class="page-intranet" style="--accent: {{ $color }};">

    <div class="contact-detail-header" style="border-top-color: {{ $color }};">
        <div class="contact-detail-photo" style="background: {{ $color }}; display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-star-half-stroke" style="color:#fff;font-size:1.5rem;"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                <span class="opp-stage-badge" style="background:{{ $evaluation->statut === 'valide' ? '#16A34A' : ($evaluation->statut === 'finalise' ? '#0891B2' : '#94A3B8') }};">{{ ucfirst($evaluation->statut) }}</span>
                @if($evaluation->score !== null)<span class="opp-stage-badge" style="background:{{ $color }};">{{ $evaluation->score }}/100</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $evaluation->titre }}</h1>
            <p class="contact-detail-poste">
                Évaluation de <strong>{{ $evaluation->utilisateur?->prenoms }} {{ $evaluation->utilisateur?->name }}</strong>
                par <strong>{{ $evaluation->evaluateur?->prenoms }} {{ $evaluation->evaluateur?->name }}</strong>
                · {{ $evaluation->date_evaluation?->format('d/m/Y') }}
            </p>
        </div>
        <div class="contact-detail-actions">
            <a href="{{ route('objectifs.evaluations.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Retour</a>
        </div>
    </div>

    @if($evaluation->objectif)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-bullseye"></i> Objectif évalué</h4>
        <a href="{{ route('objectifs.objectifs.show', $evaluation->objectif) }}" style="text-decoration:none;color:inherit;">
            <strong>{{ $evaluation->objectif->titre }}</strong>
        </a>
    </div>
    @endif

    <div class="row g-4 mt-1">
        @if($evaluation->points_forts)
        <div class="col-md-6">
            <div class="contact-detail-card" style="border-left:4px solid #16A34A;">
                <h4 class="contact-detail-card-title"><i class="fas fa-thumbs-up" style="color:#16A34A;"></i> Points forts</h4>
                <p style="font-size:.85rem;color:#475569;white-space:pre-wrap;">{{ $evaluation->points_forts }}</p>
            </div>
        </div>
        @endif
        @if($evaluation->axes_amelioration)
        <div class="col-md-6">
            <div class="contact-detail-card" style="border-left:4px solid #F59E0B;">
                <h4 class="contact-detail-card-title"><i class="fas fa-arrow-up-right-dots" style="color:#F59E0B;"></i> Axes d'amélioration</h4>
                <p style="font-size:.85rem;color:#475569;white-space:pre-wrap;">{{ $evaluation->axes_amelioration }}</p>
            </div>
        </div>
        @endif
    </div>

    @if($evaluation->commentaire)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-comment"></i> Commentaire général</h4>
        <p style="font-size:.85rem;color:#475569;white-space:pre-wrap;">{{ $evaluation->commentaire }}</p>
    </div>
    @endif
</div>
@endsection
