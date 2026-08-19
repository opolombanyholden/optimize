@extends('layouts.app')

@section('title', 'Nouveau bulletin de paie')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.paie.index') }}">Paie</a></li>
        <li class="breadcrumb-item active">Nouveau bulletin</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Nouveau bulletin de paie</h1>
        <p class="text-muted mb-0">Etablir un nouveau bulletin de salaire</p>
    </div>
    <a href="{{ route('rh.paie.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.paie.store') }}" method="POST">
    @csrf

    {{-- Informations generales --}}
    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-info-circle me-2 text-muted"></i>Informations generales</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="employee_id" class="form-label">Employe <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                        <option value="">-- Selectionner un employe --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }} {{ $employee->matricule ? '(' . $employee->matricule . ')' : '' }}</option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="label" class="form-label">Libelle <span class="text-danger">*</span></label>
                    <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label') }}" placeholder="Ex: Bulletin Mars 2026" required>
                    @error('label')
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
                    <label for="salaire_base" class="form-label">Salaire de base <span class="text-danger">*</span></label>
                    <input type="number" name="salaire_base" id="salaire_base" class="form-control @error('salaire_base') is-invalid @enderror" value="{{ old('salaire_base') }}" step="0.01" min="0" required>
                    @error('salaire_base')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="primes" class="form-label">Primes</label>
                    <input type="number" name="primes" id="primes" class="form-control @error('primes') is-invalid @enderror" value="{{ old('primes', 0) }}" step="0.01" min="0">
                    @error('primes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="indemnites" class="form-label">Indemnites</label>
                    <input type="number" name="indemnites" id="indemnites" class="form-control @error('indemnites') is-invalid @enderror" value="{{ old('indemnites', 0) }}" step="0.01" min="0">
                    @error('indemnites')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="heures_sup" class="form-label">Heures supplementaires</label>
                    <input type="number" name="heures_sup" id="heures_sup" class="form-control @error('heures_sup') is-invalid @enderror" value="{{ old('heures_sup', 0) }}" step="0.01" min="0">
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
                    <input type="number" name="cotisations_salariales" id="cotisations_salariales" class="form-control @error('cotisations_salariales') is-invalid @enderror" value="{{ old('cotisations_salariales', 0) }}" step="0.01" min="0">
                    @error('cotisations_salariales')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="cotisations_patronales" class="form-label">Cotisations patronales</label>
                    <input type="number" name="cotisations_patronales" id="cotisations_patronales" class="form-control @error('cotisations_patronales') is-invalid @enderror" value="{{ old('cotisations_patronales', 0) }}" step="0.01" min="0">
                    @error('cotisations_patronales')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="irpp" class="form-label">IRPP</label>
                    <input type="number" name="irpp" id="irpp" class="form-control @error('irpp') is-invalid @enderror" value="{{ old('irpp', 0) }}" step="0.01" min="0">
                    @error('irpp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="avances" class="form-label">Avances</label>
                    <input type="number" name="avances" id="avances" class="form-control @error('avances') is-invalid @enderror" value="{{ old('avances', 0) }}" step="0.01" min="0">
                    @error('avances')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="retenues" class="form-label">Autres retenues</label>
                    <input type="number" name="retenues" id="retenues" class="form-control @error('retenues') is-invalid @enderror" value="{{ old('retenues', 0) }}" step="0.01" min="0">
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
            <i class="fas fa-save me-1"></i>Enregistrer
        </button>
    </div>
</form>
@endsection
