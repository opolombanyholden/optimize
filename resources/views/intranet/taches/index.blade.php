@extends('layouts.app')
@section('title', 'Mes tâches')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Tâches</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-list-check"></i></span>
                Mes tâches
            </h1>
            <p class="page-subtitle">Suivi et gestion de vos tâches assignées</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="ged-view-toggle" id="tacheViewToggle">
                <button class="ged-view-btn active" data-view="list" title="Liste"><i class="fas fa-list"></i></button>
                <button class="ged-view-btn" data-view="table" title="Tableau"><i class="fas fa-table"></i></button>
                <button class="ged-view-btn" data-view="kanban" title="Kanban"><i class="fas fa-columns"></i></button>
            </div>
            @can('create:tache_intranet')
            <a href="{{ route('intranet.taches.create') }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);">
                <i class="fas fa-plus me-2"></i> Nouvelle tâche
            </a>
            @endcan
        </div>
    </div>

    {{-- Stats --}}
    <div class="courrier-stats mb-4">
        <a href="{{ route('intranet.taches.index') }}" class="courrier-stat-card">
            <div class="csc-icon" style="background:#0D9488;"><i class="fas fa-list-check"></i></div>
            <div><div class="csc-value">{{ $stats['total'] }}</div><div class="csc-label">Total</div></div>
        </a>
        <a href="{{ route('intranet.taches.index', ['mes_taches' => 1]) }}" class="courrier-stat-card highlight" style="background:linear-gradient(135deg,#F0FDFA,#CCFBF1);">
            <div class="csc-icon" style="background:#0891B2;"><i class="fas fa-user-check"></i></div>
            <div><div class="csc-value">{{ $stats['mes_taches'] }}</div><div class="csc-label">Mes tâches actives</div></div>
        </a>
        <a href="{{ route('intranet.taches.index', ['en_retard' => 1]) }}" class="courrier-stat-card">
            <div class="csc-icon" style="background:#DC2626;"><i class="fas fa-triangle-exclamation"></i></div>
            <div><div class="csc-value">{{ $stats['en_retard'] }}</div><div class="csc-label">En retard</div></div>
        </a>
        <a href="{{ route('intranet.taches.index', ['a_valider' => 1]) }}" class="courrier-stat-card">
            <div class="csc-icon" style="background:#F59E0B;"><i class="fas fa-check-double"></i></div>
            <div><div class="csc-value">{{ $stats['a_valider'] }}</div><div class="csc-label">À valider</div></div>
        </a>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="filters-bar mb-4">
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher une tâche…">
        </div>
        <select name="projet" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous projets</option>
            @foreach($projets as $p)
                <option value="{{ $p->id }}" @selected(request('projet') == $p->id)>{{ $p->nom }}</option>
            @endforeach
        </select>
        <select name="statut" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous statuts</option>
            @foreach($statuts as $s)
                <option value="{{ $s->id }}" @selected(request('statut') == $s->id)>{{ $s->libelle }}</option>
            @endforeach
        </select>
        <select name="priorite" class="filters-select" onchange="this.form.submit()">
            <option value="">Toutes priorités</option>
            @foreach($priorites as $p)
                <option value="{{ $p->id }}" @selected(request('priorite') == $p->id)>{{ $p->libelle }}</option>
            @endforeach
        </select>
        <label class="filters-toggle">
            <input type="checkbox" name="mes_taches" value="1" @checked(request()->boolean('mes_taches')) onchange="this.form.submit()">
            Mes tâches
        </label>
        <label class="filters-toggle">
            <input type="checkbox" name="en_retard" value="1" @checked(request()->boolean('en_retard')) onchange="this.form.submit()">
            En retard
        </label>
        <label class="filters-toggle">
            <input type="checkbox" name="a_valider" value="1" @checked(request()->boolean('a_valider')) onchange="this.form.submit()">
            À valider
        </label>
        @if(request('q') || request('projet') || request('statut') || request('priorite') || request()->boolean('mes_taches') || request()->boolean('en_retard') || request()->boolean('a_valider'))
        <a href="{{ route('intranet.taches.index') }}" class="btn-reset"><i class="fas fa-xmark"></i></a>
        @endif
    </form>

    @if($taches->count())

    {{-- ═══ VUE LISTE ════════════════════════════════════ --}}
    <div id="viewList" class="tache-view">
        <div class="tache-list">
            @foreach($taches as $t)
            <a href="{{ route('intranet.taches.show', $t) }}" class="tache-item {{ $t->estEnRetard() ? 'en-retard' : '' }} {{ $t->est_terminee ? 'terminee' : '' }}">
                <div class="tache-priority" style="background:{{ $t->priorite_couleur }};" title="{{ $t->priorite?->libelle }}"></div>
                <div class="tache-check {{ $t->est_terminee ? 'done' : '' }}">@if($t->est_terminee)<i class="fas fa-check"></i>@endif</div>
                <div class="tache-body">
                    <div class="tache-title">{{ $t->titre }}</div>
                    <div class="tache-meta">
                        @if($t->projet)<span><i class="fas fa-diagram-project"></i> {{ $t->projet->nom }}</span>@endif
                        @if($t->responsable)<span><i class="fas fa-user"></i> {{ $t->responsable->prenoms }}</span>@endif
                        @if($t->activites_count)<span><i class="fas fa-circle-check"></i> {{ $t->activites_count }}</span>@endif
                    </div>
                </div>
                <div class="tache-progress-col">
                    <div class="tache-progress-bar"><div class="tache-progress-fill" style="width:{{ $t->avancement }}%;background:{{ $t->statut_couleur }};"></div></div>
                    <span class="tache-progress-pct">{{ $t->avancement }}%</span>
                </div>
                <div class="tache-status"><span class="opp-stage-badge" style="background:{{ $t->statut_couleur }};font-size:.6rem;">{{ $t->statut?->libelle }}</span></div>
                <div class="tache-deadline {{ $t->estEnRetard() ? 'text-danger fw-bold' : '' }}">@if($t->date_fin)<i class="far fa-calendar"></i> {{ $t->date_fin->format('d/m/Y') }}@endif</div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ═══ VUE TABLEAU ══════════════════════════════════ --}}
    <div id="viewTable" class="tache-view d-none">
        <div class="form-card">
            <table class="opp-table">
                <thead>
                    <tr>
                        <th style="width:5px;"></th>
                        <th>Tâche</th>
                        <th>Projet</th>
                        <th>Responsable</th>
                        <th>Avancement</th>
                        <th>Statut</th>
                        <th>Priorité</th>
                        <th>Début</th>
                        <th>Échéance</th>
                        <th>Validation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($taches as $t)
                    <tr onclick="window.location='{{ route('intranet.taches.show', $t) }}'" style="cursor:pointer;" class="{{ $t->estEnRetard() ? 'table-danger' : '' }}">
                        <td><div style="width:5px;height:28px;border-radius:3px;background:{{ $t->priorite_couleur }};"></div></td>
                        <td>
                            <strong>{{ $t->titre }}</strong>
                            @if($t->estEnRetard())<i class="fas fa-triangle-exclamation text-danger ms-1" style="font-size:.65rem;"></i>@endif
                            @if($t->resume)<br><small class="text-muted">{{ Str::limit($t->resume, 50) }}</small>@endif
                        </td>
                        <td><small>{{ $t->projet?->nom ?? '—' }}</small></td>
                        <td><small>{{ $t->responsable ? $t->responsable->prenoms . ' ' . $t->responsable->name : '—' }}</small></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="tache-progress-bar" style="width:60px;"><div class="tache-progress-fill" style="width:{{ $t->avancement }}%;background:{{ $t->statut_couleur }};"></div></div>
                                <span style="font-size:.72rem;font-weight:700;">{{ $t->avancement }}%</span>
                            </div>
                        </td>
                        <td>@if($t->statut)<span class="opp-stage-badge" style="background:{{ $t->statut_couleur }};font-size:.58rem;">{{ $t->statut->libelle }}</span>@endif</td>
                        <td>@if($t->priorite)<span class="opp-stage-badge" style="background:{{ $t->priorite_couleur }};font-size:.58rem;">{{ $t->priorite->libelle }}</span>@endif</td>
                        <td><small>{{ $t->date_debut?->format('d/m/Y') ?? '—' }}</small></td>
                        <td class="{{ $t->estEnRetard() ? 'text-danger fw-bold' : '' }}"><small>{{ $t->date_fin?->format('d/m/Y') ?? '—' }}</small></td>
                        <td>
                            @if($t->statut_validation && $t->statut_validation !== 'non_soumis')
                            <span class="opp-stage-badge" style="background:{{ $t->validation_couleur }};font-size:.55rem;">{{ $t->validation_libelle }}</span>
                            @else
                            <small class="text-muted">—</small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══ VUE KANBAN ═══════════════════════════════════ --}}
    <div id="viewKanban" class="tache-view d-none">
        @php
            $statutColors = [];
            foreach($statuts as $s) { $statutColors[$s->libelle] = $s->couleur; }
            $colonnes = $taches->groupBy(fn($t) => $t->statut?->libelle ?? 'Indéfini');
        @endphp
        <div class="kanban-board" style="min-height:400px;">
            @foreach($statuts as $s)
            @php $items = $colonnes->get($s->libelle, collect()); @endphp
            <div class="kanban-column" style="--col-color: {{ $s->couleur }}; flex: 0 0 250px;">
                <div class="kanban-col-header">
                    <div class="kanban-col-title">
                        <span class="kanban-col-dot"></span>
                        {{ $s->libelle }}
                    </div>
                    <span class="kanban-col-count">{{ $items->count() }}</span>
                </div>
                <div class="kanban-col-body">
                    @foreach($items as $t)
                    <a href="{{ route('intranet.taches.show', $t) }}" class="kanban-card" style="text-decoration:none;color:inherit;border-left-color:{{ $t->priorite_couleur }};">
                        <div class="kanban-card-title" style="display:block;">{{ $t->titre }}</div>
                        @if($t->projet)<div class="kanban-card-org"><i class="fas fa-diagram-project"></i> {{ Str::limit($t->projet->nom, 20) }}</div>@endif
                        @if($t->responsable)<div class="kanban-card-contact"><i class="fas fa-user"></i> {{ $t->responsable->prenoms }}</div>@endif
                        <div class="tache-progress-bar mt-2" style="height:4px;">
                            <div class="tache-progress-fill" style="width:{{ $t->avancement }}%;background:{{ $s->couleur }};"></div>
                        </div>
                        <div class="kanban-card-footer">
                            <span class="kanban-card-amount">{{ $t->avancement }}%</span>
                            @if($t->date_fin)
                            <span class="kanban-card-prob {{ $t->estEnRetard() ? 'text-danger' : '' }}">{{ $t->date_fin->format('d/m') }}</span>
                            @endif
                        </div>
                        @if($t->estEnRetard())
                        <div class="kanban-card-resp" style="background:#DC2626;" title="En retard"><i class="fas fa-triangle-exclamation" style="font-size:.55rem;"></i></div>
                        @elseif($t->priorite?->libelle === 'Urgente')
                        <div class="kanban-card-resp" style="background:#F59E0B;" title="Urgente"><i class="fas fa-bolt" style="font-size:.55rem;"></i></div>
                        @endif
                    </a>
                    @endforeach
                    @if($items->isEmpty())
                    <div class="text-center py-3 text-muted" style="font-size:.72rem;">Aucune tâche</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">{{ $taches->links() }}</div>

    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-list-check" style="color:#0D9488;"></i></div>
        <h3>Aucune tâche</h3>
        @can('create:tache_intranet')
        <a href="{{ route('intranet.taches.create') }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-plus me-2"></i> Créer la première</a>
        @endcan
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const views = document.querySelectorAll('.tache-view');
    const btns  = document.querySelectorAll('#tacheViewToggle .ged-view-btn');
    const saved = localStorage.getItem('tache-view') || 'list';

    function setView(mode) {
        views.forEach(v => v.classList.add('d-none'));
        document.getElementById('view' + mode.charAt(0).toUpperCase() + mode.slice(1))?.classList.remove('d-none');
        btns.forEach(b => b.classList.toggle('active', b.dataset.view === mode));
        localStorage.setItem('tache-view', mode);
    }

    setView(saved);
    btns.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));
});
</script>
@endpush
