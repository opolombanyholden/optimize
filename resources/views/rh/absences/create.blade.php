@extends('layouts.app')

@section('title', 'Nouvelle absence')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.absences.index') }}">Absences</a></li>
        <li class="breadcrumb-item active">Nouvelle absence</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Nouvelle absence</h1>
        <p class="text-muted mb-0">Enregistrer une demande d'absence</p>
    </div>
    <a href="{{ route('rh.absences.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.absences.store') }}" method="POST">
    @csrf

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-calendar-times me-2 text-muted"></i>Details de l'absence</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="employee_id" class="form-label">Employe <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                        <option value="">-- Selectionner un employe --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="label" class="form-label">Libelle <span class="text-danger">*</span></label>
                    <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label') }}" required>
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="type_abscence" class="form-label">Type d'absence</label>
                    <select name="type_abscence" id="type_abscence" class="form-select @error('type_abscence') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        <option value="Conge annuel" {{ old('type_abscence') == 'Conge annuel' ? 'selected' : '' }}>Conge annuel</option>
                        <option value="Maladie" {{ old('type_abscence') == 'Maladie' ? 'selected' : '' }}>Maladie</option>
                        <option value="Maternite" {{ old('type_abscence') == 'Maternite' ? 'selected' : '' }}>Maternite</option>
                        <option value="Formation" {{ old('type_abscence') == 'Formation' ? 'selected' : '' }}>Formation</option>
                        <option value="Autre" {{ old('type_abscence') == 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('type_abscence')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="debut" class="form-label">Date debut <span class="text-danger">*</span></label>
                    <input type="date" name="debut" id="debut" class="form-control @error('debut') is-invalid @enderror" value="{{ old('debut') }}" required>
                    @error('debut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="fin" class="form-label">Date fin <span class="text-danger">*</span></label>
                    <input type="date" name="fin" id="fin" class="form-control @error('fin') is-invalid @enderror" value="{{ old('fin') }}" required>
                    @error('fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Motif</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
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
            <i class="fas fa-save me-1"></i>Enregistrer
        </button>
    </div>
</form>
@endsection
