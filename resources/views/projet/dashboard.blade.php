@extends('layouts.app')
@section('title', 'Projets / Tâches — Dashboard')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item active">Projets / Tâches</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-diagram-project"></i></span>
                Tableau de bord Projets
            </h1>
            <p class="page-subtitle">Pilotage et aide à la décision — vue consolidée</p>
        </div>
        <a href="{{ route('intranet.projets.create') }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);">
            <i class="fas fa-plus me-2"></i> Nouveau projet
        </a>
    </div>

    {{-- ═══ KPI PRINCIPAUX ═══════════════════════════════ --}}
    <div class="pmp-kpi-grid mb-4">
        <div class="pmp-kpi-card">
            <div class="pmp-kpi-icon" style="background:#0D9488;"><i class="fas fa-diagram-project"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $stats['total'] }}</div>
                <div class="pmp-kpi-label">Projets</div>
            </div>
        </div>
        <div class="pmp-kpi-card" style="border-left:3px solid #0891B2;">
            <div class="pmp-kpi-icon" style="background:#0891B2;"><i class="fas fa-spinner"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $stats['en_cours'] }}</div>
                <div class="pmp-kpi-label">En cours</div>
            </div>
        </div>
        <div class="pmp-kpi-card">
            <div class="pmp-kpi-icon" style="background:#16A34A;"><i class="fas fa-check-circle"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $stats['termines'] }}</div>
                <div class="pmp-kpi-label">Terminés</div>
            </div>
        </div>
        <div class="pmp-kpi-card" style="{{ $stats['en_retard'] > 0 ? 'border-left:3px solid #DC2626;' : '' }}">
            <div class="pmp-kpi-icon" style="background:{{ $stats['en_retard'] > 0 ? '#DC2626' : '#94A3B8' }};"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value" style="{{ $stats['en_retard'] > 0 ? 'color:#DC2626;' : '' }}">{{ $stats['en_retard'] }}</div>
                <div class="pmp-kpi-label">En retard</div>
            </div>
        </div>
        <div class="pmp-kpi-card">
            <div class="pmp-kpi-icon" style="background:#7C3AED;"><i class="fas fa-list-check"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $tachesTerminees }}/{{ $tachesTotal }}</div>
                <div class="pmp-kpi-label">Tâches terminées</div>
            </div>
        </div>
        <div class="pmp-kpi-card" style="{{ $tachesEnRetard > 0 ? 'border-left:3px solid #F59E0B;' : '' }}">
            <div class="pmp-kpi-icon" style="background:{{ $tachesEnRetard > 0 ? '#F59E0B' : '#94A3B8' }};"><i class="fas fa-clock"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $tachesEnRetard }}</div>
                <div class="pmp-kpi-label">Tâches en retard</div>
            </div>
        </div>
    </div>

    {{-- ═══ ALERTES ══════════════════════════════════════ --}}
    @if($tachesUrgentes > 0 || $jalonsEnRetard > 0 || $risquesCritiques->count() > 0)
    <div class="dash-alerts mb-4">
        <h3 class="dash-section-title"><i class="fas fa-bell" style="color:#DC2626;"></i> Alertes nécessitant votre attention</h3>
        <div class="dash-alerts-grid">
            @if($tachesUrgentes > 0)
            <div class="dash-alert-card urgent">
                <div class="dash-alert-icon"><i class="fas fa-bolt"></i></div>
                <div><strong>{{ $tachesUrgentes }} tâche(s) urgente(s)</strong><br><small>Priorité maximale, action requise</small></div>
            </div>
            @endif
            @if($jalonsEnRetard > 0)
            <div class="dash-alert-card warning">
                <div class="dash-alert-icon"><i class="fas fa-flag"></i></div>
                <div><strong>{{ $jalonsEnRetard }} jalon(s) en retard</strong><br><small>Dates dépassées, replanification nécessaire</small></div>
            </div>
            @endif
            @foreach($risquesCritiques->take(3) as $rc)
            <div class="dash-alert-card critical">
                <div class="dash-alert-icon"><i class="fas fa-shield-exclamation"></i></div>
                <div>
                    <strong>Risque critique : {{ Str::limit($rc->titre, 40) }}</strong>
                    <br><small>{{ $rc->projet?->nom }} — Score {{ $rc->probabilite * $rc->impact }}</small>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="row g-4">
        {{-- ═══ COLONNE GAUCHE ═══════════════════════════ --}}
        <div class="col-lg-8">

            {{-- Budget consolidé --}}
            @if($budgetTotal > 0)
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-coins"></i> Budget consolidé</h4>
                <div class="row g-3 text-center mb-3">
                    <div class="col-4">
                        <div style="font-size:1.3rem;font-weight:800;color:#0F172A;">{{ number_format($budgetTotal, 0, ',', ' ') }}</div>
                        <small class="text-muted">Budget total approuvé</small>
                    </div>
                    <div class="col-4">
                        <div style="font-size:1.3rem;font-weight:800;color:#D97706;">{{ number_format($budgetConsomme, 0, ',', ' ') }}</div>
                        <small class="text-muted">Consommé</small>
                    </div>
                    <div class="col-4">
                        @php $restant = $budgetTotal - $budgetConsomme; @endphp
                        <div style="font-size:1.3rem;font-weight:800;color:{{ $restant >= 0 ? '#16A34A' : '#DC2626' }};">{{ number_format($restant, 0, ',', ' ') }}</div>
                        <small class="text-muted">Restant</small>
                    </div>
                </div>
                @php $pctBudget = $budgetTotal > 0 ? min(100, round($budgetConsomme / $budgetTotal * 100)) : 0; @endphp
                <div class="tache-detail-progress">
                    <div class="tache-detail-progress-bar">
                        <div class="tache-detail-progress-fill" style="width:{{ $pctBudget }}%;background:{{ $pctBudget > 90 ? '#DC2626' : ($pctBudget > 70 ? '#F59E0B' : '#16A34A') }};"></div>
                    </div>
                    <span class="tache-detail-progress-pct">{{ $pctBudget }}%</span>
                </div>
            </div>
            @endif

            {{-- Tâches en alerte --}}
            @if($tachesAlerte->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-triangle-exclamation" style="color:#DC2626;"></i> Tâches en retard</h4>
                <div class="tache-list">
                    @foreach($tachesAlerte as $t)
                    <a href="{{ route('intranet.taches.show', $t) }}" class="tache-item en-retard" style="text-decoration:none;">
                        <div class="tache-priority" style="background:{{ $t->priorite_couleur }};"></div>
                        <div class="tache-check"></div>
                        <div class="tache-body">
                            <div class="tache-title">{{ $t->titre }}</div>
                            <div class="tache-meta">
                                <span><i class="fas fa-diagram-project"></i> {{ $t->projet?->nom }}</span>
                                @if($t->responsable)<span><i class="fas fa-user"></i> {{ $t->responsable->prenoms }}</span>@endif
                            </div>
                        </div>
                        <div class="tache-progress-col">
                            <div class="tache-progress-bar"><div class="tache-progress-fill" style="width:{{ $t->avancement }}%;background:{{ $t->statut_couleur }};"></div></div>
                            <span class="tache-progress-pct">{{ $t->avancement }}%</span>
                        </div>
                        <div class="tache-deadline text-danger fw-bold">
                            <i class="far fa-calendar"></i> {{ $t->date_fin?->format('d/m/Y') }}
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Tous les projets --}}
            <div class="contact-detail-card">
                <h4 class="contact-detail-card-title"><i class="fas fa-folder-tree"></i> Projets ({{ $projets->count() }})</h4>
                @if($projets->count())
                <div class="projets-grid" style="gap:.85rem;">
                    @foreach($projets as $p)
                    <a href="{{ route('projet.overview', $p) }}" class="projet-card" style="--accent: {{ $p->statut_couleur }};">
                        <div class="projet-card-header">
                            <div class="projet-card-badges">
                                @if($p->statut)<span class="opp-stage-badge" style="background:{{ $p->statut_couleur }};font-size:.6rem;">{{ $p->statut->libelle }}</span>@endif
                                @if($p->estEnRetard())<span class="contact-tag-lg hot" style="font-size:.5rem;padding:.1rem .3rem;">⚠️</span>@endif
                            </div>
                            @if($p->code_projet)<code class="projet-code">{{ $p->code_projet }}</code>@endif
                        </div>
                        <div class="projet-card-body">
                            <h3 class="projet-card-title">{{ $p->nom }}</h3>
                        </div>
                        <div class="projet-card-progress">
                            <div class="tache-progress-bar">
                                <div class="tache-progress-fill" style="width:{{ $p->avancement }}%;background:{{ $p->statut_couleur }};"></div>
                            </div>
                            <span class="tache-progress-pct">{{ $p->avancement }}%</span>
                        </div>
                        <div class="projet-card-footer">
                            <div class="projet-card-meta">
                                @if($p->chefProjet)<span><i class="fas fa-user-tie"></i> {{ $p->chefProjet->prenoms }}</span>@endif
                                <span><i class="fas fa-list-check"></i> {{ $p->taches_count }}</span>
                            </div>
                            @if($p->date_fin)
                            <span class="projet-card-date {{ $p->estEnRetard() ? 'text-danger fw-bold' : '' }}">
                                <i class="far fa-calendar"></i> {{ $p->date_fin->format('d/m') }}
                            </span>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-muted small">Aucun projet pour le moment.</p>
                @endif
            </div>
        </div>

        {{-- ═══ COLONNE DROITE ═══════════════════════════ --}}
        <div class="col-lg-4">

            {{-- Répartition par statut --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-chart-pie"></i> Répartition</h4>
                <div class="dash-repartition">
                    @php
                        $statutColors = [
                            'En cours' => '#0891B2', 'Non démarré' => '#94A3B8',
                            'Terminé' => '#16A34A', 'En pause' => '#F59E0B',
                            'Annulé' => '#DC2626', 'Indéfini' => '#CBD5E1',
                        ];
                    @endphp
                    @foreach($repartitionStatut as $statut => $count)
                    <div class="dash-rep-item">
                        <div class="dash-rep-bar">
                            <div class="dash-rep-fill" style="width:{{ $stats['total'] > 0 ? round($count / $stats['total'] * 100) : 0 }}%;background:{{ $statutColors[$statut] ?? '#94A3B8' }};"></div>
                        </div>
                        <div class="dash-rep-label">
                            <span class="dash-rep-dot" style="background:{{ $statutColors[$statut] ?? '#94A3B8' }};"></span>
                            {{ $statut }}
                        </div>
                        <div class="dash-rep-value">{{ $count }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Jalons prochains --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-flag-checkered"></i> Prochains jalons</h4>
                @if($jalonsProchains->count())
                <div class="d-flex flex-column gap-2">
                    @foreach($jalonsProchains as $j)
                    <div class="pmp-jalon-mini">
                        <div class="pmp-jalon-date">
                            <div class="pmp-jalon-day">{{ $j->date_prevue->format('d') }}</div>
                            <div class="pmp-jalon-month">{{ $j->date_prevue->translatedFormat('M') }}</div>
                        </div>
                        <div>
                            <div class="pmp-jalon-title">{{ $j->titre }}</div>
                            <div class="pmp-jalon-phase">{{ $j->projet?->nom }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted small">Aucun jalon à venir.</p>
                @endif
            </div>

            {{-- Indicateurs tâches --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-list-check"></i> Tâches</h4>
                @php $pctTaches = $tachesTotal > 0 ? round($tachesTerminees / $tachesTotal * 100) : 0; @endphp
                <div class="text-center mb-3">
                    <div class="dash-donut" style="--pct: {{ $pctTaches }}; --color: #0D9488;">
                        <div class="dash-donut-inner">
                            <span class="dash-donut-value">{{ $pctTaches }}%</span>
                            <span class="dash-donut-label">terminées</span>
                        </div>
                    </div>
                </div>
                <ul class="contact-info-list">
                    <li><strong>Total :</strong> {{ $tachesTotal }}</li>
                    <li><strong>Terminées :</strong> <span style="color:#16A34A;">{{ $tachesTerminees }}</span></li>
                    <li><strong>En retard :</strong> <span style="color:#DC2626;">{{ $tachesEnRetard }}</span></li>
                    <li><strong>Urgentes :</strong> <span style="color:#F59E0B;">{{ $tachesUrgentes }}</span></li>
                </ul>
            </div>

            {{-- Risques critiques --}}
            @if($risquesCritiques->count())
            <div class="contact-detail-card" style="border-left:4px solid #DC2626;">
                <h4 class="contact-detail-card-title" style="color:#DC2626;"><i class="fas fa-shield-exclamation"></i> Risques critiques ({{ $risquesCritiques->count() }})</h4>
                @foreach($risquesCritiques->take(5) as $r)
                <div class="dash-risk-item">
                    <div class="dash-risk-score">{{ $r->probabilite * $r->impact }}</div>
                    <div>
                        <div class="dash-risk-title">{{ $r->titre }}</div>
                        <div class="dash-risk-project">{{ $r->projet?->nom }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
