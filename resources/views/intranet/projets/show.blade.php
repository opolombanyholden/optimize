@extends('layouts.app')
@section('title', $projet->nom)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item active">{{ $projet->nom }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: {{ $projet->statut_couleur }};">

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
                @if($projet->categorie)<span class="badge-soft">{{ $projet->categorie }}</span>@endif
                @if($projet->estEnRetard())<span class="contact-tag-lg hot">⚠️ En retard</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $projet->nom }}</h1>
        </div>
        <div class="contact-detail-actions">
            {{-- Bouton création rapide --}}
            <div class="dropdown">
                <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="dropdown">
                    <i class="fas fa-plus me-2"></i> Créer <i class="fas fa-chevron-down ms-1" style="font-size:.6rem;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end ged-dropdown" style="width:260px;">
                    <li class="px-3 py-1"><small class="text-muted fw-bold text-uppercase" style="font-size:.62rem;letter-spacing:.05em;">Planification</small></li>
                    <li><a class="dropdown-item" href="{{ route('projet.wbs.index', $projet) }}"><i class="fas fa-sitemap"></i> Nouvelle phase WBS</a></li>
                    <li><a class="dropdown-item" href="{{ route('intranet.taches.create') }}?projet_id={{ $projet->id }}"><i class="fas fa-list-check"></i> Nouvelle tâche</a></li>
                    <li><a class="dropdown-item" href="{{ route('projet.jalons.index', $projet) }}"><i class="fas fa-flag-checkered"></i> Nouveau jalon</a></li>
                    <li><a class="dropdown-item" href="{{ route('projet.ressources.index', $projet) }}"><i class="fas fa-people-carry-box"></i> Ajouter une ressource</a></li>

                    <li><hr class="dropdown-divider"></li>
                    <li class="px-3 py-1"><small class="text-muted fw-bold text-uppercase" style="font-size:.62rem;letter-spacing:.05em;">Exécution</small></li>
                    <li><a class="dropdown-item" href="{{ route('projet.feuilles-temps.index', $projet) }}"><i class="fas fa-clock-rotate-left"></i> Saisir du temps</a></li>
                    <li><a class="dropdown-item" href="{{ route('projet.livrables.index', $projet) }}"><i class="fas fa-box-open"></i> Nouveau livrable</a></li>
                    <li><a class="dropdown-item" href="{{ route('projet.couts.index', $projet) }}"><i class="fas fa-coins"></i> Enregistrer un coût</a></li>

                    <li><hr class="dropdown-divider"></li>
                    <li class="px-3 py-1"><small class="text-muted fw-bold text-uppercase" style="font-size:.62rem;letter-spacing:.05em;">Gouvernance</small></li>
                    <li><a class="dropdown-item" href="{{ route('projet.risques.index', $projet) }}"><i class="fas fa-triangle-exclamation"></i> Signaler un risque</a></li>
                    <li><a class="dropdown-item" href="{{ route('projet.problemes.index', $projet) }}"><i class="fas fa-circle-exclamation"></i> Déclarer un problème</a></li>
                    <li><a class="dropdown-item" href="{{ route('projet.changements.index', $projet) }}"><i class="fas fa-code-compare"></i> Demande de changement</a></li>
                    <li><a class="dropdown-item" href="{{ route('intranet.rapports.create') }}?projet_id={{ $projet->id }}"><i class="fas fa-file-lines"></i> Rédiger un rapport</a></li>
                </ul>
            </div>

            @can('update:projet_intranet')
            <a href="{{ route('intranet.projets.edit', $projet) }}" class="btn btn-light"><i class="fas fa-pen-to-square me-2"></i> Modifier</a>
            @endcan
        </div>
    </div>

    {{-- Avancement --}}
    <div class="contact-detail-card mb-4">
        <h4 class="contact-detail-card-title"><i class="fas fa-chart-line"></i> Avancement global</h4>
        <div class="tache-detail-progress">
            <div class="tache-detail-progress-bar">
                <div class="tache-detail-progress-fill" style="width:{{ $projet->avancement }}%;background:{{ $projet->statut_couleur }};"></div>
            </div>
            <span class="tache-detail-progress-pct">{{ $projet->avancement }}%</span>
        </div>
    </div>

    {{-- Navigation PMP rapide --}}
    <div class="pmp-nav-grid mb-4">
        <a href="{{ route('projet.overview', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#0D9488;"><i class="fas fa-gauge-high"></i></div>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('projet.wbs.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#4F46E5;"><i class="fas fa-sitemap"></i></div>
            <span>WBS</span>
            <small>{{ $projet->phases->count() }} phases</small>
        </a>
        <a href="{{ route('projet.jalons.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#F59E0B;"><i class="fas fa-flag-checkered"></i></div>
            <span>Jalons</span>
            <small>{{ $projet->jalons->count() }}</small>
        </a>
        <a href="{{ route('projet.ressources.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#0891B2;"><i class="fas fa-people-carry-box"></i></div>
            <span>Ressources</span>
            <small>{{ $projet->membres->count() }}</small>
        </a>
        <a href="{{ route('projet.feuilles-temps.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#7C3AED;"><i class="fas fa-clock-rotate-left"></i></div>
            <span>Temps</span>
        </a>
        <a href="{{ route('projet.livrables.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#059669;"><i class="fas fa-box-open"></i></div>
            <span>Livrables</span>
        </a>
        <a href="{{ route('projet.couts.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#D97706;"><i class="fas fa-coins"></i></div>
            <span>Coûts</span>
        </a>
        <a href="{{ route('projet.evm.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#DC2626;"><i class="fas fa-chart-line"></i></div>
            <span>EVM</span>
        </a>
        <a href="{{ route('projet.risques.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#991B1B;"><i class="fas fa-triangle-exclamation"></i></div>
            <span>Risques</span>
            <small>{{ $projet->risques->count() }}</small>
        </a>
        <a href="{{ route('projet.problemes.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#EA580C;"><i class="fas fa-circle-exclamation"></i></div>
            <span>Problèmes</span>
        </a>
        <a href="{{ route('projet.changements.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#7C3AED;"><i class="fas fa-code-compare"></i></div>
            <span>Changements</span>
        </a>
        <a href="{{ route('projet.parties-prenantes.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#BE185D;"><i class="fas fa-users-between-lines"></i></div>
            <span>Parties prenantes</span>
        </a>
        <a href="{{ route('projet.lecons.index', $projet) }}" class="pmp-nav-card">
            <div class="pmp-nav-icon" style="background:#16A34A;"><i class="fas fa-lightbulb"></i></div>
            <span>Leçons</span>
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Infos clés --}}
            <div class="contact-detail-grid mb-4">
                <div class="contact-detail-card">
                    <h4 class="contact-detail-card-title"><i class="far fa-calendar"></i> Planification</h4>
                    <ul class="contact-info-list">
                        @if($projet->date_debut)<li><strong>Début :</strong> {{ $projet->date_debut->format('d/m/Y') }}</li>@endif
                        @if($projet->date_fin)<li class="{{ $projet->estEnRetard() ? 'text-danger' : '' }}"><strong>Fin :</strong> {{ $projet->date_fin->format('d/m/Y') }}</li>@endif
                        @if($projet->budget_approuve)<li><strong>Budget :</strong> {{ number_format($projet->budget_approuve, 0, ',', ' ') }} {{ $projet->devise }}</li>@endif
                    </ul>
                </div>
                <div class="contact-detail-card">
                    <h4 class="contact-detail-card-title"><i class="fas fa-user-tie"></i> Direction</h4>
                    <ul class="contact-info-list">
                        @if($projet->chefProjet)<li><strong>Chef :</strong> {{ $projet->chefProjet->prenoms }} {{ $projet->chefProjet->name }}</li>@endif
                        @if($projet->sponsor)<li><strong>Sponsor :</strong> {{ $projet->sponsor->prenoms }} {{ $projet->sponsor->name }}</li>@endif
                    </ul>
                </div>
                <div class="contact-detail-card">
                    <h4 class="contact-detail-card-title"><i class="fas fa-chart-pie"></i> Indicateurs</h4>
                    <ul class="contact-info-list">
                        <li><strong>Tâches :</strong> {{ $projet->taches->count() }}</li>
                        <li><strong>Phases :</strong> {{ $projet->phases->count() }}</li>
                        <li><strong>Membres :</strong> {{ $projet->membres->count() }}</li>
                    </ul>
                </div>
            </div>

            {{-- Description --}}
            @if($projet->description)
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
                <div class="annonce-content p-0 border-0">{!! $projet->description !!}</div>
            </div>
            @endif

            {{-- Tâches --}}
            @if($projet->taches->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title">
                    <i class="fas fa-list-check"></i> Tâches ({{ $projet->taches->count() }})
                    <a href="{{ route('intranet.taches.index', ['projet' => $projet->id]) }}" class="ms-auto" style="font-size:.72rem;color:#0D9488;text-decoration:none;font-weight:600;">Voir toutes →</a>
                </h4>
                <div class="tache-list">
                    @foreach($projet->taches->sortByDesc('priorite_id')->take(8) as $t)
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
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Membres --}}
            @if($projet->membres->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-users"></i> Équipe ({{ $projet->membres->count() }})</h4>
                <div class="contacts-mini-grid">
                    @foreach($projet->membres as $m)
                    <div class="contact-mini">
                        <div class="contact-mini-avatar contact-avatar-letters" style="background:{{ ['#7C3AED','#059669','#D97706','#0891B2','#0D9488','#4F46E5'][$loop->index % 6] }};">
                            {{ strtoupper(substr($m->prenoms ?? $m->name, 0, 1)) }}{{ strtoupper(substr($m->name, 0, 1)) }}
                        </div>
                        <div><div class="contact-mini-name">{{ $m->prenoms }} {{ $m->name }}</div></div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Objectifs --}}
            @if($projet->objectifs)
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-bullseye"></i> Objectifs</h4>
                <div class="annonce-content p-0 border-0">{!! $projet->objectifs !!}</div>
            </div>
            @endif

            {{-- Phases WBS --}}
            @if($projet->phases->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title">
                    <i class="fas fa-sitemap"></i> Phases WBS
                    <a href="{{ route('projet.wbs.index', $projet) }}" class="ms-auto" style="font-size:.72rem;color:#0D9488;text-decoration:none;font-weight:600;">Gérer →</a>
                </h4>
                @foreach($projet->phases as $ph)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="wbs-phase-code" style="--phase-color:{{ $ph->couleur ?? '#0D9488' }};font-size:.6rem;">{{ $ph->code_wbs }}</span>
                    <span style="font-size:.82rem;font-weight:600;flex:1;">{{ $ph->nom }}</span>
                    <div class="tache-progress-bar" style="width:60px;"><div class="tache-progress-fill" style="width:{{ $ph->avancement_real }}%;background:{{ $ph->couleur ?? '#0D9488' }};"></div></div>
                    <span style="font-size:.68rem;font-weight:700;">{{ $ph->avancement_real }}%</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <footer class="annonce-footer">
        <div class="annonce-footer-actions">
            <a href="{{ route('intranet.projets.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i> Retour</a>
            <a href="{{ route('projet.overview', $projet) }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);">
                <i class="fas fa-gauge-high me-2"></i> Dashboard PMP
            </a>
        </div>
    </footer>

</div>
@endsection
