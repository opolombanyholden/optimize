@extends('layouts.app')
@section('title', 'Pipeline')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Pipeline</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-chart-gantt"></i></span>
                Pipeline commercial
            </h1>
            <p class="page-subtitle">Vue Kanban des opportunités · drag & drop pour changer d'étape</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('intranet.opportunites.index') }}" class="btn btn-light"><i class="fas fa-list me-2"></i> Vue liste</a>
            @can('create:opportunite')
            <a href="{{ route('intranet.opportunites.create') }}" class="btn btn-intranet"><i class="fas fa-plus me-2"></i> Nouvelle opportunité</a>
            @endcan
        </div>
    </div>

    <form method="GET" class="filters-bar mb-3">
        <select name="responsable" class="filters-select" onchange="this.form.submit()">
            <option value="">Tous responsables</option>
            @foreach($responsables as $r)
                <option value="{{ $r->id }}" @selected(request('responsable')==$r->id)>{{ $r->prenoms }} {{ $r->name }}</option>
            @endforeach
        </select>
        <label class="filters-toggle">
            <input type="checkbox" name="mes_opp" value="1" @checked(request()->boolean('mes_opp')) onchange="this.form.submit()">
            Mes opportunités
        </label>
    </form>

    <div class="kanban-board">
        @foreach($etapes as $etape)
        @php
            $opps = $etape->opportunites;
            $total = $opps->sum('valeur');
        @endphp
        <div class="kanban-column" data-etape-id="{{ $etape->id }}" style="--col-color: {{ $etape->couleur }};">
            <div class="kanban-col-header">
                <div class="kanban-col-title">
                    <span class="kanban-col-dot"></span>
                    {{ $etape->nom }}
                </div>
                <span class="kanban-col-count">{{ $opps->count() }}</span>
            </div>
            <div class="kanban-col-total">
                {{ number_format($total, 0, ',', ' ') }} XAF
            </div>
            <div class="kanban-col-body" data-etape="{{ $etape->id }}">
                @foreach($opps as $opp)
                <div class="kanban-card" data-id="{{ $opp->id }}">
                    <a href="{{ route('intranet.opportunites.show', $opp) }}" class="kanban-card-title">{{ $opp->titre }}</a>
                    @if($opp->organisation)
                        <div class="kanban-card-org"><i class="fas fa-building"></i> {{ $opp->organisation->nom }}</div>
                    @endif
                    @if($opp->contact)
                        <div class="kanban-card-contact"><i class="fas fa-user"></i> {{ $opp->contact->nom_complet }}</div>
                    @endif
                    <div class="kanban-card-footer">
                        @if($opp->valeur)
                            <span class="kanban-card-amount">{{ number_format($opp->valeur, 0, ',', ' ') }} {{ $opp->devise }}</span>
                        @endif
                        <span class="kanban-card-prob">{{ $opp->probabilite }}%</span>
                    </div>
                    @if($opp->responsable)
                        <div class="kanban-card-resp" title="{{ $opp->responsable->prenoms }} {{ $opp->responsable->name }}">
                            {{ strtoupper(substr($opp->responsable->prenoms ?? $opp->responsable->name, 0, 1)) }}{{ strtoupper(substr($opp->responsable->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('.kanban-col-body').forEach(col => {
        new Sortable(col, {
            group: 'kanban',
            animation: 180,
            ghostClass: 'kanban-ghost',
            dragClass:  'kanban-drag',
            onEnd: function (evt) {
                const card     = evt.item;
                const newCol   = evt.to;
                const oppId    = card.dataset.id;
                const newEtape = newCol.dataset.etape;
                const newOrder = Array.from(newCol.children).indexOf(card);

                fetch('/intranet/opportunites/' + oppId + '/move', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ etape_id: newEtape, ordre_kanban: newOrder }),
                });
            },
        });
    });
});
</script>
@endpush
