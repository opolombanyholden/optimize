@extends('layouts.app')

@section('title', 'Modifier absence')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.absences.index') }}">Absences</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Modifier l'absence</h1>
        <p class="text-muted mb-0">{{ $absence->label }} &mdash; {{ $absence->employee?->noms }} {{ $absence->employee?->prenoms }}</p>
    </div>
    <a href="{{ route('rh.absences.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.absences.update', $absence) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-calendar-times me-2 text-muted"></i>Details de l'absence</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="employee_id" class="form-label">Employe</label>
                    <input type="hidden" name="employee_id" value="{{ $absence->employee_id }}">

                    <select id="employee_id" class="form-select" disabled>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $absence->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">L'employe ne peut pas etre modifie</small>
                </div>
                <div class="col-12 col-md-6">
                    <label for="label" class="form-label">Libelle <span class="text-danger">*</span></label>
                    <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $absence->label) }}" required>
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="type_abscence" class="form-label">Type d'absence</label>
                    <select name="type_abscence" id="type_abscence" class="form-select @error('type_abscence') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        <option value="Conge annuel" {{ old('type_abscence', $absence->type_abscence) == 'Conge annuel' ? 'selected' : '' }}>Conge annuel</option>
                        <option value="Maladie" {{ old('type_abscence', $absence->type_abscence) == 'Maladie' ? 'selected' : '' }}>Maladie</option>
                        <option value="Maternite" {{ old('type_abscence', $absence->type_abscence) == 'Maternite' ? 'selected' : '' }}>Maternite</option>
                        <option value="Formation" {{ old('type_abscence', $absence->type_abscence) == 'Formation' ? 'selected' : '' }}>Formation</option>
                        <option value="Autre" {{ old('type_abscence', $absence->type_abscence) == 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('type_abscence')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="debut" class="form-label">Date debut</label>
                    <input type="date" name="debut" id="debut" class="form-control @error('debut') is-invalid @enderror" value="{{ old('debut', $absence->debut?->format('Y-m-d')) }}">
                    @error('debut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="fin" class="form-label">Date fin</label>
                    <input type="date" name="fin" id="fin" class="form-control @error('fin') is-invalid @enderror" value="{{ old('fin', $absence->fin?->format('Y-m-d')) }}">
                    @error('fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="0" {{ old('statut', $absence->statut) == 0 ? 'selected' : '' }}>En attente</option>
                        <option value="1" {{ old('statut', $absence->statut) == 1 ? 'selected' : '' }}>Approuve</option>
                        <option value="2" {{ old('statut', $absence->statut) == 2 ? 'selected' : '' }}>Rejete</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Motif</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $absence->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.absences.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Mettre a jour
        </button>
    </div>
</form>
@endsection
