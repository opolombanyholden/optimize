@extends('layouts.app')
@section('title', $phase->nom . ' — Phase WBS')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.wbs.index', $projet) }}">WBS</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($phase->nom, 30) }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: {{ $phase->couleur ?? '#0D9488' }};">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'wbs'])

    {{-- En-tête phase --}}
    <div class="contact-detail-header mb-4" style="border-top-color: {{ $phase->couleur ?? '#0D9488' }};">
        <div class="contact-detail-photo contact-avatar-letters" style="background: {{ $phase->couleur ?? '#0D9488' }}; font-size: 1.3rem;">
            <i class="fas fa-sitemap"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                @if($phase->code_wbs)<code class="opp-ref">{{ $phase->code_wbs }}</code>@endif
                @if($phase->statut)<span class="opp-stage-badge" style="background:{{ $phase->statut_couleur }};">{{ $phase->statut->libelle }}</span>@endif
                @if($phase->statut_cloture !== 'ouvert')
                <span class="opp-stage-badge" style="background:{{ $phase->cloture_couleur }};">
                    <i class="fas {{ $phase->statut_cloture === 'approuve' ? 'fa-check-circle' : ($phase->statut_cloture === 'soumis' ? 'fa-clock' : 'fa-circle-xmark') }} me-1"></i>{{ $phase->cloture_libelle }}
                </span>
                @endif
                @if($phase->estEnRetard())<span class="contact-tag-lg hot">En retard</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $phase->nom }}</h1>
            <a href="{{ route('projet.overview', $projet) }}" class="contact-detail-orga" style="text-decoration:none;">
                <i class="fas fa-diagram-project"></i> {{ $projet->nom }}
                @if($phase->ponderation) · {{ $phase->ponderation }}% du projet @endif
            </a>
        </div>
        <div class="contact-detail-actions">
            @if($phase->aDesValideurs())
                @if(in_array($phase->statut_cloture, ['ouvert', 'rejete', 'revisions']))
                <button class="btn btn-light" onclick="ouvrirModalCloture({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}', '{{ $phase->statut_cloture }}')">
                    <i class="fas fa-paper-plane me-2"></i> Soumettre clôture
                </button>
                @endif
                @if($phase->statut_cloture === 'soumis' && $phase->estValideur())
                <button class="btn text-white" style="background:#16A34A;" onclick="ouvrirModalDecision({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}', 'approuver')"><i class="fas fa-check me-2"></i> Approuver</button>
                <button class="btn text-white" style="background:#DC2626;" onclick="ouvrirModalDecision({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}', 'rejeter')"><i class="fas fa-times me-2"></i> Rejeter</button>
                @endif
            @endif
            {{-- Modifier / Demander modification --}}
            @if($phase->peutModifier())
            <a href="{{ route('projet.wbs.index', $projet) }}" class="btn btn-light"><i class="fas fa-pen-to-square me-2"></i> Modifier</a>
            @elseif($phase->est_verrouille)
            <button class="btn btn-warning btn-sm" onclick="demanderModification({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}')">
                <i class="fas fa-lock me-2"></i> Demander modification
            </button>
            @if(!$phase->peutSupprimer())
            <button class="btn btn-outline-danger btn-sm" onclick="demanderSuppression({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}')">
                <i class="fas fa-trash me-1"></i> Demander suppression
            </button>
            @endif
            @endif
            <a href="{{ route('projet.wbs.index', $projet) }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-arrow-left me-2"></i> Retour WBS</a>
        </div>
    </div>

    {{-- Demandes en attente --}}
    @if($phase->aDemandeEnAttente())
    <div class="alert alert-warning mb-4" style="font-size:.82rem;border-radius:9px;">
        <i class="fas fa-clock me-2"></i> <strong>Demande en attente :</strong> Une demande de modification/suppression est en cours d'examen par un administrateur.
    </div>
    @endif

    {{-- Message rejet --}}
    @if($phase->statut_cloture === 'rejete' && $phase->motif_rejet)
    <div class="alert alert-danger mb-4" style="font-size:.82rem;border-radius:9px;">
        <i class="fas fa-circle-xmark me-2"></i> <strong>Clôture rejetée :</strong> {{ $phase->motif_rejet }}
    </div>
    @elseif($phase->statut_cloture === 'revisions' && $phase->motif_rejet)
    <div class="alert alert-warning mb-4" style="font-size:.82rem;border-radius:9px;">
        <i class="fas fa-arrows-rotate me-2"></i> <strong>Révisions demandées :</strong> {{ $phase->motif_rejet }}
    </div>
    @endif

    {{-- Avancement --}}
    <div class="contact-detail-card mb-4">
        <h4 class="contact-detail-card-title"><i class="fas fa-chart-line"></i> Avancement</h4>
        <div class="tache-detail-progress">
            <div class="tache-detail-progress-bar">
                <div class="tache-detail-progress-fill" style="width:{{ $phase->avancement_real }}%;background:{{ $phase->couleur ?? '#0D9488' }};"></div>
            </div>
            <span class="tache-detail-progress-pct">{{ $phase->avancement_real }}%</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">

            {{-- Description --}}
            @if($phase->description)
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
                <div class="content-body" style="font-size:.85rem;">{!! $phase->description !!}</div>
            </div>
            @endif

            {{-- Médias & Pièces jointes --}}
            @include('intranet._partials.media-display', ['entity' => $phase])

            {{-- Tâches --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-list-check"></i> Tâches ({{ $phase->taches->count() }})</h4>
                @if($phase->taches->count())
                <div class="tache-list">
                    @foreach($phase->taches->sortBy('ordre') as $t)
                    <a href="{{ route('intranet.taches.show', $t) }}" class="tache-item {{ $t->estEnRetard() ? 'en-retard' : '' }}" style="text-decoration:none;">
                        <div class="tache-priority" style="background:{{ $t->priorite_couleur }};"></div>
                        <div class="tache-check {{ $t->est_terminee ? 'done' : '' }}">@if($t->est_terminee)<i class="fas fa-check"></i>@endif</div>
                        <div class="tache-body">
                            <div class="tache-title">{{ $t->titre }}</div>
                            <div class="tache-meta">
                                @if($t->responsable)<span><i class="fas fa-user"></i> {{ $t->responsable->prenoms }}</span>@endif
                                @if($t->date_fin)<span class="{{ $t->estEnRetard() ? 'text-danger fw-bold' : '' }}"><i class="far fa-calendar"></i> {{ $t->date_fin->format('d/m/Y') }}</span>@endif
                            </div>
                        </div>
                        <div class="tache-progress-col">
                            <div class="tache-progress-bar"><div class="tache-progress-fill" style="width:{{ $t->avancement }}%;background:{{ $t->statut_couleur }};"></div></div>
                            <span class="tache-progress-pct">{{ $t->avancement }}%</span>
                        </div>
                        <div class="tache-status">@if($t->statut)<span class="opp-stage-badge" style="background:{{ $t->statut_couleur }};font-size:.6rem;">{{ $t->statut->libelle }}</span>@endif</div>
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-muted small">Aucune tâche dans cette phase.</p>
                @endif
                <a href="{{ route('projet.taches.index', $projet) }}?phase={{ $phase->id }}" class="btn btn-sm btn-light mt-2"><i class="fas fa-plus me-1"></i> Ajouter une tâche</a>
            </div>

            {{-- Coûts --}}
            @if($phase->couts->count() || $phase->taches->sum(fn($t) => $t->cout_total_estime) > 0)
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-coins"></i> Coûts</h4>
                <div class="row g-3 text-center mb-3">
                    <div class="col-md-4">
                        <div style="font-size:1.2rem;font-weight:800;color:#0D9488;">{{ number_format($phase->cout_estime, 0, ',', ' ') }}</div>
                        <small class="text-muted">{{ $projet->devise }} estimé</small>
                    </div>
                    <div class="col-md-4">
                        <div style="font-size:1.2rem;font-weight:800;color:#D97706;">{{ number_format($phase->cout_reel, 0, ',', ' ') }}</div>
                        <small class="text-muted">réel</small>
                    </div>
                    <div class="col-md-4">
                        @php $ecart = $phase->ecart_cout; @endphp
                        <div style="font-size:1.2rem;font-weight:800;color:{{ $ecart > 0 ? '#DC2626' : '#16A34A' }};">{{ $ecart > 0 ? '+' : '' }}{{ number_format($ecart, 0, ',', ' ') }}</div>
                        <small class="text-muted">écart</small>
                    </div>
                </div>
            </div>
            @endif

            {{-- Historique de validation --}}
            @if($phase->historiqueValidation->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-clock-rotate-left"></i> Historique de validation</h4>
                <div class="d-flex flex-column gap-3">
                    @foreach($phase->historiqueValidation as $h)
                    <div class="d-flex gap-2 align-items-start">
                        <div style="width:30px;height:30px;border-radius:50%;background:{{ $h->couleur }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas {{ $h->icone }} text-white" style="font-size:.65rem;"></i>
                        </div>
                        <div>
                            <div style="font-size:.8rem;font-weight:600;">{{ $h->libelle }}</div>
                            <div style="font-size:.7rem;color:#64748B;">
                                {{ $h->user ? trim(($h->user->prenoms ?? '') . ' ' . $h->user->name) : '' }} · {{ $h->created_at->format('d/m/Y H:i') }}
                            </div>
                            @if($h->justification)
                            <div style="font-size:.75rem;color:#475569;margin-top:2px;"><i class="fas fa-quote-left me-1" style="font-size:.55rem;"></i> {{ $h->justification }}</div>
                            @endif
                            @if($h->commentaire)
                            <div style="font-size:.75rem;color:#475569;margin-top:2px;"><i class="fas fa-comment me-1" style="font-size:.55rem;"></i> {{ $h->commentaire }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Planification --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="far fa-calendar"></i> Planification</h4>
                <ul class="contact-info-list">
                    @if($phase->date_debut)<li><strong>Début planifié :</strong> {{ $phase->date_debut->format('d/m/Y') }}</li>@endif
                    @if($phase->date_fin)<li class="{{ $phase->estEnRetard() ? 'text-danger fw-bold' : '' }}"><strong>Fin planifiée :</strong> {{ $phase->date_fin->format('d/m/Y') }}</li>@endif
                    @if($phase->date_debut_reelle)<li style="color:#16A34A;"><strong>Début effectif :</strong> {{ $phase->date_debut_reelle->format('d/m/Y') }}</li>@endif
                    @if($phase->date_fin_reelle)<li style="color:#16A34A;"><strong>Fin effective :</strong> {{ $phase->date_fin_reelle->format('d/m/Y') }}</li>@endif
                    @if($phase->ponderation)<li><strong>Pondération :</strong> {{ $phase->ponderation }}%</li>@endif
                    <li><strong>Ordre :</strong> {{ $phase->ordre }}</li>
                </ul>
            </div>

            {{-- Responsable --}}
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-user"></i> Responsable</h4>
                @if($phase->responsable)
                <div class="contact-mini">
                    <div class="contact-mini-avatar contact-avatar-letters" style="background:#7C3AED;">
                        {{ strtoupper(substr($phase->responsable->prenoms ?? $phase->responsable->name, 0, 1)) }}{{ strtoupper(substr($phase->responsable->name, 0, 1)) }}
                    </div>
                    <div><div class="contact-mini-name">{{ $phase->responsable->prenoms }} {{ $phase->responsable->name }}</div><div class="contact-mini-poste">Responsable de phase</div></div>
                </div>
                @else
                <p class="text-muted small">Non assigné</p>
                @endif
                @if($phase->auteur)
                <div class="contact-mini mt-2">
                    <div class="contact-mini-avatar contact-avatar-letters" style="background:#64748B;width:26px;height:26px;font-size:.55rem;">
                        {{ strtoupper(substr($phase->auteur->prenoms ?? $phase->auteur->name, 0, 1)) }}{{ strtoupper(substr($phase->auteur->name, 0, 1)) }}
                    </div>
                    <div><div class="contact-mini-name" style="font-size:.75rem;">{{ $phase->auteur->prenoms }} {{ $phase->auteur->name }}</div><div class="contact-mini-poste">Créé le {{ $phase->created_at->format('d/m/Y') }}</div></div>
                </div>
                @endif
            </div>

            {{-- Valideurs --}}
            @if($phase->valideurs->count())
            <div class="contact-detail-card mb-4" style="border-left:4px solid #6366F1;">
                <h4 class="contact-detail-card-title"><i class="fas fa-user-check" style="color:#6366F1;"></i> Valideurs de clôture</h4>
                <div class="d-flex flex-column gap-1">
                    @foreach($phase->valideurs as $v)
                    <span class="opp-stage-badge" style="background:#6366F1;font-size:.7rem;">
                        <i class="fas {{ $v->valideur_type === 'user' ? 'fa-user' : 'fa-users' }} me-1"></i>{{ $v->nom_affichage }}
                    </span>
                    @endforeach
                </div>
                @if($phase->justification_cloture)
                <div class="mt-2 p-2" style="background:#F8FAFC;border-radius:6px;font-size:.75rem;">
                    <strong>Justification :</strong><br>{{ $phase->justification_cloture }}
                </div>
                @endif
            </div>
            @endif

            {{-- Jalons de la phase --}}
            @if($phase->jalons->count())
            <div class="contact-detail-card mb-4">
                <h4 class="contact-detail-card-title"><i class="fas fa-flag-checkered"></i> Jalons ({{ $phase->jalons->count() }})</h4>
                <div class="d-flex flex-column gap-2">
                    @foreach($phase->jalons as $j)
                    <a href="{{ route('projet.jalons.show', [$projet, $j]) }}" class="d-flex align-items-center gap-2 py-1" style="text-decoration:none;font-size:.78rem;">
                        <i class="fas fa-flag" style="color:{{ match($j->statut) { 'atteint' => '#16A34A', 'manque' => '#DC2626', 'reporte' => '#F59E0B', default => '#0D9488' } }};"></i>
                        <span style="flex:1;color:#0F172A;">{{ $j->titre }}</span>
                        <span class="opp-stage-badge" style="background:{{ match($j->statut) { 'atteint' => '#16A34A', 'manque' => '#DC2626', 'reporte' => '#F59E0B', default => '#0D9488' } }};font-size:.55rem;">{{ ucfirst($j->statut) }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Statistiques --}}
            <div class="contact-detail-card">
                <h4 class="contact-detail-card-title"><i class="fas fa-chart-pie"></i> Résumé</h4>
                <ul class="contact-info-list">
                    <li><strong>Tâches :</strong> {{ $phase->taches->count() }}</li>
                    <li><strong>Terminées :</strong> {{ $phase->taches->filter(fn($t) => $t->statut?->libelle === 'Terminé')->count() }}</li>
                    <li><strong>En retard :</strong> <span style="color:{{ $phase->taches->filter(fn($t) => $t->estEnRetard())->count() > 0 ? '#DC2626' : '#16A34A' }};">{{ $phase->taches->filter(fn($t) => $t->estEnRetard())->count() }}</span></li>
                    @if($phase->jalons)<li><strong>Jalons :</strong> {{ $phase->jalons->count() }}</li>@endif
                    @if($phase->livrables)<li><strong>Livrables :</strong> {{ $phase->livrables->count() }}</li>@endif
                </ul>
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
