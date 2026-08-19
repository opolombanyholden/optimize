@extends('layouts.app')

@section('title', 'Modifier mission')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.missions.index') }}">Missions</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Modifier la mission</h1>
        <p class="text-muted mb-0">{{ $mission->label }} &mdash; {{ $mission->employee?->noms }} {{ $mission->employee?->prenoms }}</p>
    </div>
    <a href="{{ route('rh.missions.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.missions.update', $mission) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-route me-2 text-muted"></i>Details de la mission</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="employee_id" class="form-label">Employe</label>
                    <input type="hidden" name="employee_id" value="{{ $mission->employee_id }}">

                    <select id="employee_id" class="form-select" disabled>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $mission->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">L'employe ne peut pas etre modifie</small>
                </div>
                <div class="col-12 col-md-6">
                    <label for="label" class="form-label">Libelle <span class="text-danger">*</span></label>
                    <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $mission->label) }}" required>
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="lieu" class="form-label">Lieu</label>
                    <input type="text" name="lieu" id="lieu" class="form-control @error('lieu') is-invalid @enderror" value="{{ old('lieu', $mission->lieu) }}">
                    @error('lieu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="0" {{ old('statut', $mission->statut) == 0 ? 'selected' : '' }}>Planifiee</option>
                        <option value="1" {{ old('statut', $mission->statut) == 1 ? 'selected' : '' }}>En cours</option>
                        <option value="2" {{ old('statut', $mission->statut) == 2 ? 'selected' : '' }}>Terminee</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="debut" class="form-label">Date de debut <span class="text-danger">*</span></label>
                    <input type="date" name="debut" id="debut" class="form-control @error('debut') is-invalid @enderror" value="{{ old('debut', $mission->debut?->format('Y-m-d')) }}" required>
                    @error('debut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="fin" class="form-label">Date de fin <span class="text-danger">*</span></label>
                    <input type="date" name="fin" id="fin" class="form-control @error('fin') is-invalid @enderror" value="{{ old('fin', $mission->fin?->format('Y-m-d')) }}" required>
                    @error('fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="budget" class="form-label">Budget previsionnel (XAF)</label>
                    <input type="number" step="0.01" min="0" name="budget" id="budget" class="form-control @error('budget') is-invalid @enderror" value="{{ old('budget', $mission->budget) }}">
                    @error('budget')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="frais_reels" class="form-label">Frais reels (XAF)</label>
                    <input type="number" step="0.01" min="0" name="frais_reels" id="frais_reels" class="form-control @error('frais_reels') is-invalid @enderror" value="{{ old('frais_reels', $mission->frais_reels) }}">
                    @error('frais_reels')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="introduction" class="form-label">Introduction</label>
                    <textarea name="introduction" id="introduction" class="form-control @error('introduction') is-invalid @enderror" rows="2">{{ old('introduction', $mission->introduction) }}</textarea>
                    @error('introduction')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description detaillee</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $mission->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.missions.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Mettre a jour
        </button>
    </div>
</form>
@endsection
