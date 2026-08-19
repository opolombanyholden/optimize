@extends('layouts.app')

@section('title', $plan->titre)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('objectifs.dashboard') }}">Objectifs</a></li>
        <li class="breadcrumb-item"><a href="{{ route('objectifs.plans-action.index') }}">Plans d'action</a></li>
        <li class="breadcrumb-item active">{{ \Illuminate\Support\Str::limit($plan->titre, 50) }}</li>
    </ol>
@endsection

@section('content')
@php $enRetard = $plan->estEnRetard(); @endphp

<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-start gap-3">
        <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
              style="width:54px;height:54px;background:{{ $plan->statut_couleur }}20;color:{{ $plan->statut_couleur }};font-size:1.4rem;">
            <i class="fas fa-bolt"></i>
        </span>
        <div>
            <h1 class="h4 mb-1">{{ $plan->titre }}</h1>
            <p class="text-muted mb-0">
                <span class="badge" style="background:{{ $plan->statut_couleur }}; color:#fff;">{{ $plan->statut_libelle }}</span>
                <span class="badge" style="background:{{ $plan->priorite_couleur }}; color:#fff;">Priorité {{ ucfirst($plan->priorite) }}</span>
                @if($enRetard)
                    <span class="badge bg-danger"><i class="fas fa-clock me-1"></i>En retard</span>
                @endif
                @if($plan->objectif)
                    · Rattaché à <a href="{{ route('objectifs.objectifs.show', $plan->objectif) }}">{{ $plan->objectif->titre }}</a>
                @endif
            </p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('objectifs.plans-action.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
        @can('update:plan_action')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEditPlan">
                <i class="fas fa-pen me-1"></i> Modifier
            </button>
        @endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        {{-- Avancement --}}
        <div class="card data-card mb-3">
            <div class="card-header"><strong><i class="fas fa-chart-line me-1"></i> Avancement</strong></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small text-muted">Progression</span>
                    <strong>{{ $plan->avancement ?? 0 }}%</strong>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $plan->avancement ?? 0 }}%; background-color: {{ $plan->statut_couleur }};"></div>
                </div>
            </div>
        </div>

        {{-- Description --}}
        @if($plan->description)
            <div class="card data-card mb-3">
                <div class="card-header"><strong><i class="fas fa-align-left me-1"></i> Description</strong></div>
                <div class="card-body">
                    <p class="mb-0">{!! nl2br(e($plan->description)) !!}</p>
                </div>
            </div>
        @endif

        {{-- Résultats attendus + Moyens --}}
        @if($plan->resultats_attendus || $plan->moyens_requis)
            <div class="row g-3 mb-3">
                @if($plan->resultats_attendus)
                    <div class="col-md-6">
                        <div class="card data-card h-100">
                            <div class="card-header"><strong><i class="fas fa-bullseye me-1 text-success"></i> Résultats attendus</strong></div>
                            <div class="card-body"><p class="mb-0 small">{!! nl2br(e($plan->resultats_attendus)) !!}</p></div>
                        </div>
                    </div>
                @endif
                @if($plan->moyens_requis)
                    <div class="col-md-6">
                        <div class="card data-card h-100">
                            <div class="card-header"><strong><i class="fas fa-tools me-1 text-warning"></i> Moyens requis</strong></div>
                            <div class="card-body"><p class="mb-0 small">{!! nl2br(e($plan->moyens_requis)) !!}</p></div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Budget --}}
        @if($plan->budget_estime > 0 || $plan->budget_reel > 0)
            <div class="card data-card">
                <div class="card-header"><strong><i class="fas fa-coins me-1"></i> Budget</strong></div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Estimé</small>
                            <strong class="fs-5">{{ number_format($plan->budget_estime ?? 0, 0, ',', ' ') }} XAF</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Réel</small>
                            <strong class="fs-5 {{ $plan->budget_reel > $plan->budget_estime ? 'text-danger' : 'text-success' }}">
                                {{ number_format($plan->budget_reel ?? 0, 0, ',', ' ') }} XAF
                            </strong>
                        </div>
                        <div class="col-md-4">
                            @php
                                $ecart = ($plan->budget_reel ?? 0) - ($plan->budget_estime ?? 0);
                            @endphp
                            <small class="text-muted d-block">Écart</small>
                            <strong class="fs-5 {{ $ecart > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $ecart > 0 ? '+' : '' }}{{ number_format($ecart, 0, ',', ' ') }} XAF
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar info --}}
    <div class="col-lg-4">
        <div class="card data-card mb-3">
            <div class="card-header"><strong><i class="fas fa-info-circle me-1"></i> Informations</strong></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-5 text-muted">Responsable</dt>
                    <dd class="col-sm-7">{{ $plan->responsable?->name ?? '—' }}</dd>

                    <dt class="col-sm-5 text-muted">Début</dt>
                    <dd class="col-sm-7">{{ $plan->date_debut?->translatedFormat('d M Y') ?? '—' }}</dd>

                    <dt class="col-sm-5 text-muted">Échéance</dt>
                    <dd class="col-sm-7 {{ $enRetard ? 'text-danger fw-bold' : '' }}">
                        {{ $plan->date_echeance?->translatedFormat('d M Y') ?? '—' }}
                    </dd>

                    @if($plan->date_realisation)
                        <dt class="col-sm-5 text-muted">Réalisé le</dt>
                        <dd class="col-sm-7 text-success">{{ $plan->date_realisation->translatedFormat('d M Y') }}</dd>
                    @endif

                    <dt class="col-sm-5 text-muted">Créé par</dt>
                    <dd class="col-sm-7">{{ $plan->auteur?->name ?? '—' }}</dd>

                    <dt class="col-sm-5 text-muted">Créé le</dt>
                    <dd class="col-sm-7">{{ $plan->created_at?->translatedFormat('d M Y') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Objectif parent --}}
        @if($plan->objectif)
            <div class="card data-card border-info">
                <div class="card-header bg-info text-white"><strong><i class="fas fa-bullseye me-1"></i> Objectif rattaché</strong></div>
                <div class="card-body">
                    <h6 class="mb-1">
                        <a href="{{ route('objectifs.objectifs.show', $plan->objectif) }}">{{ $plan->objectif->titre }}</a>
                    </h6>
                    @if($plan->objectif->responsable)
                        <small class="text-muted d-block">Pilote : {{ $plan->objectif->responsable->name }}</small>
                    @endif
                    <div class="mt-2">
                        <small class="text-muted">Progression objectif :</small>
                        <div class="progress mt-1" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ $plan->objectif->progression }}%"></div>
                        </div>
                        <small class="text-end d-block mt-1">{{ $plan->objectif->progression }}%</small>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modal Edit (reuse pattern from index) --}}
@can('update:plan_action')
<div class="modal fade" id="modalEditPlan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('objectifs.plans-action.update', $plan) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Modifier le plan d'action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="titre" class="form-control" required value="{{ $plan->titre }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priorité <span class="text-danger">*</span></label>
                        <select name="priorite" class="form-select" required>
                            @foreach(['basse','normale','haute','urgente'] as $p)
                                <option value="{{ $p }}" @selected($plan->priorite === $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Objectif rattaché <span class="text-danger">*</span></label>
                        <select name="objectif_id" class="form-select" required>
                            @foreach($objectifs as $o)
                                <option value="{{ $o->id }}" @selected($plan->objectif_id === $o->id)>{{ $o->titre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Responsable</label>
                        <select name="responsable_id" class="form-select">
                            <option value="">—</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @selected($plan->responsable_id === $u->id)>{{ $u->name }} {{ $u->prenoms }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select" required>
                            @foreach(['planifie','en_cours','realise','reporte','annule'] as $s)
                                <option value="{{ $s }}" @selected($plan->statut === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Avancement (%)</label>
                        <input type="number" name="avancement" class="form-control" min="0" max="100" value="{{ $plan->avancement ?? 0 }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Échéance</label>
                        <input type="date" name="date_echeance" class="form-control" value="{{ $plan->date_echeance?->toDateString() }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Budget estimé (XAF)</label>
                        <input type="number" name="budget_estime" class="form-control" min="0" value="{{ $plan->budget_estime }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Budget réel (XAF)</label>
                        <input type="number" name="budget_reel" class="form-control" min="0" value="{{ $plan->budget_reel }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ $plan->description }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Résultats attendus</label>
                        <textarea name="resultats_attendus" class="form-control" rows="2">{{ $plan->resultats_attendus }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Moyens requis</label>
                        <textarea name="moyens_requis" class="form-control" rows="2">{{ $plan->moyens_requis }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
