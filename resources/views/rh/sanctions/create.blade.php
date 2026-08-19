@extends('layouts.app')

@section('title', 'Nouvelle sanction')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.sanctions.index') }}">Sanctions disciplinaires</a></li>
        <li class="breadcrumb-item active">Nouvelle sanction</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Nouvelle sanction</h1>
        <p class="text-muted mb-0">Enregistrer une sanction disciplinaire</p>
    </div>
    <a href="{{ route('rh.sanctions.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.sanctions.store') }}" method="POST">
    @csrf

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-gavel me-2 text-muted"></i>Details de la sanction</h5>
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
                    <label for="type" class="form-label">Type de sanction <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="">-- Selectionner --</option>
                        @foreach(\App\Models\Sanction::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="motif" class="form-label">Motif <span class="text-danger">*</span></label>
                    <input type="text" name="motif" id="motif" class="form-control @error('motif') is-invalid @enderror" maxlength="255" value="{{ old('motif') }}" required>
                    @error('motif')
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
                <div class="col-12 col-md-4">
                    <label for="date_fait" class="form-label">Date des faits</label>
                    <input type="date" name="date_fait" id="date_fait" class="form-control @error('date_fait') is-invalid @enderror" value="{{ old('date_fait') }}">
                    @error('date_fait')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_notification" class="form-label">Date de notification <span class="text-danger">*</span></label>
                    <input type="date" name="date_notification" id="date_notification" class="form-control @error('date_notification') is-invalid @enderror" value="{{ old('date_notification') }}" required>
                    @error('date_notification')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_fin" class="form-label">Date de fin (mise a pied)</label>
                    <input type="date" name="date_fin" id="date_fin" class="form-control @error('date_fin') is-invalid @enderror" value="{{ old('date_fin') }}">
                    @error('date_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="niveau_gravite" class="form-label">Niveau de gravite <span class="text-danger">*</span></label>
                    <select name="niveau_gravite" id="niveau_gravite" class="form-select @error('niveau_gravite') is-invalid @enderror" required>
                        <option value="">-- Selectionner --</option>
                        @foreach(\App\Models\Sanction::NIVEAUX as $key => $label)
                            <option value="{{ $key }}" {{ old('niveau_gravite') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('niveau_gravite')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="1" {{ old('statut', 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('statut') === '0' ? 'selected' : '' }}>Annulee</option>
                        <option value="2" {{ old('statut') === '2' ? 'selected' : '' }}>Archivee</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="reaction_employe" class="form-label">Reaction de l'employe</label>
                    <textarea name="reaction_employe" id="reaction_employe" class="form-control @error('reaction_employe') is-invalid @enderror" rows="3">{{ old('reaction_employe') }}</textarea>
                    @error('reaction_employe')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.sanctions.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Enregistrer
        </button>
    </div>
</form>
@endsection
