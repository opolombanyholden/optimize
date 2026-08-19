@extends('layouts.app')
@section('title', $objectif->titre)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('objectifs.dashboard') }}">Objectifs & KPI</a></li>
    <li class="breadcrumb-item"><a href="{{ route('objectifs.objectifs.index') }}">Objectifs</a></li>
    @if($objectif->parent)<li class="breadcrumb-item"><a href="{{ route('objectifs.objectifs.show', $objectif->parent) }}">{{ Str::limit($objectif->parent->titre, 25) }}</a></li>@endif
    <li class="breadcrumb-item active">{{ Str::limit($objectif->titre, 35) }}</li>
</ol>
@endsection

@section('content')
@php $color = $objectif->couleur_affichage; @endphp
<div class="page-intranet" style="--accent: {{ $color }};">

    <div class="contact-detail-header" style="border-top-color: {{ $color }};">
        <div class="contact-detail-photo" style="background: {{ $color }}; display:flex;align-items:center;justify-content:center;">
            <i class="fas {{ $objectif->icone_affichage }}" style="color:#fff;font-size:1.5rem;"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                @if($objectif->code)<code class="opp-ref">{{ $objectif->code }}</code>@endif
                <span class="opp-stage-badge" style="background:{{ $objectif->statut_couleur }};">{{ $objectif->statut_libelle }}</span>
                <span class="opp-stage-badge" style="background:#7C3AED;">{{ $objectif->portee_libelle }}</span>
                @if($objectif->estEnRetard())<span class="contact-tag-lg hot">En retard</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $objectif->titre }}</h1>
            @if($objectif->parent)
            <a href="{{ route('objectifs.objectifs.show', $objectif->parent) }}" class="contact-detail-orga"><i class="fas fa-arrow-up-from-bracket"></i> {{ $objectif->parent->titre }}</a>
            @endif
        </div>
        <div class="contact-detail-actions">
            <a href="{{ route('objectifs.objectifs.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Liste</a>
        </div>
    </div>

    {{-- Progression globale --}}
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-chart-line"></i> Progression globale</h4>
        <div class="tache-detail-progress">
            <div class="tache-detail-progress-bar">
                <div class="tache-detail-progress-fill" style="width:{{ $objectif->progression }}%;background:{{ $color }};"></div>
            </div>
            <span class="tache-detail-progress-pct">{{ $objectif->progression }}%</span>
        </div>
        <small style="font-size:.7rem;color:#94A3B8;">
            Calculée en cascade depuis sous-objectifs / KPI / projets liés.
        </small>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            {{-- Description --}}
            @if($objectif->description)
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
                <p style="font-size:.85rem;color:#475569;">{{ $objectif->description }}</p>
            </div>
            @endif

            {{-- Sous-objectifs --}}
            @if($objectif->sousObjectifs->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-bullseye"></i> Sous-objectifs ({{ $objectif->sousObjectifs->count() }})</h4>
                <div class="d-flex flex-column gap-2">
                    @foreach($objectif->sousObjectifs as $so)
                    <a href="{{ route('objectifs.objectifs.show', $so) }}" style="text-decoration:none;color:inherit;">
                        <div style="background:#F8FAFC;border-radius:8px;padding:.7rem;border-left:3px solid {{ $so->couleur_affichage }};">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas {{ $so->icone_affichage }}" style="color:{{ $so->couleur_affichage }};"></i>
                                <strong style="font-size:.82rem;flex:1;">{{ $so->titre }}</strong>
                                @if($so->ponderation)<span style="font-size:.65rem;color:#64748B;">{{ $so->ponderation }}%</span>@endif
                                <span class="opp-stage-badge" style="background:{{ $so->statut_couleur }};font-size:.55rem;">{{ $so->statut_libelle }}</span>
                            </div>
                            <div class="tache-progress-bar">
                                <div class="tache-progress-fill" style="width:{{ $so->progression }}%;background:{{ $so->couleur_affichage }};"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1" style="font-size:.65rem;color:#94A3B8;">
                                <span>{{ $so->progression }}%</span>
                                @if($so->responsable)<span>{{ $so->responsable->prenoms }} {{ $so->responsable->name }}</span>@endif
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- KPI rattachés --}}
            @if($objectif->kpi->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-chart-line"></i> KPI rattachés ({{ $objectif->kpi->count() }})</h4>
                <table class="opp-table" style="font-size:.78rem;">
                    <thead><tr><th>KPI</th><th style="text-align:right;">Actuel</th><th style="text-align:right;">Cible</th><th style="width:120px;">Progression</th><th>Tendance</th></tr></thead>
                    <tbody>
                    @foreach($objectif->kpi as $k)
                    <tr>
                        <td><a href="{{ route('objectifs.kpi.show', $k) }}"><strong>{{ $k->titre }}</strong></a></td>
                        <td style="text-align:right;">{{ rtrim(rtrim($k->valeur_actuelle, '0'), '.') }} {{ $k->unite }}</td>
                        <td style="text-align:right;">{{ rtrim(rtrim($k->valeur_cible, '0'), '.') }} {{ $k->unite }}</td>
                        <td>
                            <div class="tache-progress-bar"><div class="tache-progress-fill" style="width:{{ $k->progression }}%;background:{{ $k->progression >= 90 ? '#16A34A' : ($k->progression >= 50 ? '#0891B2' : '#DC2626') }};"></div></div>
                            <small>{{ $k->progression }}%</small>
                        </td>
                        <td>
                            @if($k->tendance === 'hausse')<i class="fas fa-arrow-trend-up text-success"></i>
                            @elseif($k->tendance === 'baisse')<i class="fas fa-arrow-trend-down text-danger"></i>
                            @else<i class="fas fa-minus text-muted"></i>@endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Projets liés --}}
            @if($objectif->projets->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-diagram-project"></i> Projets liés ({{ $objectif->projets->count() }})</h4>
                <div class="d-flex flex-column gap-2">
                    @foreach($objectif->projets as $p)
                    <a href="{{ route('projet.overview', $p) }}" class="d-flex align-items-center gap-2 p-2" style="background:#F8FAFC;border-radius:8px;text-decoration:none;color:inherit;">
                        <i class="fas fa-diagram-project" style="color:#0D9488;"></i>
                        <strong style="font-size:.82rem;flex:1;">{{ $p->nom }}</strong>
                        @if($p->statut)<span class="opp-stage-badge" style="background:{{ $p->statut_couleur }};font-size:.55rem;">{{ $p->statut->libelle }}</span>@endif
                        <span style="font-size:.7rem;color:#64748B;">{{ $p->avancement_real }}%</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Plans d'action --}}
            @if($objectif->plansAction->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title">
                    <i class="fas fa-list-ul" style="color:#6366F1;"></i> Plans d'action ({{ $objectif->plansAction->count() }})
                    <a href="{{ route('objectifs.plans-action.index') }}" class="ms-auto" style="font-size:.72rem;color:#6366F1;text-decoration:none;font-weight:600;">Voir tous</a>
                </h4>
                <div class="d-flex flex-column gap-2">
                    @foreach($objectif->plansAction as $pa)
                    <div style="background:#F8FAFC;border-radius:8px;padding:.6rem;border-left:3px solid {{ $pa->statut_couleur }};">
                        <div class="d-flex align-items-center gap-2">
                            <span class="opp-stage-badge" style="background:{{ $pa->priorite_couleur }};font-size:.55rem;">{{ ucfirst($pa->priorite) }}</span>
                            <strong style="font-size:.82rem;flex:1;">{{ $pa->titre }}</strong>
                            <span class="opp-stage-badge" style="background:{{ $pa->statut_couleur }};font-size:.55rem;">{{ $pa->statut_libelle }}</span>
                            @if($pa->estEnRetard())<span class="opp-stage-badge" style="background:#DC2626;font-size:.5rem;">Retard</span>@endif
                        </div>
                        <div class="tache-progress-bar mt-1" style="height:5px;">
                            <div class="tache-progress-fill" style="width:{{ $pa->avancement }}%;background:{{ $pa->statut_couleur }};"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1" style="font-size:.65rem;color:#94A3B8;">
                            <span>{{ $pa->avancement }}% · {{ $pa->responsable?->prenoms }} {{ $pa->responsable?->name }}</span>
                            @if($pa->date_echeance)<span>Échéance : {{ $pa->date_echeance->format('d/m/Y') }}</span>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Évaluations --}}
            @if($objectif->evaluations->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-star"></i> Évaluations ({{ $objectif->evaluations->count() }})</h4>
                <div class="d-flex flex-column gap-2">
                    @foreach($objectif->evaluations as $e)
                    <a href="{{ route('objectifs.evaluations.show', $e) }}" style="text-decoration:none;color:inherit;">
                        <div style="background:#F8FAFC;border-radius:8px;padding:.6rem;">
                            <div class="d-flex align-items-center gap-2">
                                <strong style="font-size:.8rem;flex:1;">{{ $e->utilisateur?->prenoms }} {{ $e->utilisateur?->name }}</strong>
                                @if($e->score !== null)<span style="font-size:.78rem;font-weight:700;color:{{ $e->score >= 70 ? '#16A34A' : ($e->score >= 50 ? '#F59E0B' : '#DC2626') }};">{{ $e->score }}/100</span>@endif
                                <span style="font-size:.65rem;color:#64748B;">{{ $e->date_evaluation?->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-circle-info"></i> Informations</h4>
                <ul class="contact-info-list">
                    @if($objectif->responsable)<li><strong>Responsable :</strong> <a href="{{ route('intranet.annuaire.collaborateurs.show', $objectif->responsable) }}">{{ $objectif->responsable->prenoms }} {{ $objectif->responsable->name }}</a></li>@endif
                    @if($objectif->service)<li><strong>Service :</strong> <a href="{{ route('intranet.annuaire.services.show', $objectif->service) }}">{{ $objectif->service->nom }}</a></li>@endif
                    @if($objectif->date_debut)<li><strong>Début :</strong> {{ $objectif->date_debut->format('d/m/Y') }}</li>@endif
                    @if($objectif->date_fin)<li class="{{ $objectif->estEnRetard() ? 'text-danger fw-bold' : '' }}"><strong>Échéance :</strong> {{ $objectif->date_fin->format('d/m/Y') }}</li>@endif
                    @if($objectif->ponderation)<li><strong>Pondération :</strong> {{ $objectif->ponderation }}%</li>@endif
                    @if($objectif->auteur)<li><strong>Créé par :</strong> {{ $objectif->auteur->prenoms }} {{ $objectif->auteur->name }}</li>@endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
