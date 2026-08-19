@extends('layouts.app')
@section('title', 'Objectifs & KPI — Tableau de bord')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Objectifs & KPI</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #DB2777;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#DB2777,#BE185D);"><i class="fas fa-bullseye"></i></span>
                Objectifs & KPI
            </h1>
            <p class="page-subtitle">Pilotage stratégique · OKR & indicateurs de performance</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('objectifs.objectifs.index') }}" class="btn btn-light"><i class="fas fa-bullseye me-1"></i> Objectifs</a>
            <a href="{{ route('objectifs.kpi.index') }}" class="btn btn-light"><i class="fas fa-chart-line me-1"></i> KPI</a>
            <a href="{{ route('objectifs.evaluations.index') }}" class="btn btn-light"><i class="fas fa-star-half-stroke me-1"></i> Évaluations</a>
        </div>
    </div>

    {{-- KPI globaux --}}
    <div class="pmp-kpi-grid mb-4">
        <div class="pmp-kpi-card">
            <div class="pmp-kpi-icon" style="background:#DB2777;"><i class="fas fa-bullseye"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $stats['objectifs_strategiques'] }}</div>
                <div class="pmp-kpi-label">Objectifs stratégiques</div>
            </div>
        </div>
        <div class="pmp-kpi-card">
            <div class="pmp-kpi-icon" style="background:#0891B2;"><i class="fas fa-spinner"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $stats['objectifs_actifs'] }}</div>
                <div class="pmp-kpi-label">Actifs en cours</div>
            </div>
        </div>
        <div class="pmp-kpi-card">
            <div class="pmp-kpi-icon" style="background:#16A34A;"><i class="fas fa-check-circle"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $stats['objectifs_atteints'] }}</div>
                <div class="pmp-kpi-label">Atteints</div>
            </div>
        </div>
        <div class="pmp-kpi-card" style="{{ $stats['objectifs_en_retard'] > 0 ? 'border-left:3px solid #DC2626;' : '' }}">
            <div class="pmp-kpi-icon" style="background:{{ $stats['objectifs_en_retard'] > 0 ? '#DC2626' : '#94A3B8' }};"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value" style="{{ $stats['objectifs_en_retard'] > 0 ? 'color:#DC2626;' : '' }}">{{ $stats['objectifs_en_retard'] }}</div>
                <div class="pmp-kpi-label">En retard</div>
            </div>
        </div>
        <div class="pmp-kpi-card">
            <div class="pmp-kpi-icon" style="background:#7C3AED;"><i class="fas fa-chart-line"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $stats['kpi_total'] }}</div>
                <div class="pmp-kpi-label">KPI suivis</div>
            </div>
        </div>
        <div class="pmp-kpi-card" style="{{ $stats['kpi_en_alerte'] > 0 ? 'border-left:3px solid #F59E0B;' : '' }}">
            <div class="pmp-kpi-icon" style="background:{{ $stats['kpi_en_alerte'] > 0 ? '#F59E0B' : '#94A3B8' }};"><i class="fas fa-bell"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value" style="{{ $stats['kpi_en_alerte'] > 0 ? 'color:#F59E0B;' : '' }}">{{ $stats['kpi_en_alerte'] }}</div>
                <div class="pmp-kpi-label">KPI en alerte</div>
            </div>
        </div>
    </div>

    {{-- Widget Alertes stratégiques --}}
    @if($alertesListe->count())
    <div class="contact-detail-card mb-4" style="border-top: 3px solid {{ $compteurAlertes['critiques'] > 0 ? '#DC2626' : ($compteurAlertes['attention'] > 0 ? '#F59E0B' : '#0891B2') }};">
        <h4 class="contact-detail-card-title">
            <i class="fas fa-triangle-exclamation" style="color:#DC2626;"></i> Alertes stratégiques
            <span class="ms-auto d-flex gap-2" style="font-size:.7rem;">
                @if($compteurAlertes['critiques'] > 0)
                    <span class="badge" style="background:#DC2626;color:#fff;">{{ $compteurAlertes['critiques'] }} critique{{ $compteurAlertes['critiques'] > 1 ? 's' : '' }}</span>
                @endif
                @if($compteurAlertes['attention'] > 0)
                    <span class="badge" style="background:#F59E0B;color:#fff;">{{ $compteurAlertes['attention'] }} attention</span>
                @endif
                @if($compteurAlertes['info'] > 0)
                    <span class="badge" style="background:#0891B2;color:#fff;">{{ $compteurAlertes['info'] }} info</span>
                @endif
            </span>
        </h4>
        <div class="row g-2">
            @foreach($alertesListe as $a)
            <div class="col-md-6">
                <a href="{{ $a['url'] }}" style="text-decoration:none;color:inherit;display:block;">
                    <div style="background:#F8FAFC;border-radius:8px;padding:.65rem .85rem;border-left:3px solid {{ $a['couleur'] }};">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas {{ $a['icone'] }}" style="color:{{ $a['couleur'] }};font-size:.85rem;margin-top:2px;"></i>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:.78rem;font-weight:600;color:#0F172A;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $a['titre'] }}</div>
                                <div style="font-size:.7rem;color:#64748B;margin-top:1px;">{{ $a['message'] }}</div>
                                @if($a['responsable'])
                                    <div style="font-size:.65rem;color:#94A3B8;margin-top:2px;"><i class="fas fa-user me-1"></i>{{ $a['responsable'] }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @if($compteurAlertes['total'] > $alertesListe->count())
            <div class="text-center mt-3"><small class="text-muted">+{{ $compteurAlertes['total'] - $alertesListe->count() }} autres alertes</small></div>
        @endif
    </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Top objectifs stratégiques --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title">
                    <i class="fas fa-bullseye" style="color:#DB2777;"></i> Objectifs stratégiques
                    <a href="{{ route('objectifs.objectifs.index', ['type' => 'strategique']) }}" class="ms-auto" style="font-size:.72rem;color:#DB2777;text-decoration:none;font-weight:600;">Voir tous →</a>
                </h4>
                @if($objectifsStrategiques->count())
                <div class="d-flex flex-column gap-3">
                    @foreach($objectifsStrategiques as $o)
                    <a href="{{ route('objectifs.objectifs.show', $o) }}" style="text-decoration:none;color:inherit;">
                        <div style="background:#F8FAFC;border-radius:9px;padding:.85rem;border-left:3px solid {{ $o->couleur_affichage }};">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas {{ $o->icone_affichage }}" style="color:{{ $o->couleur_affichage }};"></i>
                                @if($o->code)<code style="font-size:.65rem;">{{ $o->code }}</code>@endif
                                <strong style="font-size:.88rem;flex:1;">{{ $o->titre }}</strong>
                                <span class="opp-stage-badge" style="background:{{ $o->statut_couleur }};font-size:.6rem;">{{ $o->statut_libelle }}</span>
                                @if($o->estEnRetard())<span class="opp-stage-badge" style="background:#DC2626;font-size:.55rem;">En retard</span>@endif
                            </div>
                            <div class="tache-detail-progress">
                                <div class="tache-detail-progress-bar">
                                    <div class="tache-detail-progress-fill" style="width:{{ $o->progression }}%;background:{{ $o->couleur_affichage }};"></div>
                                </div>
                                <span class="tache-detail-progress-pct">{{ $o->progression }}%</span>
                            </div>
                            <div class="d-flex gap-3 mt-2" style="font-size:.7rem;color:#64748B;">
                                @if($o->responsable)<span><i class="fas fa-user"></i> {{ $o->responsable->prenoms }} {{ $o->responsable->name }}</span>@endif
                                <span><i class="fas fa-bullseye"></i> {{ $o->sousObjectifs->count() }} sous-obj.</span>
                                <span><i class="fas fa-chart-line"></i> {{ $o->kpi->count() }} KPI</span>
                                @if($o->date_fin)<span><i class="far fa-calendar"></i> Échéance : {{ $o->date_fin->format('d/m/Y') }}</span>@endif
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-muted small mb-0">Aucun objectif stratégique défini.</p>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            {{-- KPI critiques --}}
            @if($kpisCritiques->count())
            <div class="contact-detail-card mb-4" style="border-left:4px solid #DC2626;">
                <h4 class="contact-detail-card-title"><i class="fas fa-bell" style="color:#DC2626;"></i> KPI critiques (&lt; 50%)</h4>
                @foreach($kpisCritiques as $k)
                <a href="{{ route('objectifs.kpi.show', $k) }}" style="text-decoration:none;color:inherit;">
                    <div class="dash-risk-item">
                        <div class="dash-risk-score" style="background:#DC2626;">{{ $k->progression }}%</div>
                        <div>
                            <div class="dash-risk-title">{{ Str::limit($k->titre, 35) }}</div>
                            <div class="dash-risk-project">{{ $k->valeur_actuelle }} {{ $k->unite }} / cible {{ $k->valeur_cible }}</div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

            {{-- KPI au top --}}
            @if($kpisAuTop->count())
            <div class="contact-detail-card mb-4" style="border-left:4px solid #16A34A;">
                <h4 class="contact-detail-card-title"><i class="fas fa-trophy" style="color:#16A34A;"></i> KPI au top (≥ 90%)</h4>
                @foreach($kpisAuTop as $k)
                <a href="{{ route('objectifs.kpi.show', $k) }}" style="text-decoration:none;color:inherit;">
                    <div class="dash-risk-item">
                        <div class="dash-risk-score" style="background:#16A34A;">{{ $k->progression }}%</div>
                        <div>
                            <div class="dash-risk-title">{{ Str::limit($k->titre, 35) }}</div>
                            <div class="dash-risk-project">{{ $k->valeur_actuelle }} {{ $k->unite }} / cible {{ $k->valeur_cible }}</div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

            {{-- Répartition par portée --}}
            <div class="contact-detail-card">
                <h4 class="contact-detail-card-title"><i class="fas fa-chart-pie"></i> Répartition par portée</h4>
                @php $totalObj = $repartitionPortee->sum() ?: 1; @endphp
                @foreach(['organisation' => 'Organisation', 'service' => 'Service', 'equipe' => 'Équipe', 'individuel' => 'Individuel'] as $key => $libelle)
                @php $count = $repartitionPortee[$key] ?? 0; $pct = round(($count / $totalObj) * 100); @endphp
                <div class="mb-2">
                    <div class="d-flex justify-content-between" style="font-size:.78rem;">
                        <span>{{ $libelle }}</span>
                        <span><strong>{{ $count }}</strong> · {{ $pct }}%</span>
                    </div>
                    <div class="tache-progress-bar">
                        <div class="tache-progress-fill" style="width:{{ $pct }}%;background:#7C3AED;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
