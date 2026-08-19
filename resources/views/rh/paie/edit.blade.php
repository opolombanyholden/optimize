@extends('layouts.app')

@section('title', 'Modifier bulletin de paie')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.paie.index') }}">Paie</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Modifier le bulletin de paie</h1>
        <p class="text-muted mb-0">{{ $paie->label }} &mdash; {{ $paie->employee?->noms }} {{ $paie->employee?->prenoms }}</p>
    </div>
    <a href="{{ route('rh.paie.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.paie.update', $paie) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Informations generales --}}
    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-info-circle me-2 text-muted"></i>Informations generales</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="employee_id" class="form-label">Employe</label>
                    <input type="hidden" name="employee_id" value="{{ $paie->employee_id }}">

                    <select id="employee_id" class="form-select" disabled>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $paie->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">L'employe ne peut pas etre modifie</small>
                </div>
                <div class="col-12 col-md-6">
                    <label for="label" class="form-label">Libelle</label>
                    <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $paie->label) }}">
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="0" {{ old('statut', $paie->statut) == 0 ? 'selected' : '' }}>Brouillon</option>
                        <option value="1" {{ old('statut', $paie->statut) == 1 ? 'selected' : '' }}>Valide</option>
                        <option value="2" {{ old('statut', $paie->statut) == 2 ? 'selected' : '' }}>Paye</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Gains --}}
    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-arrow-up me-2 text-success"></i>Gains</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="salaire_base" class="form-label">Salaire de base</label>
                    <input type="number" name="salaire_base" id="salaire_base" class="form-control @error('salaire_base') is-invalid @enderror" value="{{ old('salaire_base', $paie->salaire_base) }}" step="0.01" min="0">
                    @error('salaire_base')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="primes" class="form-label">Primes</label>
                    <input type="number" name="primes" id="primes" class="form-control @error('primes') is-invalid @enderror" value="{{ old('primes', $paie->primes) }}" step="0.01" min="0">
                    @error('primes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="indemnites" class="form-label">Indemnites</label>
                    <input type="number" name="indemnites" id="indemnites" class="form-control @error('indemnites') is-invalid @enderror" value="{{ old('indemnites', $paie->indemnites) }}" step="0.01" min="0">
                    @error('indemnites')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="heures_sup" class="form-label">Heures supplementaires</label>
                    <input type="number" name="heures_sup" id="heures_sup" class="form-control @error('heures_sup') is-invalid @enderror" value="{{ old('heures_sup', $paie->heures_sup) }}" step="0.01" min="0">
                    @error('heures_sup')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Retenues --}}
    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-arrow-down me-2 text-danger"></i>Retenues</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="cotisations_salariales" class="form-label">Cotisations salariales</label>
                    <input type="number" name="cotisations_salariales" id="cotisations_salariales" class="form-control @error('cotisations_salariales') is-invalid @enderror" value="{{ old('cotisations_salariales', $paie->cotisations_salariales) }}" step="0.01" min="0">
                    @error('cotisations_salariales')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="cotisations_patronales" class="form-label">Cotisations patronales</label>
                    <input type="number" name="cotisations_patronales" id="cotisations_patronales" class="form-control @error('cotisations_patronales') is-invalid @enderror" value="{{ old('cotisations_patronales', $paie->cotisations_patronales) }}" step="0.01" min="0">
                    @error('cotisations_patronales')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="irpp" class="form-label">IRPP</label>
                    <input type="number" name="irpp" id="irpp" class="form-control @error('irpp') is-invalid @enderror" value="{{ old('irpp', $paie->irpp) }}" step="0.01" min="0">
                    @error('irpp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="avances" class="form-label">Avances</label>
                    <input type="number" name="avances" id="avances" class="form-control @error('avances') is-invalid @enderror" value="{{ old('avances', $paie->avances) }}" step="0.01" min="0">
                    @error('avances')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="retenues" class="form-label">Autres retenues</label>
                    <input type="number" name="retenues" id="retenues" class="form-control @error('retenues') is-invalid @enderror" value="{{ old('retenues', $paie->retenues) }}" step="0.01" min="0">
                    @error('retenues')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.paie.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Mettre a jour
        </button>
    </div>
</form>
@endsection
