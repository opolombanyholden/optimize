@extends('layouts.app')

@section('title', 'Nouvel ayant-droit')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.affilies.index') }}">Ayants-droit</a></li>
        <li class="breadcrumb-item active">Nouveau</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Nouvel ayant-droit</h1>
        <p class="text-muted mb-0">Enregistrer un nouvel ayant-droit (conjoint, enfant, parent...)</p>
    </div>
    <a href="{{ route('rh.affilies.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.affilies.store') }}" method="POST">
    @csrf

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-user-group me-2 text-muted"></i>Informations de l'ayant-droit</h5>
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
                    <label for="liens" class="form-label">Lien <span class="text-danger">*</span></label>
                    <select name="liens" id="liens" class="form-select @error('liens') is-invalid @enderror" required>
                        <option value="">-- Selectionner --</option>
                        <option value="Conjoint" {{ old('liens') == 'Conjoint' ? 'selected' : '' }}>Conjoint</option>
                        <option value="Enfant" {{ old('liens') == 'Enfant' ? 'selected' : '' }}>Enfant</option>
                        <option value="Parent" {{ old('liens') == 'Parent' ? 'selected' : '' }}>Parent</option>
                        <option value="Autre" {{ old('liens') == 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('liens')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="noms" class="form-label">Noms <span class="text-danger">*</span></label>
                    <input type="text" name="noms" id="noms" class="form-control @error('noms') is-invalid @enderror" value="{{ old('noms') }}" required>
                    @error('noms')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="prenoms" class="form-label">Prenoms</label>
                    <input type="text" name="prenoms" id="prenoms" class="form-control @error('prenoms') is-invalid @enderror" value="{{ old('prenoms') }}">
                    @error('prenoms')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_naissance" class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" id="date_naissance" class="form-control @error('date_naissance') is-invalid @enderror" value="{{ old('date_naissance') }}">
                    @error('date_naissance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="contact1" class="form-label">Contact principal</label>
                    <input type="text" name="contact1" id="contact1" class="form-control @error('contact1') is-invalid @enderror" value="{{ old('contact1') }}">
                    @error('contact1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="contact2" class="form-label">Contact secondaire</label>
                    <input type="text" name="contact2" id="contact2" class="form-control @error('contact2') is-invalid @enderror" value="{{ old('contact2') }}">
                    @error('contact2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="label" class="form-label">Libelle / Note</label>
                    <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label') }}">
                    @error('label')
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
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.affilies.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Enregistrer
        </button>
    </div>
</form>
@endsection
