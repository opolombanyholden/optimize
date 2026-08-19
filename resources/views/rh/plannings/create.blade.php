@extends('layouts.app')

@section('title', 'Nouveau planning')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.plannings.index') }}">Plannings & Emplois du temps</a></li>
        <li class="breadcrumb-item active">Nouveau planning</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Nouveau planning</h1>
        <p class="text-muted mb-0">Programmer une journee de travail</p>
    </div>
    <a href="{{ route('rh.plannings.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.plannings.store') }}" method="POST">
    @csrf

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-calendar-week me-2 text-muted"></i>Details du planning</h5>
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
                    <label for="date_jour" class="form-label">Date du jour <span class="text-danger">*</span></label>
                    <input type="date" name="date_jour" id="date_jour" class="form-control @error('date_jour') is-invalid @enderror" value="{{ old('date_jour') }}" required>
                    @error('date_jour')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="type_journee" class="form-label">Type de journee <span class="text-danger">*</span></label>
                    <select name="type_journee" id="type_journee" class="form-select @error('type_journee') is-invalid @enderror" required>
                        @foreach(\App\Models\Planning::TYPES_JOURNEE as $key => $label)
                            <option value="{{ $key }}" {{ old('type_journee', 'travail') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type_journee')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="heure_debut" class="form-label">Heure debut</label>
                    <input type="time" name="heure_debut" id="heure_debut" class="form-control @error('heure_debut') is-invalid @enderror" value="{{ old('heure_debut') }}">
                    @error('heure_debut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="heure_fin" class="form-label">Heure fin</label>
                    <input type="time" name="heure_fin" id="heure_fin" class="form-control @error('heure_fin') is-invalid @enderror" value="{{ old('heure_fin') }}">
                    @error('heure_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="heure_debut_pause" class="form-label">Debut pause</label>
                    <input type="time" name="heure_debut_pause" id="heure_debut_pause" class="form-control @error('heure_debut_pause') is-invalid @enderror" value="{{ old('heure_debut_pause') }}">
                    @error('heure_debut_pause')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="heure_fin_pause" class="form-label">Fin pause</label>
                    <input type="time" name="heure_fin_pause" id="heure_fin_pause" class="form-control @error('heure_fin_pause') is-invalid @enderror" value="{{ old('heure_fin_pause') }}">
                    @error('heure_fin_pause')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="heures_prevues" class="form-label">Heures prevues</label>
                    <input type="number" step="0.25" min="0" name="heures_prevues" id="heures_prevues" class="form-control @error('heures_prevues') is-invalid @enderror" value="{{ old('heures_prevues', 8) }}">
                    @error('heures_prevues')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="heures_reelles" class="form-label">Heures reelles</label>
                    <input type="number" step="0.25" min="0" name="heures_reelles" id="heures_reelles" class="form-control @error('heures_reelles') is-invalid @enderror" value="{{ old('heures_reelles') }}">
                    @error('heures_reelles')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="lieu" class="form-label">Lieu</label>
                    <select name="lieu" id="lieu" class="form-select @error('lieu') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        <option value="bureau" {{ old('lieu') == 'bureau' ? 'selected' : '' }}>Bureau</option>
                        <option value="teletravail" {{ old('lieu') == 'teletravail' ? 'selected' : '' }}>Teletravail</option>
                        <option value="mission" {{ old('lieu') == 'mission' ? 'selected' : '' }}>Mission</option>
                    </select>
                    @error('lieu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="1" {{ old('statut', 1) == 1 ? 'selected' : '' }}>Planifie</option>
                        <option value="2" {{ old('statut') === '2' ? 'selected' : '' }}>Realise</option>
                        <option value="0" {{ old('statut') === '0' ? 'selected' : '' }}>Annule</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.plannings.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Enregistrer
        </button>
    </div>
</form>
@endsection
