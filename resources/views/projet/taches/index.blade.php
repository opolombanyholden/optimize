@extends('layouts.app')
@section('title', $projet->nom . ' — Tâches')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">Tâches</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'taches'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-list-check"></i></span>
                Tâches — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">{{ $taches->count() }} tâche(s) · {{ $taches->where('statut.libelle', 'Terminé')->count() }} terminée(s)</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="ged-view-toggle" id="tpViewToggle">
                <button class="ged-view-btn active" data-view="list" title="Liste"><i class="fas fa-list"></i></button>
                <button class="ged-view-btn" data-view="kanban" title="Kanban"><i class="fas fa-columns"></i></button>
            </div>
            <a href="{{ route('intranet.taches.create') }}?projet_id={{ $projet->id }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);">
                <i class="fas fa-plus me-2"></i> Nouvelle tâche
            </a>
        </div>
    </div>

    @if($taches->count())

    {{-- Vue liste --}}
    <div id="viewList" class="tp-view">
        {{-- Par phase --}}
        @if($projet->phases->count())
            @foreach($projet->phases->sortBy('ordre') as $phase)
            @php $phaseTaches = $taches->where('phase_id', $phase->id); @endphp
            @if($phaseTaches->count())
            <div class="contact-detail-card mb-3">
                <h4 class="contact-detail-card-title">
                    <span class="wbs-phase-code" style="--phase-color:{{ $phase->couleur ?? '#0D9488' }};font-size:.6rem;">{{ $phase->code_wbs }}</span>
                    {{ $phase->nom }}
                    <span class="ms-auto" style="font-size:.72rem;color:#94A3B8;">{{ $phaseTaches->count() }} tâche(s)</span>
                </h4>
                <div class="tache-list">
                    @foreach($phaseTaches->sortBy('ordre') as $t)
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
            @endforeach

            {{-- Tâches sans phase --}}
            @php $sansPhase = $taches->whereNull('phase_id'); @endphp
            @if($sansPhase->count())
            <div class="contact-detail-card mb-3">
                <h4 class="contact-detail-card-title"><i class="fas fa-inbox"></i> Sans phase</h4>
                <div class="tache-list">
                    @foreach($sansPhase as $t)
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
        @else
            {{-- Pas de phases : liste plate --}}
            <div class="tache-list">
                @foreach($taches->sortByDesc('priorite_id') as $t)
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
        @endif
    </div>

    {{-- Vue Kanban --}}
    <div id="viewKanban" class="tp-view d-none">
        <div class="kanban-board" style="min-height:400px;">
            @foreach($statuts as $s)
            @php $items = $taches->filter(fn($t) => $t->statut_id === $s->id); @endphp
            <div class="kanban-column" style="--col-color: {{ $s->couleur }}; flex: 0 0 250px;">
                <div class="kanban-col-header">
                    <div class="kanban-col-title"><span class="kanban-col-dot"></span> {{ $s->libelle }}</div>
                    <span class="kanban-col-count">{{ $items->count() }}</span>
                </div>
                <div class="kanban-col-body">
                    @foreach($items as $t)
                    <a href="{{ route('intranet.taches.show', $t) }}" class="kanban-card" style="text-decoration:none;color:inherit;border-left-color:{{ $t->priorite_couleur }};">
                        <div class="kanban-card-title" style="display:block;">{{ $t->titre }}</div>
                        @if($t->phase)<div class="kanban-card-org"><i class="fas fa-sitemap"></i> {{ $t->phase->nom }}</div>@endif
                        @if($t->responsable)<div class="kanban-card-contact"><i class="fas fa-user"></i> {{ $t->responsable->prenoms }}</div>@endif
                        <div class="tache-progress-bar mt-2" style="height:4px;">
                            <div class="tache-progress-fill" style="width:{{ $t->avancement }}%;background:{{ $s->couleur }};"></div>
                        </div>
                        <div class="kanban-card-footer">
                            <span class="kanban-card-amount">{{ $t->avancement }}%</span>
                            @if($t->date_fin)<span class="kanban-card-prob {{ $t->estEnRetard() ? 'text-danger' : '' }}">{{ $t->date_fin->format('d/m') }}</span>@endif
                        </div>
                        @if($t->estEnRetard())<div class="kanban-card-resp" style="background:#DC2626;"><i class="fas fa-triangle-exclamation" style="font-size:.55rem;"></i></div>@endif
                    </a>
                    @endforeach
                    @if($items->isEmpty())<div class="text-center py-3 text-muted" style="font-size:.72rem;">—</div>@endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-list-check" style="color:#0D9488;"></i></div>
        <h3>Aucune tâche dans ce projet</h3>
        <a href="{{ route('intranet.taches.create') }}?projet_id={{ $projet->id }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);">
            <i class="fas fa-plus me-2"></i> Créer la première
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const views = document.querySelectorAll('.tp-view');
    const btns  = document.querySelectorAll('#tpViewToggle .ged-view-btn');
    const saved = localStorage.getItem('tp-view') || 'list';
    function setView(mode) {
        views.forEach(v => v.classList.add('d-none'));
        document.getElementById('view' + mode.charAt(0).toUpperCase() + mode.slice(1))?.classList.remove('d-none');
        btns.forEach(b => b.classList.toggle('active', b.dataset.view === mode));
        localStorage.setItem('tp-view', mode);
    }
    setView(saved);
    btns.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));
});
</script>
@endpush
