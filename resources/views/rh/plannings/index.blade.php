@extends('layouts.app')

@section('title', 'Plannings & Emplois du temps')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Plannings & Emplois du temps</li>
    </ol>
@endsection

@section('content')
@php
    $debutCarbon = $debut instanceof \Carbon\Carbon ? $debut : \Carbon\Carbon::parse($debut);
    $finCarbon   = $fin   instanceof \Carbon\Carbon ? $fin   : \Carbon\Carbon::parse($fin);
    $semainePrecedente = $debutCarbon->copy()->subWeek();
    $semaineSuivante   = $debutCarbon->copy()->addWeek();

    $jours = [];
    for ($d = $debutCarbon->copy(); $d->lte($finCarbon); $d->addDay()) {
        $jours[$d->format('Y-m-d')] = $d->copy();
    }

    $planningsParJour = [];
    foreach ($plannings as $p) {
        $key = $p->date_jour?->format('Y-m-d');
        if (!$key) { continue; }
        $planningsParJour[$key][] = $p;
    }

    $couleursType = [
        'travail' => 'primary',
        'repos'   => 'secondary',
        'ferie'   => 'info',
        'conge'   => 'success',
        'absence' => 'danger',
    ];
@endphp
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Plannings & Emplois du temps</h1>
        <p class="text-muted mb-0">Vue hebdomadaire des horaires du personnel</p>
    </div>
    <a href="{{ route('rh.plannings.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouveau planning
    </a>
</div>

{{-- Filtres semaine --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.plannings.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="debut" class="form-label">Debut de semaine</label>
                <input type="date" name="debut" id="debut" class="form-control" value="{{ $debutCarbon->format('Y-m-d') }}">
            </div>
            <div class="col-12 col-md-3">
                <label for="fin" class="form-label">Fin de semaine</label>
                <input type="date" name="fin" id="fin" class="form-control" value="{{ $finCarbon->format('Y-m-d') }}">
            </div>
            <div class="col-12 col-md-3">
                <label for="employee_id" class="form-label">Employe</label>
                <select name="employee_id" id="employee_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les employes</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.plannings.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Navigation semaine --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('rh.plannings.index', array_merge(request()->except(['debut','fin']), ['debut' => $semainePrecedente->format('Y-m-d'), 'fin' => $semainePrecedente->copy()->addDays(6)->format('Y-m-d')])) }}" class="btn btn-outline-secondary">
        <i class="fas fa-chevron-left me-1"></i>Semaine precedente
    </a>
    <h5 class="mb-0 text-muted">
        Semaine du {{ $debutCarbon->format('d/m/Y') }} au {{ $finCarbon->format('d/m/Y') }}
    </h5>
    <a href="{{ route('rh.plannings.index', array_merge(request()->except(['debut','fin']), ['debut' => $semaineSuivante->format('Y-m-d'), 'fin' => $semaineSuivante->copy()->addDays(6)->format('Y-m-d')])) }}" class="btn btn-outline-secondary">
        Semaine suivante<i class="fas fa-chevron-right ms-1"></i>
    </a>
</div>

{{-- Vue calendrier hebdomadaire --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-calendar-week me-2 text-muted"></i>Calendrier hebdomadaire</h5>
        <span class="text-muted">{{ $plannings->count() }} entree(s)</span>
    </div>
    <div class="card-body p-0">
        @if(empty($jours))
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucun jour dans la plage selectionnee</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered mb-0" style="table-layout: fixed;">
                    <thead>
                        <tr class="text-center">
                            @foreach($jours as $cle => $date)
                                <th class="bg-light">
                                    <div class="text-uppercase small text-muted">{{ $date->isoFormat('ddd') }}</div>
                                    <div><strong>{{ $date->format('d/m') }}</strong></div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($jours as $cle => $date)
                                <td class="align-top" style="height: 220px; vertical-align: top;">
                                    @if(!empty($planningsParJour[$cle]))
                                        @foreach($planningsParJour[$cle] as $p)
                                            @php $color = $couleursType[$p->type_journee] ?? 'secondary'; @endphp
                                            <a href="{{ route('rh.plannings.show', $p) }}" class="d-block text-decoration-none mb-2">
                                                <div class="border-start border-{{ $color }} border-4 ps-2 py-1 bg-white">
                                                    <div class="small fw-semibold text-dark">
                                                        {{ $p->employee?->noms }} {{ $p->employee?->prenoms }}
                                                    </div>
                                                    <div class="small text-muted">
                                                        @if($p->heure_debut && $p->heure_fin)
                                                            {{ \Illuminate\Support\Str::limit($p->heure_debut, 5, '') }} - {{ \Illuminate\Support\Str::limit($p->heure_fin, 5, '') }}
                                                        @else
                                                            <em>{{ \App\Models\Planning::TYPES_JOURNEE[$p->type_journee] ?? $p->type_journee }}</em>
                                                        @endif
                                                    </div>
                                                    @if($p->lieu)
                                                        <div class="small text-muted">
                                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $p->lieu }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </a>
                                        @endforeach
                                    @else
                                        <div class="text-center text-muted small py-3">
                                            <i class="fas fa-minus"></i>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
