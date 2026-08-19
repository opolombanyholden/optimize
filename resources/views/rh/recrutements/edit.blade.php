@extends('layouts.app')

@section('title', 'Modifier recrutement')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.recrutements.index') }}">Recrutements</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Modifier le recrutement</h1>
        <p class="text-muted mb-0">{{ $recrutement->label }}</p>
    </div>
    <a href="{{ route('rh.recrutements.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.recrutements.update', $recrutement) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-user-plus me-2 text-muted"></i>Informations du recrutement</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="label" class="form-label">Titre du poste <span class="text-danger">*</span></label>
                    <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $recrutement->label) }}" required>
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="debut" class="form-label">Date de publication</label>
                    <input type="date" name="debut" id="debut" class="form-control @error('debut') is-invalid @enderror" value="{{ old('debut', $recrutement->debut?->format('Y-m-d')) }}">
                    @error('debut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="fin" class="form-label">Date limite</label>
                    <input type="date" name="fin" id="fin" class="form-control @error('fin') is-invalid @enderror" value="{{ old('fin', $recrutement->fin?->format('Y-m-d')) }}">
                    @error('fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="0" {{ old('statut', $recrutement->statut) == 0 ? 'selected' : '' }}>Ouvert</option>
                        <option value="1" {{ old('statut', $recrutement->statut) == 1 ? 'selected' : '' }}>Ferme</option>
                        <option value="2" {{ old('statut', $recrutement->statut) == 2 ? 'selected' : '' }}>Annule</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-8">
                    <label for="introduction" class="form-label">Resume du poste</label>
                    <input type="text" name="introduction" id="introduction" class="form-control @error('introduction') is-invalid @enderror" value="{{ old('introduction', $recrutement->introduction) }}">
                    @error('introduction')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description detaillee</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="6">{{ old('description', $recrutement->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.recrutements.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Mettre a jour
        </button>
    </div>
</form>
@endsection
