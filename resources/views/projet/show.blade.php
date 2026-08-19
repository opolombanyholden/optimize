@extends('layouts.app')
@section('title', $projet->nom . ' — Dashboard')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item active">{{ $projet->nom }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: {{ $projet->statut_couleur }};">

    @include("projet._partials.projet-header", ["projet" => $projet, "currentPage" => "overview"])

    {{-- En-tête --}}
    <div class="contact-detail-header mb-4" style="border-top-color: {{ $projet->statut_couleur }};">
        <div class="contact-detail-photo contact-avatar-letters" style="background: {{ $projet->statut_couleur }}; font-size: 1.5rem;">
            <i class="fas fa-diagram-project"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                @if($projet->code_projet)<code class="opp-ref">{{ $projet->code_projet }}</code>@endif
                @if($projet->statut)<span class="opp-stage-badge" style="background:{{ $projet->statut_couleur }};">{{ $projet->statut->libelle }}</span>@endif
                @if($projet->priorite)<span class="opp-stage-badge" style="background:{{ $projet->priorite_couleur }};">{{ $projet->priorite->libelle }}</span>@endif
                @if($projet->objectif)
                <span class="opp-stage-badge" style="background:#7C3AED;"><i class="fas fa-bullseye me-1"></i>{{ Str::limit($projet->objectif->titre, 25) }}</span>
                @endif
                @if($projet->estEnRetard())<span class="contact-tag-lg hot">En retard</span>@endif
                {{-- Badge clôture projet --}}
                @if($projet->statut_cloture !== 'ouvert')
                <span class="opp-stage-badge" style="background:{{ $projet->cloture_couleur }};">
                    <i class="fas {{ $projet->statut_cloture === 'approuve' ? 'fa-check-circle' : ($projet->statut_cloture === 'soumis' ? 'fa-clock' : ($projet->statut_cloture === 'rejete' ? 'fa-circle-xmark' : 'fa-arrows-rotate')) }} me-1"></i>{{ $projet->cloture_libelle }}
                </span>
                @endif
            </div>
            <h1 class="contact-detail-name">{{ $projet->nom }}</h1>
        </div>
        <div class="contact-detail-actions">
            {{-- Boutons workflow clôture projet --}}
            @if($projet->aDesValideurs())
                @if(in_array($projet->statut_cloture, ['ouvert', 'rejete', 'revisions']))
                <button class="btn btn-light" onclick="ouvrirModalCloture({{ $projet->id }}, 'projet', '{{ addslashes($projet->nom) }}', '{{ $projet->statut_cloture }}')">
                    <i class="fas fa-paper-plane me-2"></i> Soumettre clôture
                </button>
                @endif
                @if($projet->statut_cloture === 'soumis' && $projet->estValideur())
                <button class="btn text-white" style="background:#16A34A;" onclick="ouvrirModalDecision({{ $projet->id }}, 'projet', '{{ addslashes($projet->nom) }}', 'approuver')">
                    <i class="fas fa-check me-2"></i> Approuver
                </button>
                <button class="btn text-white" style="background:#DC2626;" onclick="ouvrirModalDecision({{ $projet->id }}, 'projet', '{{ addslashes($projet->nom) }}', 'rejeter')">
                    <i class="fas fa-times me-2"></i> Rejeter
                </button>
                @endif
                <button class="btn btn-light" onclick="voirHistorique({{ $projet->id }}, 'projet')" title="Historique validation">
                    <i class="fas fa-clock-rotate-left"></i>
                </button>
            @else
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalValideursProjet" title="Définir les valideurs du projet">
                    <i class="fas fa-user-check me-2"></i> Valideurs
                </button>
            @endif
            @if($projet->peutModifier())
            <a href="{{ route('intranet.projets.edit', $projet) }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-pen-to-square me-2"></i> Modifier</a>
            @elseif($projet->est_verrouille)
            <button class="btn btn-warning btn-sm" onclick="demanderModification({{ $projet->id }}, 'projet', '{{ addslashes($projet->nom) }}')">
                <i class="fas fa-lock me-2"></i> Demander modification
            </button>
            @endif
        </div>
    </div>

    {{-- Message rejet/révisions projet --}}
    @if($projet->statut_cloture === 'rejete' && $projet->motif_rejet)
    <div class="alert alert-danger mb-4" style="font-size:.82rem;border-radius:9px;">
        <i class="fas fa-circle-xmark me-2"></i> <strong>Clôture rejetée :</strong> {{ $projet->motif_rejet }}
    </div>
    @elseif($projet->statut_cloture === 'revisions' && $projet->motif_rejet)
    <div class="alert alert-warning mb-4" style="font-size:.82rem;border-radius:9px;">
        <i class="fas fa-arrows-rotate me-2"></i> <strong>Révisions demandées :</strong> {{ $projet->motif_rejet }}
    </div>
    @endif

    {{-- Avancement --}}
    <div class="contact-detail-card mb-4">
        <h4 class="contact-detail-card-title"><i class="fas fa-chart-line"></i> Avancement global</h4>
        <div class="tache-detail-progress">
            <div class="tache-detail-progress-bar">
                <div class="tache-detail-progress-fill" style="width:{{ $kpi['avancement'] }}%;background:{{ $projet->statut_couleur }};"></div>
            </div>
            <span class="tache-detail-progress-pct">{{ $kpi['avancement'] }}%</span>
        </div>
    </div>

    {{-- KPI principaux --}}
    <div class="pmp-kpi-grid mb-4">
        <div class="pmp-kpi-card">
            <div class="pmp-kpi-icon" style="background:#0D9488;"><i class="fas fa-list-check"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $kpi['taches_terminees'] }}/{{ $kpi['taches_total'] }}</div>
                <div class="pmp-kpi-label">Tâches terminées</div>
            </div>
        </div>
        <div class="pmp-kpi-card" style="{{ $kpi['taches_en_retard'] > 0 ? 'border-left:3px solid #DC2626;' : '' }}">
            <div class="pmp-kpi-icon" style="background:{{ $kpi['taches_en_retard'] > 0 ? '#DC2626' : '#94A3B8' }};"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value" style="{{ $kpi['taches_en_retard'] > 0 ? 'color:#DC2626;' : '' }}">{{ $kpi['taches_en_retard'] }}</div>
                <div class="pmp-kpi-label">Tâches en retard</div>
            </div>
        </div>
        <div class="pmp-kpi-card">
            <div class="pmp-kpi-icon" style="background:#F59E0B;"><i class="fas fa-flag-checkered"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $kpi['jalons_atteints'] }}/{{ $kpi['jalons_total'] }}</div>
                <div class="pmp-kpi-label">Jalons atteints</div>
            </div>
        </div>
        <div class="pmp-kpi-card" style="{{ $kpi['risques_critiques'] > 0 ? 'border-left:3px solid #DC2626;' : '' }}">
            <div class="pmp-kpi-icon" style="background:{{ $kpi['risques_critiques'] > 0 ? '#DC2626' : '#94A3B8' }};"><i class="fas fa-shield-halved"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $kpi['risques_ouverts'] }} <small style="color:#DC2626;">({{ $kpi['risques_critiques'] }} crit.)</small></div>
                <div class="pmp-kpi-label">Risques ouverts</div>
            </div>
        </div>
        <div class="pmp-kpi-card" style="{{ $kpi['problemes_ouverts'] > 0 ? 'border-left:3px solid #F59E0B;' : '' }}">
            <div class="pmp-kpi-icon" style="background:{{ $kpi['problemes_ouverts'] > 0 ? '#F59E0B' : '#94A3B8' }};"><i class="fas fa-circle-exclamation"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value">{{ $kpi['problemes_ouverts'] }}</div>
                <div class="pmp-kpi-label">Problèmes ouverts</div>
            </div>
        </div>
        <div class="pmp-kpi-card" style="{{ ($phasesEnAttente + $jalonsEnAttente) > 0 ? 'border-left:3px solid #6366F1;' : '' }}">
            <div class="pmp-kpi-icon" style="background:{{ ($phasesEnAttente + $jalonsEnAttente) > 0 ? '#6366F1' : '#94A3B8' }};"><i class="fas fa-user-check"></i></div>
            <div class="pmp-kpi-body">
                <div class="pmp-kpi-value" style="{{ ($phasesEnAttente + $jalonsEnAttente) > 0 ? 'color:#6366F1;' : '' }}">{{ $phasesEnAttente + $jalonsEnAttente }}</div>
                <div class="pmp-kpi-label">Clôtures en attente</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- COLONNE GAUCHE --}}
        <div class="col-lg-8">

            {{-- Tableau de bord des validations --}}
            @if($projet->phases->filter(fn($p) => $p->statut_cloture !== 'ouvert' || $p->valideurs->count())->count() || $projet->jalons->filter(fn($j) => $j->statut_cloture !== 'ouvert' || $j->valideurs->count())->count())
            <div class="contact-detail-card mb-4" style="border-left:4px solid #6366F1;">
                <h4 class="contact-detail-card-title">
                    <i class="fas fa-user-check" style="color:#6366F1;"></i> Validation & Clôture
                </h4>

                {{-- Phases avec workflow --}}
                @php $phasesAvecWf = $projet->phases->filter(fn($p) => $p->valideurs->count() > 0); @endphp
                @if($phasesAvecWf->count())
                <h6 style="font-size:.72rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-top:.5rem;">Phases WBS</h6>
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($phasesAvecWf as $phase)
                    <div class="d-flex align-items-center gap-2 py-1 px-2" style="background:#F8FAFC;border-radius:8px;font-size:.78rem;">
                        <span class="badge" style="background:{{ $phase->couleur ?? '#0D9488' }};font-size:.6rem;">{{ $phase->code_wbs ?? 'WBS-'.$phase->ordre }}</span>
                        <a href="{{ route('projet.wbs.show', [$projet, $phase]) }}" style="flex:1;text-decoration:none;color:#0F172A;font-weight:700;">{{ $phase->nom }}</a>
                        <span class="badge" style="background:{{ $phase->cloture_couleur }};font-size:.6rem;">{{ $phase->cloture_libelle }}</span>

                        @if(in_array($phase->statut_cloture, ['ouvert', 'rejete', 'revisions']))
                        <button class="btn-action process btn-sm" title="Soumettre"
                                onclick="ouvrirModalCloture({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}', '{{ $phase->statut_cloture }}')">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                        @endif
                        @if($phase->statut_cloture === 'soumis' && $phase->estValideur())
                        <button class="btn-action success btn-sm" onclick="ouvrirModalDecision({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}', 'approuver')"><i class="fas fa-check"></i></button>
                        <button class="btn-action danger btn-sm" onclick="ouvrirModalDecision({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}', 'rejeter')"><i class="fas fa-times"></i></button>
                        @endif
                        <button class="ged-action-btn" onclick="voirHistorique({{ $phase->id }}, 'phase')" title="Historique"><i class="fas fa-clock-rotate-left" style="font-size:.65rem;"></i></button>
                    </div>
                    @if($phase->statut_cloture === 'rejete' && $phase->motif_rejet)
                    <div class="px-3" style="font-size:.7rem;color:#DC2626;margin-top:-6px;"><i class="fas fa-circle-xmark me-1"></i> {{ $phase->motif_rejet }}</div>
                    @elseif($phase->statut_cloture === 'revisions' && $phase->motif_rejet)
                    <div class="px-3" style="font-size:.7rem;color:#D97706;margin-top:-6px;"><i class="fas fa-arrows-rotate me-1"></i> {{ $phase->motif_rejet }}</div>
                    @endif
                    @endforeach
                </div>
                @endif

                {{-- Jalons avec workflow --}}
                @php $jalonsAvecWf = $projet->jalons->filter(fn($j) => $j->valideurs->count() > 0); @endphp
                @if($jalonsAvecWf->count())
                <h6 style="font-size:.72rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Jalons</h6>
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($jalonsAvecWf as $jalon)
                    <div class="d-flex align-items-center gap-2 py-1 px-2" style="background:#F8FAFC;border-radius:8px;font-size:.78rem;">
                        <i class="fas fa-flag" style="color:{{ match($jalon->statut) { 'atteint' => '#16A34A', 'manque' => '#DC2626', 'reporte' => '#F59E0B', default => '#0D9488' } }};font-size:.7rem;"></i>
                        <a href="{{ route('projet.jalons.show', [$projet, $jalon]) }}" style="flex:1;text-decoration:none;color:#0F172A;font-weight:700;">{{ $jalon->titre }}</a>
                        <span style="font-size:.68rem;color:#64748B;">{{ $jalon->date_prevue->format('d/m/Y') }}</span>
                        <span class="badge" style="background:{{ $jalon->cloture_couleur }};font-size:.6rem;">{{ $jalon->cloture_libelle }}</span>

                        @if(in_array($jalon->statut_cloture, ['ouvert', 'rejete', 'revisions']))
                        <button class="btn-action process btn-sm" onclick="ouvrirModalCloture({{ $jalon->id }}, 'jalon', '{{ addslashes($jalon->titre) }}', '{{ $jalon->statut_cloture }}')"><i class="fas fa-paper-plane"></i></button>
                        @endif
                        @if($jalon->statut_cloture === 'soumis' && $jalon->estValideur())
                        <button class="btn-action success btn-sm" onclick="ouvrirModalDecision({{ $jalon->id }}, 'jalon', '{{ addslashes($jalon->titre) }}', 'approuver')"><i class="fas fa-check"></i></button>
                        <button class="btn-action danger btn-sm" onclick="ouvrirModalDecision({{ $jalon->id }}, 'jalon', '{{ addslashes($jalon->titre) }}', 'rejeter')"><i class="fas fa-times"></i></button>
                        @endif
                        <button class="ged-action-btn" onclick="voirHistorique({{ $jalon->id }}, 'jalon')" title="Historique"><i class="fas fa-clock-rotate-left" style="font-size:.65rem;"></i></button>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Valideurs du projet --}}
                @if($projet->valideurs->count())
                <h6 style="font-size:.72rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Valideurs du projet</h6>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($projet->valideurs as $v)
                    <span class="opp-stage-badge" style="background:#6366F1;font-size:.65rem;">
                        <i class="fas {{ $v->valideur_type === 'user' ? 'fa-user' : 'fa-users' }} me-1"></i>{{ $v->nom_affichage }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            {{-- Phases WBS (inline) --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title">
                    <i class="fas fa-sitemap" style="color:#0D9488;"></i> Phases WBS ({{ $projet->phases->count() }})
                    <a href="{{ route('projet.wbs.index', $projet) }}" class="ms-auto" style="font-size:.72rem;color:#0D9488;text-decoration:none;font-weight:600;">Voir tout</a>
                </h4>
                @if($projet->phases->count())
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($projet->phases->sortBy('ordre') as $phase)
                    <div class="d-flex align-items-center gap-2 py-2 px-3" style="background:#F8FAFC;border-radius:8px;border-left:3px solid {{ $phase->couleur ?? '#0D9488' }};">
                        <a href="{{ route('projet.wbs.show', [$projet, $phase]) }}" style="flex:1;text-decoration:none;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge" style="background:{{ $phase->couleur ?? '#0D9488' }};font-size:.6rem;">{{ $phase->code_wbs ?? 'WBS-'.$phase->ordre }}</span>
                                <strong style="font-size:.82rem;color:#0F172A;">{{ $phase->nom }}</strong>
                                @if($phase->statut)<span class="opp-stage-badge" style="background:{{ $phase->statut_couleur }};font-size:.55rem;">{{ $phase->statut->libelle }}</span>@endif
                            </div>
                            <div class="d-flex align-items-center gap-3 mt-1" style="font-size:.7rem;color:#64748B;">
                                <span><i class="fas fa-list-check"></i> {{ $phase->taches->count() }} tâches</span>
                                @if($phase->ponderation)<span><i class="fas fa-weight-hanging"></i> {{ $phase->ponderation }}%</span>@endif
                                @if($phase->date_debut && $phase->date_fin)<span><i class="far fa-calendar"></i> {{ $phase->date_debut->format('d/m') }} → {{ $phase->date_fin->format('d/m') }}</span>@endif
                            </div>
                        </a>
                        <div class="tache-progress-bar" style="width:60px;">
                            <div class="tache-progress-fill" style="width:{{ $phase->avancement_real }}%;background:{{ $phase->couleur ?? '#0D9488' }};"></div>
                        </div>
                        <span style="font-size:.72rem;font-weight:700;min-width:30px;text-align:right;">{{ $phase->avancement_real }}%</span>
                        @if($phase->est_verrouille)<i class="fas fa-lock" style="color:#F59E0B;font-size:.6rem;" title="Verrouillé"></i>@endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted small mb-2">Aucune phase WBS définie.</p>
                @endif
                <a href="{{ route('projet.wbs.index', $projet) }}" class="btn btn-sm btn-light"><i class="fas fa-plus me-1"></i> Ajouter une phase</a>
            </div>

            {{-- Budget & Finances --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-coins"></i> Budget & Finances
                    <a href="{{ route('projet.couts.index', $projet) }}" class="ms-auto" style="font-size:.72rem;color:#0D9488;text-decoration:none;font-weight:600;">Voir détails</a>
                </h4>
                @if($kpi['budget_approuve'])
                <div class="row g-3 text-center mb-3">
                    <div class="col-md-4">
                        <div style="font-size:1.3rem;font-weight:800;color:#0F172A;">{{ number_format($kpi['budget_approuve'], 0, ',', ' ') }}</div>
                        <small class="text-muted">{{ $projet->devise }} approuvé</small>
                    </div>
                    <div class="col-md-4">
                        <div style="font-size:1.3rem;font-weight:800;color:#D97706;">{{ number_format($kpi['budget_consomme'], 0, ',', ' ') }}</div>
                        <small class="text-muted">consommé</small>
                    </div>
                    <div class="col-md-4">
                        <div style="font-size:1.3rem;font-weight:800;color:{{ $kpi['budget_restant'] >= 0 ? '#16A34A' : '#DC2626' }};">{{ number_format($kpi['budget_restant'], 0, ',', ' ') }}</div>
                        <small class="text-muted">restant</small>
                    </div>
                </div>
                @php $pctBudget = $kpi['budget_approuve'] > 0 ? min(100, round($kpi['budget_consomme'] / $kpi['budget_approuve'] * 100)) : 0; @endphp
                <div class="tache-detail-progress">
                    <div class="tache-detail-progress-bar">
                        <div class="tache-detail-progress-fill" style="width:{{ $pctBudget }}%;background:{{ $pctBudget > 90 ? '#DC2626' : ($pctBudget > 70 ? '#F59E0B' : '#16A34A') }};"></div>
                    </div>
                    <span class="tache-detail-progress-pct">{{ $pctBudget }}%</span>
                </div>
                @else
                <p class="text-muted small">Aucun budget défini. <a href="{{ route('intranet.projets.edit', $projet) }}">Définir le budget</a></p>
                @endif

                <div class="row g-3 mt-3 text-center" style="border-top:1px solid #F1F5F9;padding-top:.85rem;">
                    <div class="col-6">
                        <div style="font-size:1.1rem;font-weight:700;color:#0891B2;">{{ $kpi['heures_estimees'] }}h</div>
                        <small class="text-muted">Heures estimées</small>
                    </div>
                    <div class="col-6">
                        <div style="font-size:1.1rem;font-weight:700;color:{{ $kpi['heures_reelles'] > $kpi['heures_estimees'] && $kpi['heures_estimees'] > 0 ? '#DC2626' : '#0F172A' }};">{{ $kpi['heures_reelles'] }}h</div>
                        <small class="text-muted">Heures réelles</small>
                    </div>
                </div>
            </div>

            {{-- Risques & Problèmes --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title">
                    <i class="fas fa-shield-halved" style="color:#DC2626;"></i> Risques & Problèmes
                    <a href="{{ route('projet.risques.index', $projet) }}" class="ms-auto" style="font-size:.72rem;color:#0D9488;text-decoration:none;font-weight:600;">Registre</a>
                </h4>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="dash-mini-stat" style="border-left:3px solid #DC2626;">
                            <div class="dash-mini-value">{{ $kpi['risques_ouverts'] }}</div>
                            <div class="dash-mini-label">Risques ouverts</div>
                            <div class="dash-mini-sub">dont {{ $kpi['risques_critiques'] }} critiques</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dash-mini-stat" style="border-left:3px solid #F59E0B;">
                            <div class="dash-mini-value">{{ $kpi['problemes_ouverts'] }}</div>
                            <div class="dash-mini-label">Problèmes ouverts</div>
                        </div>
                    </div>
                </div>

                @php $topRisques = $projet->risques->whereIn('statut', ['identifie','analyse'])->sortByDesc(fn($r) => $r->probabilite * $r->impact)->take(5); @endphp
                @if($topRisques->count())
                <table class="opp-table" style="font-size:.78rem;">
                    <thead><tr><th>Risque</th><th>P</th><th>I</th><th>Score</th><th>Niveau</th><th>Stratégie</th></tr></thead>
                    <tbody>
                    @foreach($topRisques as $r)
                    @php $score = $r->probabilite * $r->impact; $niveau = $r->niveau; @endphp
                    <tr>
                        <td><strong>{{ Str::limit($r->titre, 35) }}</strong></td>
                        <td>{{ $r->probabilite }}</td>
                        <td>{{ $r->impact }}</td>
                        <td><strong>{{ $score }}</strong></td>
                        <td><span class="opp-stage-badge" style="background:{{ match($niveau) {'critique'=>'#DC2626','eleve'=>'#F59E0B','moyen'=>'#0891B2',default=>'#16A34A'} }};font-size:.6rem;">{{ ucfirst($niveau) }}</span></td>
                        <td>{{ $r->strategie ?? '—' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- Tâches récentes --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-list-check"></i> Tâches récentes</h4>
                @if($tachesRecentes->count())
                <div class="tache-list">
                    @foreach($tachesRecentes as $t)
                    <a href="{{ route('intranet.taches.show', $t) }}" class="tache-item {{ $t->estEnRetard() ? 'en-retard' : '' }}" style="text-decoration:none;">
                        <div class="tache-priority" style="background:{{ $t->priorite_couleur }};"></div>
                        <div class="tache-check {{ $t->est_terminee ? 'done' : '' }}">@if($t->est_terminee)<i class="fas fa-check"></i>@endif</div>
                        <div class="tache-body">
                            <div class="tache-title">{{ $t->titre }}</div>
                            <div class="tache-meta">@if($t->responsable)<span><i class="fas fa-user"></i> {{ $t->responsable->prenoms }}</span>@endif</div>
                        </div>
                        <div class="tache-progress-col">
                            <div class="tache-progress-bar"><div class="tache-progress-fill" style="width:{{ $t->avancement }}%;background:{{ $t->statut_couleur }};"></div></div>
                            <span class="tache-progress-pct">{{ $t->avancement }}%</span>
                        </div>
                        <div class="tache-status">@if($t->statut)<span class="opp-stage-badge" style="background:{{ $t->statut_couleur }};font-size:.6rem;">{{ $t->statut->libelle }}</span>@endif</div>
                        <div class="tache-deadline {{ $t->estEnRetard() ? 'text-danger fw-bold' : '' }}">@if($t->date_fin)<i class="far fa-calendar"></i> {{ $t->date_fin->format('d/m') }}@endif</div>
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-muted small">Aucune tâche.</p>
                @endif
            </div>
        </div>

        {{-- COLONNE DROITE --}}
        <div class="col-lg-4">

            {{-- Demandes de changement --}}
            @if($kpi['changements_en_attente'] > 0)
            <div class="contact-detail-card mb-4" style="border-left:4px solid #7C3AED;">
                <h4 class="contact-detail-card-title"><i class="fas fa-code-compare" style="color:#7C3AED;"></i> Changements en attente</h4>
                @foreach($projet->changements->where('statut', 'soumis')->take(5) as $ch)
                <div class="dash-risk-item">
                    <div class="dash-risk-score" style="background:#7C3AED;">
                        <i class="fas fa-code-compare" style="font-size:.7rem;"></i>
                    </div>
                    <div>
                        <div class="dash-risk-title">{{ Str::limit($ch->titre, 30) }}</div>
                        <div class="dash-risk-project">
                            @if($ch->impact_cout)<span style="color:#D97706;">{{ number_format($ch->impact_cout, 0, ',', ' ') }} {{ $projet->devise }}</span> · @endif
                            @if($ch->impact_delai_jours)<span style="color:#DC2626;">{{ $ch->impact_delai_jours }}j</span>@endif
                        </div>
                    </div>
                </div>
                @endforeach
                <a href="{{ route('projet.changements.index', $projet) }}" class="d-block mt-2" style="font-size:.75rem;color:#7C3AED;text-decoration:none;font-weight:600;">Voir toutes les demandes</a>
            </div>
            @endif

            {{-- Jalons prochains --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-flag-checkered"></i> Prochains jalons
                    <a href="{{ route('projet.jalons.index', $projet) }}" class="ms-auto" style="font-size:.72rem;color:#0D9488;text-decoration:none;font-weight:600;">Tous</a>
                </h4>
                @if($jalonsProchains->count())
                <div class="d-flex flex-column gap-2">
                    @foreach($jalonsProchains as $j)
                    <a href="{{ route('projet.jalons.show', [$projet, $j]) }}" class="pmp-jalon-mini {{ $j->estEnRetard() ? 'en-retard' : '' }}" style="text-decoration:none;color:inherit;">
                        <div class="pmp-jalon-date">
                            <div class="pmp-jalon-day">{{ $j->date_prevue->format('d') }}</div>
                            <div class="pmp-jalon-month">{{ $j->date_prevue->translatedFormat('M') }}</div>
                        </div>
                        <div style="flex:1;">
                            <div class="pmp-jalon-title">{{ $j->titre }}</div>
                            @if($j->phase)<div class="pmp-jalon-phase">{{ $j->phase->nom }}</div>@endif
                        </div>
                        @if($j->statut_cloture !== 'ouvert')
                        <span class="badge" style="background:{{ $j->cloture_couleur }};font-size:.55rem;">{{ $j->cloture_libelle }}</span>
                        @endif
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-muted small">Aucun jalon planifié.</p>
                @endif
            </div>

            {{-- Équipe --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-users"></i> Équipe ({{ $kpi['membres'] }})</h4>
                <ul class="contact-info-list">
                    @if($projet->chefProjet)<li><strong>Chef de projet :</strong> {{ $projet->chefProjet->prenoms }} {{ $projet->chefProjet->name }}</li>@endif
                    @if($projet->sponsor)<li><strong>Sponsor :</strong> {{ $projet->sponsor->prenoms }} {{ $projet->sponsor->name }}</li>@endif
                </ul>
                @if($projet->membres->count())
                <div class="d-flex flex-wrap gap-1 mt-2">
                    @foreach($projet->membres->take(10) as $m)
                    <div class="contact-mini-avatar contact-avatar-letters" style="background:{{ ['#7C3AED','#059669','#D97706','#0891B2','#0D9488','#4F46E5'][$loop->index % 6] }};width:30px;height:30px;font-size:.6rem;" title="{{ $m->prenoms }} {{ $m->name }}">
                        {{ strtoupper(substr($m->prenoms ?? $m->name, 0, 1)) }}{{ strtoupper(substr($m->name, 0, 1)) }}
                    </div>
                    @endforeach
                    @if($projet->membres->count() > 10)
                    <div class="contact-mini-avatar contact-avatar-letters" style="background:#94A3B8;width:30px;height:30px;font-size:.55rem;">+{{ $projet->membres->count() - 10 }}</div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Planning --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="far fa-calendar"></i> Planning</h4>
                <ul class="contact-info-list">
                    @if($projet->date_debut)<li><strong>Début :</strong> {{ $projet->date_debut->format('d/m/Y') }}</li>@endif
                    @if($projet->date_fin)<li class="{{ $projet->estEnRetard() ? 'text-danger fw-bold' : '' }}"><strong>Fin prévue :</strong> {{ $projet->date_fin->format('d/m/Y') }}</li>@endif
                    <li><strong>Phases WBS :</strong> {{ $kpi['phases'] }}</li>
                    <li><strong>Jalons en retard :</strong> <span style="color:{{ $kpi['jalons_en_retard'] > 0 ? '#DC2626' : '#16A34A' }};">{{ $kpi['jalons_en_retard'] }}</span></li>
                </ul>
            </div>

            {{-- Navigation rapide --}}
            <div class="contact-detail-card">
                <h4 class="contact-detail-card-title"><i class="fas fa-compass"></i> Accès rapide</h4>
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('projet.wbs.index', $projet) }}" class="dash-quick-link"><i class="fas fa-sitemap"></i> WBS & Phases</a>
                    <a href="{{ route('projet.couts.index', $projet) }}" class="dash-quick-link"><i class="fas fa-coins"></i> Coûts & Budget</a>
                    <a href="{{ route('projet.evm.index', $projet) }}" class="dash-quick-link"><i class="fas fa-chart-line"></i> Valeur acquise (EVM)</a>
                    <a href="{{ route('projet.risques.index', $projet) }}" class="dash-quick-link"><i class="fas fa-triangle-exclamation"></i> Registre des risques</a>
                    <a href="{{ route('projet.changements.index', $projet) }}" class="dash-quick-link"><i class="fas fa-code-compare"></i> Demandes de changement</a>
                    <a href="{{ route('projet.parties-prenantes.index', $projet) }}" class="dash-quick-link"><i class="fas fa-users-between-lines"></i> Parties prenantes</a>
                    <a href="{{ route('projet.feuilles-temps.index', $projet) }}" class="dash-quick-link"><i class="fas fa-clock-rotate-left"></i> Feuilles de temps</a>
                    <a href="{{ route('projet.lecons.index', $projet) }}" class="dash-quick-link"><i class="fas fa-lightbulb"></i> Leçons apprises</a>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Modale valideurs projet --}}
<div class="modal fade" id="modalValideursProjet" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content" action="{{ route('projet.cloture.soumettre', ['type' => 'projet', 'id' => $projet->id]) }}" id="formValideursProjet">
            @csrf
            <div class="modal-header" style="background:linear-gradient(135deg,#6366F1,#4F46E5);">
                <h5 class="modal-title text-white"><i class="fas fa-user-check me-2"></i> Valideurs du projet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.82rem;color:#475569;">Définissez qui peut approuver la clôture de ce projet. Modifiez le projet pour gérer les valideurs.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Utilisateurs</label>
                        <select name="valideurs_users[]" class="form-select" multiple size="4">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $projet->validateursUsers->pluck('valideur_id')->contains($u->id) ? 'selected' : '' }}>{{ $u->prenoms }} {{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Groupes</label>
                        <select name="valideurs_groupes[]" class="form-select" multiple size="4">
                            @foreach($groupes as $g)
                                <option value="{{ $g->id }}" {{ $projet->validateursGroupes->pluck('valideur_id')->contains($g->id) ? 'selected' : '' }}>{{ $g->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <small class="form-hint mt-2" style="color:#6366F1;"><i class="fas fa-info-circle"></i> Ctrl+clic pour sélection multiple</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn text-white" style="background:#6366F1;" onclick="sauverValideursProjet()"><i class="fas fa-save me-2"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- Modale soumission clôture --}}
<div class="modal fade" id="modalCloture" tabindex="-1">
    <div class="modal-dialog">
        <form id="clotureForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header" style="background:linear-gradient(135deg,#6366F1,#4F46E5);">
                <h5 class="modal-title text-white"><i class="fas fa-paper-plane me-2"></i> <span id="clotureTitle">Soumettre pour clôture</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.82rem;color:#475569;">Soumission de : <strong id="clotureNom"></strong></p>
                <div id="clotureRejetMsg" style="display:none;" class="alert alert-warning py-2 mb-3"></div>
                <div class="mb-3">
                    <label class="form-label">Justification de clôture <span class="text-danger">*</span></label>
                    <textarea name="justification" id="clotureJustification" rows="4" class="form-control" required
                              placeholder="Décrivez pourquoi cette entité peut être clôturée : livrables réalisés, critères remplis..."></textarea>
                    <small class="form-hint">Minimum 10 caractères</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn text-white" style="background:#6366F1;"><i class="fas fa-paper-plane me-2"></i> Soumettre</button>
            </div>
        </form>
    </div>
</div>

{{-- Modale décision valideur --}}
<div class="modal fade" id="modalDecision" tabindex="-1">
    <div class="modal-dialog">
        <form id="decisionForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header" id="decisionHeader">
                <h5 class="modal-title text-white" id="decisionTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.82rem;color:#475569;" id="decisionDesc"></p>
                <div class="mb-3">
                    <label class="form-label" id="decisionLabel">Commentaire</label>
                    <textarea name="commentaire" id="decisionCommentaire" rows="3" class="form-control"></textarea>
                </div>
                <input type="hidden" name="motif" id="decisionMotif">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn text-white" id="decisionBtn"></button>
            </div>
        </form>
    </div>
</div>

{{-- Modale historique --}}
<div class="modal fade" id="modalHistorique" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-clock-rotate-left me-2"></i> Historique de validation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="historiqueBody" style="max-height:400px;overflow-y:auto;"></div>
        </div>
    </div>
</div>

@include('projet._partials.modales-protection')

@push('scripts')
<script>
function ouvrirModalCloture(entityId, type, nom, statutCloture) {
    document.getElementById('clotureNom').textContent = nom;
    document.getElementById('clotureJustification').value = '';
    const rejetMsg = document.getElementById('clotureRejetMsg');
    if (statutCloture === 'rejete' || statutCloture === 'revisions') {
        document.getElementById('clotureTitle').textContent = 'Resoumettre pour clôture';
        rejetMsg.style.display = 'block';
        rejetMsg.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Soumission précédente ' + (statutCloture === 'rejete' ? 'rejetée' : 'renvoyée pour révisions') + '.';
    } else {
        document.getElementById('clotureTitle').textContent = 'Soumettre pour clôture';
        rejetMsg.style.display = 'none';
    }
    document.getElementById('clotureForm').action = '/projet/cloture/' + type + '/' + entityId + '/soumettre';
    new bootstrap.Modal(document.getElementById('modalCloture')).show();
}

function ouvrirModalDecision(entityId, type, nom, action) {
    const isApprouver = action === 'approuver';
    document.getElementById('decisionHeader').style.background = isApprouver ? 'linear-gradient(135deg,#16A34A,#15803D)' : 'linear-gradient(135deg,#DC2626,#B91C1C)';
    document.getElementById('decisionTitle').textContent = isApprouver ? 'Approuver la clôture' : 'Rejeter la clôture';
    document.getElementById('decisionDesc').textContent = (isApprouver ? 'Approuver : ' : 'Rejeter : ') + nom;
    document.getElementById('decisionLabel').textContent = isApprouver ? 'Commentaire (optionnel)' : 'Motif de rejet *';
    document.getElementById('decisionCommentaire').required = !isApprouver;
    document.getElementById('decisionCommentaire').value = '';
    document.getElementById('decisionBtn').innerHTML = isApprouver ? '<i class="fas fa-check me-1"></i> Approuver' : '<i class="fas fa-times me-1"></i> Rejeter';
    document.getElementById('decisionBtn').style.background = isApprouver ? '#16A34A' : '#DC2626';
    document.getElementById('decisionForm').action = '/projet/cloture/' + type + '/' + entityId + '/' + action;
    document.getElementById('decisionForm').onsubmit = function () {
        if (!isApprouver) {
            document.getElementById('decisionMotif').value = document.getElementById('decisionCommentaire').value;
            document.getElementById('decisionCommentaire').name = '';
            document.getElementById('decisionMotif').name = 'motif';
        } else {
            document.getElementById('decisionCommentaire').name = 'commentaire';
            document.getElementById('decisionMotif').name = '';
        }
    };
    new bootstrap.Modal(document.getElementById('modalDecision')).show();
}

function voirHistorique(entityId, type) {
    const body = document.getElementById('historiqueBody');
    body.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';
    new bootstrap.Modal(document.getElementById('modalHistorique')).show();
    fetch('/projet/cloture/' + type + '/' + entityId + '/historique')
        .then(r => r.json())
        .then(data => {
            if (!data.length) { body.innerHTML = '<p class="text-center text-muted py-3">Aucun historique.</p>'; return; }
            let html = '';
            data.forEach(h => {
                html += '<div class="d-flex gap-2 mb-3 align-items-start">';
                html += '<div style="width:28px;height:28px;border-radius:50%;background:' + h.couleur + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">';
                html += '<i class="fas ' + h.icone + ' text-white" style="font-size:.65rem;"></i></div>';
                html += '<div><div style="font-size:.78rem;font-weight:600;">' + h.libelle + '</div>';
                html += '<div style="font-size:.7rem;color:#64748B;">' + (h.user || '') + ' · ' + h.date + '</div>';
                if (h.justification) html += '<div style="font-size:.72rem;color:#475569;margin-top:2px;"><i class="fas fa-quote-left me-1" style="font-size:.55rem;"></i>' + h.justification + '</div>';
                if (h.commentaire) html += '<div style="font-size:.72rem;color:#475569;margin-top:2px;"><i class="fas fa-comment me-1" style="font-size:.55rem;"></i>' + h.commentaire + '</div>';
                html += '</div></div>';
            });
            body.innerHTML = html;
        })
        .catch(() => { body.innerHTML = '<p class="text-center text-danger py-3">Erreur.</p>'; });
}

function sauverValideursProjet() {
    const form = document.getElementById('formValideursProjet');
    const formData = new FormData(form);
    fetch('/projet/cloture/projet/{{ $projet->id }}/valideurs', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData,
    }).then(r => {
        if (r.ok) location.reload();
    });
}
</script>
@endpush
@endsection
