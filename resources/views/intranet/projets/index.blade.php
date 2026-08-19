@extends('layouts.app')
@section('title', 'Projets')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Projets</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-diagram-project"></i></span>
                Tous les projets
            </h1>
            <p class="page-subtitle">Vue d'ensemble des projets de l'organisation</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            {{-- Toggle vue --}}
            <div class="ged-view-toggle" id="projetViewToggle">
                <button class="ged-view-btn active" data-view="cards" title="Cartes"><i class="fas fa-grip"></i></button>
                <button class="ged-view-btn" data-view="table" title="Tableau"><i class="fas fa-table"></i></button>
                <button class="ged-view-btn" data-view="kanban" title="Kanban"><i class="fas fa-columns"></i></button>
            </div>
            @can('create:projet_intranet')
            <a href="{{ route('intranet.projets.create') }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);">
                <i class="fas fa-plus me-2"></i> Nouveau projet
            </a>
            @endcan
        </div>
    </div>

    {{-- Stats --}}
    <div class="courrier-stats mb-4">
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#0D9488;"><i class="fas fa-diagram-project"></i></div>
            <div><div class="csc-value">{{ $stats['total'] }}</div><div class="csc-label">Total</div></div>
        </div>
        <div class="courrier-stat-card highlight" style="background:linear-gradient(135deg,#F0FDFA,#CCFBF1);">
            <div class="csc-icon" style="background:#0891B2;"><i class="fas fa-spinner"></i></div>
            <div><div class="csc-value">{{ $stats['en_cours'] }}</div><div class="csc-label">En cours</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#16A34A;"><i class="fas fa-check-circle"></i></div>
            <div><div class="csc-value">{{ $stats['termines'] }}</div><div class="csc-label">Terminés</div></div>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="filters-bar mb-4">
        <div class="filters-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un projet…">
        </div>
        <select name="categorie" class="filters-select" onchange="this.form.submit()">
            <option value="">Toutes catégories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" @selected(request('categorie') === $cat)>{{ $cat }}</option>
            @endforeach
        </select>
        <label class="filters-toggle">
            <input type="checkbox" name="mes_projets" value="1" @checked(request()->boolean('mes_projets')) onchange="this.form.submit()">
            Mes projets
        </label>
        @if(request('q') || request('categorie') || request()->boolean('mes_projets'))
        <a href="{{ route('intranet.projets.index') }}" class="btn-reset"><i class="fas fa-xmark"></i></a>
        @endif
    </form>

    @if($projets->count())

    {{-- ═══ VUE CARTES ═══════════════════════════════════ --}}
    <div id="viewCards" class="projet-view">
        <div class="projets-grid">
            @foreach($projets as $p)
            <a href="{{ route('projet.overview', $p) }}" class="projet-card" style="--accent: {{ $p->statut_couleur }};">
                <div class="projet-card-header">
                    <div class="projet-card-badges">
                        @if($p->statut)<span class="opp-stage-badge" style="background:{{ $p->statut_couleur }};font-size:.6rem;">{{ $p->statut->libelle }}</span>@endif
                        @if($p->priorite)<span class="opp-stage-badge" style="background:{{ $p->priorite_couleur }};font-size:.6rem;">{{ $p->priorite->libelle }}</span>@endif
                        @if($p->estEnRetard())<span class="contact-tag-lg hot" style="font-size:.5rem;padding:.1rem .3rem;">⚠️</span>@endif
                    </div>
                    @if($p->code_projet)<code class="projet-code">{{ $p->code_projet }}</code>@endif
                </div>
                <div class="projet-card-body">
                    <h3 class="projet-card-title">{{ $p->nom }}</h3>
                    @if($p->categorie)<span class="projet-card-cat">{{ $p->categorie }}</span>@endif
                    @if($p->description)<p class="projet-card-desc">{{ Str::limit(strip_tags($p->description), 80) }}</p>@endif
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
                        <span><i class="fas fa-users"></i> {{ $p->membres_count }}</span>
                    </div>
                    @if($p->date_fin)
                    <span class="projet-card-date {{ $p->estEnRetard() ? 'text-danger fw-bold' : '' }}">
                        <i class="far fa-calendar"></i> {{ $p->date_fin->format('d/m/Y') }}
                    </span>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ═══ VUE TABLEAU ═════════════════════════════════ --}}
    <div id="viewTable" class="projet-view d-none">
        <div class="form-card">
            <table class="opp-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Projet</th>
                        <th>Catégorie</th>
                        <th>Chef de projet</th>
                        <th>Avancement</th>
                        <th>Statut</th>
                        <th>Priorité</th>
                        <th>Budget</th>
                        <th>Tâches</th>
                        <th>Échéance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projets as $p)
                    <tr onclick="window.location='{{ route('projet.overview', $p) }}'" style="cursor:pointer;">
                        <td><code class="opp-ref">{{ $p->code_projet ?? '—' }}</code></td>
                        <td>
                            <strong>{{ $p->nom }}</strong>
                            @if($p->estEnRetard())<i class="fas fa-triangle-exclamation text-danger ms-1" style="font-size:.7rem;"></i>@endif
                        </td>
                        <td>{{ $p->categorie ?? '—' }}</td>
                        <td>{{ $p->chefProjet ? $p->chefProjet->prenoms . ' ' . $p->chefProjet->name : '—' }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="tache-progress-bar" style="width:80px;">
                                    <div class="tache-progress-fill" style="width:{{ $p->avancement }}%;background:{{ $p->statut_couleur }};"></div>
                                </div>
                                <span class="tache-progress-pct">{{ $p->avancement }}%</span>
                            </div>
                        </td>
                        <td>@if($p->statut)<span class="opp-stage-badge" style="background:{{ $p->statut_couleur }};">{{ $p->statut->libelle }}</span>@endif</td>
                        <td>@if($p->priorite)<span class="opp-stage-badge" style="background:{{ $p->priorite_couleur }};">{{ $p->priorite->libelle }}</span>@endif</td>
                        <td>{{ $p->budget_approuve ? number_format($p->budget_approuve, 0, ',', ' ') . ' ' . $p->devise : '—' }}</td>
                        <td>{{ $p->taches_count }}</td>
                        <td class="{{ $p->estEnRetard() ? 'text-danger fw-bold' : '' }}">{{ $p->date_fin?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══ VUE KANBAN ══════════════════════════════════ --}}
    <div id="viewKanban" class="projet-view d-none">
        @php
            $statutColors = [
                'Non démarré' => '#94A3B8', 'En cours' => '#0891B2',
                'En pause' => '#F59E0B', 'Terminé' => '#16A34A', 'Annulé' => '#DC2626',
            ];
            $colonnes = $projets->groupBy(fn($p) => $p->statut?->libelle ?? 'Indéfini');
        @endphp
        <div class="kanban-board" style="min-height:400px;">
            @foreach($statutColors as $statut => $color)
            @php $items = $colonnes->get($statut, collect()); @endphp
            <div class="kanban-column" style="--col-color: {{ $color }}; flex: 0 0 260px;">
                <div class="kanban-col-header">
                    <div class="kanban-col-title">
                        <span class="kanban-col-dot"></span>
                        {{ $statut }}
                    </div>
                    <span class="kanban-col-count">{{ $items->count() }}</span>
                </div>
                <div class="kanban-col-body">
                    @foreach($items as $p)
                    <a href="{{ route('projet.overview', $p) }}" class="kanban-card" style="text-decoration:none;color:inherit;">
                        <div class="kanban-card-title" style="display:block;">{{ $p->nom }}</div>
                        @if($p->code_projet)<div class="kanban-card-org"><i class="fas fa-hashtag"></i> {{ $p->code_projet }}</div>@endif
                        @if($p->chefProjet)<div class="kanban-card-contact"><i class="fas fa-user-tie"></i> {{ $p->chefProjet->prenoms }}</div>@endif
                        <div class="tache-progress-bar mt-2" style="height:5px;">
                            <div class="tache-progress-fill" style="width:{{ $p->avancement }}%;background:{{ $color }};"></div>
                        </div>
                        <div class="kanban-card-footer">
                            <span class="kanban-card-amount">{{ $p->avancement }}%</span>
                            <span class="kanban-card-prob">{{ $p->taches_count }} tâches</span>
                        </div>
                        @if($p->estEnRetard())
                        <div class="kanban-card-resp" style="background:#DC2626;" title="En retard">
                            <i class="fas fa-triangle-exclamation" style="font-size:.55rem;"></i>
                        </div>
                        @endif
                    </a>
                    @endforeach
                    @if($items->isEmpty())
                    <div class="text-center py-3 text-muted" style="font-size:.75rem;">Aucun projet</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">{{ $projets->links() }}</div>

    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-diagram-project" style="color:#0D9488;"></i></div>
        <h3>Aucun projet</h3>
        @can('create:projet_intranet')
        <a href="{{ route('intranet.projets.create') }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-plus me-2"></i> Créer le premier</a>
        @endcan
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const views = document.querySelectorAll('.projet-view');
    const btns  = document.querySelectorAll('#projetViewToggle .ged-view-btn');
    const saved = localStorage.getItem('projet-view') || 'cards';

    function setView(mode) {
        views.forEach(v => v.classList.add('d-none'));
        document.getElementById('view' + mode.charAt(0).toUpperCase() + mode.slice(1))?.classList.remove('d-none');
        btns.forEach(b => b.classList.toggle('active', b.dataset.view === mode));
        localStorage.setItem('projet-view', mode);
    }

    setView(saved);
    btns.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));
});
</script>
@endpush
