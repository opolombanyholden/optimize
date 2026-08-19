@extends('layouts.app')

@section('title', 'Nouveau solde de conges')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.conges-soldes.index') }}">Soldes de conges</a></li>
        <li class="breadcrumb-item active">Nouveau solde</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Nouveau solde de conges</h1>
        <p class="text-muted mb-0">Initialiser un compteur de conges pour un employe</p>
    </div>
    <a href="{{ route('rh.conges-soldes.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.conges-soldes.store') }}" method="POST">
    @csrf

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-umbrella-beach me-2 text-muted"></i>Compteur de conges</h5>
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
                <div class="col-12 col-md-3">
                    <label for="annee" class="form-label">Annee <span class="text-danger">*</span></label>
                    <select name="annee" id="annee" class="form-select @error('annee') is-invalid @enderror" required>
                        @for($a = 2024; $a <= 2027; $a++)
                            <option value="{{ $a }}" {{ old('annee', $annee ?? now()->year) == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endfor
                    </select>
                    @error('annee')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="type_conge" class="form-label">Type de conge <span class="text-danger">*</span></label>
                    <select name="type_conge" id="type_conge" class="form-select @error('type_conge') is-invalid @enderror" required>
                        <option value="">-- Selectionner --</option>
                        @foreach(\App\Models\CongeSolde::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ old('type_conge') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type_conge')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="droit_annuel" class="form-label">Droit annuel (jours) <span class="text-danger">*</span></label>
                    <input type="number" step="0.5" min="0" name="droit_annuel" id="droit_annuel" class="form-control @error('droit_annuel') is-invalid @enderror" value="{{ old('droit_annuel', 24) }}" required>
                    @error('droit_annuel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="report_n_moins_1" class="form-label">Report N-1 (jours)</label>
                    <input type="number" step="0.5" min="0" name="report_n_moins_1" id="report_n_moins_1" class="form-control @error('report_n_moins_1') is-invalid @enderror" value="{{ old('report_n_moins_1', 0) }}">
                    @error('report_n_moins_1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="acquis_periode" class="form-label">Acquis sur periode (jours)</label>
                    <input type="number" step="0.5" min="0" name="acquis_periode" id="acquis_periode" class="form-control @error('acquis_periode') is-invalid @enderror" value="{{ old('acquis_periode', 0) }}">
                    @error('acquis_periode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="pris_periode" class="form-label">Pris sur periode (jours)</label>
                    <input type="number" step="0.5" min="0" name="pris_periode" id="pris_periode" class="form-control @error('pris_periode') is-invalid @enderror" value="{{ old('pris_periode', 0) }}">
                    @error('pris_periode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="date_debut_acquisition" class="form-label">Debut de la periode d'acquisition</label>
                    <input type="date" name="date_debut_acquisition" id="date_debut_acquisition" class="form-control @error('date_debut_acquisition') is-invalid @enderror" value="{{ old('date_debut_acquisition') }}">
                    @error('date_debut_acquisition')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="date_fin_acquisition" class="form-label">Fin de la periode d'acquisition</label>
                    <input type="date" name="date_fin_acquisition" id="date_fin_acquisition" class="form-control @error('date_fin_acquisition') is-invalid @enderror" value="{{ old('date_fin_acquisition') }}">
                    @error('date_fin_acquisition')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.conges-soldes.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Enregistrer
        </button>
    </div>
</form>
@endsection
