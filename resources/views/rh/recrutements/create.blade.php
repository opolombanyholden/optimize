@extends('layouts.app')

@section('title', 'Nouveau recrutement')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.recrutements.index') }}">Recrutements</a></li>
        <li class="breadcrumb-item active">Nouveau recrutement</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Nouveau recrutement</h1>
        <p class="text-muted mb-0">Creer une nouvelle campagne de recrutement</p>
    </div>
    <a href="{{ route('rh.recrutements.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.recrutements.store') }}" method="POST">
    @csrf

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-user-plus me-2 text-muted"></i>Informations du recrutement</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label for="label" class="form-label">Titre du poste <span class="text-danger">*</span></label>
                    <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label') }}" placeholder="Ex: Developpeur Full Stack" required>
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="debut" class="form-label">Date de publication</label>
                    <input type="date" name="debut" id="debut" class="form-control @error('debut') is-invalid @enderror" value="{{ old('debut') }}">
                    @error('debut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="fin" class="form-label">Date limite</label>
                    <input type="date" name="fin" id="fin" class="form-control @error('fin') is-invalid @enderror" value="{{ old('fin') }}">
                    @error('fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-8">
                    <label for="introduction" class="form-label">Resume du poste</label>
                    <input type="text" name="introduction" id="introduction" class="form-control @error('introduction') is-invalid @enderror" value="{{ old('introduction') }}" placeholder="Bref resume du poste">
                    @error('introduction')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description detaillee</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="6" placeholder="Description complete du poste, missions, responsabilites, exigences, lieu de travail...">{{ old('description') }}</textarea>
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
            <i class="fas fa-save me-1"></i>Enregistrer
        </button>
    </div>
</form>
@endsection
