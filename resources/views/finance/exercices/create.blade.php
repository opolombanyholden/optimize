@extends('layouts.app')

@section('title', 'Nouvel exercice')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.exercices.index') }}">Exercices</a></li>
        <li class="breadcrumb-item active">Nouveau</li>
    </ol>
@endsection

@section('content')
<div class="page-header">
    <h1>Nouvel exercice budg&eacute;taire</h1>
    <p class="text-muted mb-0">Cr&eacute;ez un nouvel exercice pour votre gestion budg&eacute;taire.</p>
</div>

<div class="card data-card">
    <div class="card-header">
        <h5><i class="fas fa-plus-circle me-2 text-muted"></i>Informations de l'exercice</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('finance.exercices.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                {{-- Libell&eacute; --}}
                <div class="col-12 col-md-6">
                    <label for="libelle" class="form-label">Libell&eacute; <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" id="libelle" class="form-control @error('libelle') is-invalid @enderror" value="{{ old('libelle') }}" required placeholder="Ex: Exercice 2026">
                    @error('libelle')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Exercice (ann&eacute;e) --}}
                <div class="col-12 col-md-6">
                    <label for="exercice" class="form-label">Exercice</label>
                    <input type="text" name="exercice" id="exercice" class="form-control @error('exercice') is-invalid @enderror" value="{{ old('exercice') }}" placeholder="Ex: 2026">
                    @error('exercice')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Date d&eacute;but --}}
                <div class="col-12 col-md-6">
                    <label for="datedebut" class="form-label">Date de d&eacute;but</label>
                    <input type="date" name="datedebut" id="datedebut" class="form-control @error('datedebut') is-invalid @enderror" value="{{ old('datedebut') }}">
                    @error('datedebut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Date fin --}}
                <div class="col-12 col-md-6">
                    <label for="datefin" class="form-label">Date de fin</label>
                    <input type="date" name="datefin" id="datefin" class="form-control @error('datefin') is-invalid @enderror" value="{{ old('datefin') }}">
                    @error('datefin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Budget global initial --}}
                <div class="col-12 col-md-6">
                    <label for="budgetglobalinitial" class="form-label">Budget global initial</label>
                    <div class="input-group">
                        <input type="number" name="budgetglobalinitial" id="budgetglobalinitial" class="form-control @error('budgetglobalinitial') is-invalid @enderror" value="{{ old('budgetglobalinitial') }}" min="0" step="0.01" placeholder="0">
                        <span class="input-group-text">F</span>
                        @error('budgetglobalinitial')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Périmètre de planification --}}
                <div class="col-12">
                    <label class="form-label">Périmètre de planification <span class="text-danger">*</span></label>
                    <div class="row g-2">
                        @foreach(\App\Models\Exercice::TYPES_PLANIFICATION as $tk => $tv)
                            @php $couleur = \App\Models\Exercice::TYPE_PLANIFICATION_COULEURS[$tk]; @endphp
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="type_planification" id="tp-{{ $tk }}" value="{{ $tk }}"
                                       @checked(old('type_planification', 'mixte') === $tk) required>
                                <label class="btn btn-outline-{{ $couleur }} w-100 text-start" for="tp-{{ $tk }}">
                                    <i class="fas {{ \App\Models\Exercice::TYPE_PLANIFICATION_ICONES[$tk] }} me-2"></i>
                                    <strong>{{ ucfirst($tk) }}</strong>
                                    <div class="small text-muted">{{ $tv }}</div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted mt-1 d-block">
                        <i class="fas fa-info-circle me-1"></i> Détermine quelles lignes du référentiel seront pré-remplies dans la planification.
                    </small>
                </div>

                {{-- Commentaire --}}
                <div class="col-12">
                    <label for="commentaire" class="form-label">Commentaire</label>
                    <textarea name="commentaire" id="commentaire" rows="3" class="form-control @error('commentaire') is-invalid @enderror" placeholder="Notes ou remarques sur cet exercice...">{{ old('commentaire') }}</textarea>
                    @error('commentaire')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('finance.exercices.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
