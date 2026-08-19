@extends('layouts.app')

@section('title', 'Modifier ayant-droit')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.affilies.index') }}">Ayants-droit</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Modifier l'ayant-droit</h1>
        <p class="text-muted mb-0">{{ $affilie->noms }} {{ $affilie->prenoms }} &mdash; {{ $affilie->employee?->noms }} {{ $affilie->employee?->prenoms }}</p>
    </div>
    <a href="{{ route('rh.affilies.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.affilies.update', $affilie) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-user-group me-2 text-muted"></i>Informations de l'ayant-droit</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="employee_id" class="form-label">Employe</label>
                    <input type="hidden" name="employee_id" value="{{ $affilie->employee_id }}">

                    <select id="employee_id" class="form-select" disabled>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $affilie->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">L'employe ne peut pas etre modifie</small>
                </div>
                <div class="col-12 col-md-6">
                    <label for="liens" class="form-label">Lien <span class="text-danger">*</span></label>
                    <select name="liens" id="liens" class="form-select @error('liens') is-invalid @enderror" required>
                        <option value="Conjoint" {{ old('liens', $affilie->liens) == 'Conjoint' ? 'selected' : '' }}>Conjoint</option>
                        <option value="Enfant" {{ old('liens', $affilie->liens) == 'Enfant' ? 'selected' : '' }}>Enfant</option>
                        <option value="Parent" {{ old('liens', $affilie->liens) == 'Parent' ? 'selected' : '' }}>Parent</option>
                        <option value="Autre" {{ old('liens', $affilie->liens) == 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('liens')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="noms" class="form-label">Noms <span class="text-danger">*</span></label>
                    <input type="text" name="noms" id="noms" class="form-control @error('noms') is-invalid @enderror" value="{{ old('noms', $affilie->noms) }}" required>
                    @error('noms')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="prenoms" class="form-label">Prenoms</label>
                    <input type="text" name="prenoms" id="prenoms" class="form-control @error('prenoms') is-invalid @enderror" value="{{ old('prenoms', $affilie->prenoms) }}">
                    @error('prenoms')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_naissance" class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" id="date_naissance" class="form-control @error('date_naissance') is-invalid @enderror" value="{{ old('date_naissance', $affilie->date_naissance?->format('Y-m-d')) }}">
                    @error('date_naissance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="contact1" class="form-label">Contact principal</label>
                    <input type="text" name="contact1" id="contact1" class="form-control @error('contact1') is-invalid @enderror" value="{{ old('contact1', $affilie->contact1) }}">
                    @error('contact1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="contact2" class="form-label">Contact secondaire</label>
                    <input type="text" name="contact2" id="contact2" class="form-control @error('contact2') is-invalid @enderror" value="{{ old('contact2', $affilie->contact2) }}">
                    @error('contact2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $affilie->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="label" class="form-label">Libelle / Note</label>
                    <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $affilie->label) }}">
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="1" {{ old('statut', $affilie->statut) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ old('statut', $affilie->statut) == 0 ? 'selected' : '' }}>Inactif</option>
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
            <i class="fas fa-save me-1"></i>Mettre a jour
        </button>
    </div>
</form>
@endsection
