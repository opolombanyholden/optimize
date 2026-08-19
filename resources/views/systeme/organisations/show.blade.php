@extends('layouts.app')

@section('title', $organisation->label)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Administration</li>
        <li class="breadcrumb-item"><a href="{{ route('systeme.organisations.index') }}">Organisations</a></li>
        <li class="breadcrumb-item active">{{ $organisation->label }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ $organisation->label }}</h1>
        <p class="text-muted mb-0">Details de l'organisation</p>
    </div>
    <div class="d-flex gap-2">
        @can('update:organisation')
        <a href="{{ route('systeme.organisations.edit', $organisation) }}" class="btn btn-outline-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        @endcan
        <a href="{{ route('systeme.organisations.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Informations generales --}}
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2 text-muted"></i>Informations generales</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted fw-bold">Designation</label>
                        <p>{{ $organisation->label }}</p>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted fw-bold">Type</label>
                        <p><span class="badge bg-info text-dark">{{ $organisation->type->label ?? '-' }}</span></p>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted fw-bold">Organisation parente</label>
                        <p>
                            @if($organisation->parent)
                                <a href="{{ route('systeme.organisations.show', $organisation->parent) }}">{{ $organisation->parent->label }}</a>
                            @else
                                <span class="text-muted">Racine</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted fw-bold">Responsable</label>
                        <p>{{ $organisation->chef->name ?? '-' }}</p>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted fw-bold">Statut</label>
                        <p>
                            @if($organisation->statut == 1)
                                <span class="badge badge-status badge-actif">Actif</span>
                            @else
                                <span class="badge badge-status badge-rejete">Inactif</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted fw-bold">Date de creation</label>
                        <p>{{ $organisation->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                    @if($organisation->introduction)
                    <div class="col-12">
                        <label class="form-label text-muted fw-bold">Introduction</label>
                        <p>{{ $organisation->introduction }}</p>
                    </div>
                    @endif
                    @if($organisation->description)
                    <div class="col-12">
                        <label class="form-label text-muted fw-bold">Description</label>
                        <p>{{ $organisation->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Sous-organisations --}}
    <div class="col-12 col-lg-4">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-sitemap me-2 text-muted"></i>Sous-organisations</h5>
            </div>
            <div class="card-body p-0">
                @if($organisation->enfants->isEmpty())
                    <div class="empty-state py-4">
                        <i class="fas fa-folder-open" style="font-size: 2rem;"></i>
                        <p class="mb-0">Aucune sous-organisation</p>
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($organisation->enfants as $enfant)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('systeme.organisations.show', $enfant) }}">{{ $enfant->label }}</a>
                                <br><small class="text-muted">{{ $enfant->type->label ?? '' }}</small>
                            </div>
                            @if($enfant->statut == 1)
                                <span class="badge badge-status badge-actif">Actif</span>
                            @else
                                <span class="badge badge-status badge-rejete">Inactif</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
