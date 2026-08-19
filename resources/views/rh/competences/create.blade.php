@extends('layouts.app')

@section('title', 'Nouvelle competence')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.competences.index') }}">Competences</a></li>
        <li class="breadcrumb-item active">Nouvelle</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Nouvelle competence</h1>
        <p class="text-muted mb-0">Enregistrer une nouvelle competence pour un employe</p>
    </div>
    <a href="{{ route('rh.competences.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.competences.store') }}" method="POST">
    @csrf

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-brain me-2 text-muted"></i>Details de la competence</h5>
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
                    <label for="niveau" class="form-label">Niveau</label>
                    <select name="niveau" id="niveau" class="form-select @error('niveau') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        <option value="debutant" {{ old('niveau') == 'debutant' ? 'selected' : '' }}>Debutant</option>
                        <option value="intermediaire" {{ old('niveau') == 'intermediaire' ? 'selected' : '' }}>Intermediaire</option>
                        <option value="avance" {{ old('niveau') == 'avance' ? 'selected' : '' }}>Avance</option>
                        <option value="expert" {{ old('niveau') == 'expert' ? 'selected' : '' }}>Expert</option>
                    </select>
                    @error('niveau')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="debut" class="form-label">Date de debut</label>
                    <input type="datetime-local" name="debut" id="debut" class="form-control @error('debut') is-invalid @enderror" value="{{ old('debut') }}">
                    @error('debut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="fin" class="form-label">Date de fin</label>
                    <input type="datetime-local" name="fin" id="fin" class="form-control @error('fin') is-invalid @enderror" value="{{ old('fin') }}">
                    @error('fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="1" {{ old('statut', 1) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ old('statut') === '0' ? 'selected' : '' }}>Inactif</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="introduction" class="form-label">Introduction</label>
                    <textarea name="introduction" id="introduction" class="form-control @error('introduction') is-invalid @enderror" rows="2">{{ old('introduction') }}</textarea>
                    @error('introduction')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description detaillee</label>
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
        <a href="{{ route('rh.competences.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Enregistrer
        </button>
    </div>
</form>
@endsection
