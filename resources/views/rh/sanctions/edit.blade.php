@extends('layouts.app')

@section('title', 'Modifier sanction')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.sanctions.index') }}">Sanctions disciplinaires</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Modifier la sanction</h1>
        <p class="text-muted mb-0">{{ $sanction->motif }} &mdash; {{ $sanction->employee?->noms }} {{ $sanction->employee?->prenoms }}</p>
    </div>
    <a href="{{ route('rh.sanctions.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.sanctions.update', $sanction) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-gavel me-2 text-muted"></i>Details de la sanction</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="employee_id" class="form-label">Employe</label>
                    <input type="hidden" name="employee_id" value="{{ $sanction->employee_id }}">

                    <select id="employee_id" class="form-select" disabled>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $sanction->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">L'employe ne peut pas etre modifie</small>
                </div>
                <div class="col-12 col-md-6">
                    <label for="type" class="form-label">Type de sanction <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="">-- Selectionner --</option>
                        @foreach(\App\Models\Sanction::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $sanction->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="motif" class="form-label">Motif <span class="text-danger">*</span></label>
                    <input type="text" name="motif" id="motif" class="form-control @error('motif') is-invalid @enderror" maxlength="255" value="{{ old('motif', $sanction->motif) }}" required>
                    @error('motif')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description detaillee</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $sanction->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_fait" class="form-label">Date des faits</label>
                    <input type="date" name="date_fait" id="date_fait" class="form-control @error('date_fait') is-invalid @enderror" value="{{ old('date_fait', $sanction->date_fait?->format('Y-m-d')) }}">
                    @error('date_fait')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_notification" class="form-label">Date de notification <span class="text-danger">*</span></label>
                    <input type="date" name="date_notification" id="date_notification" class="form-control @error('date_notification') is-invalid @enderror" value="{{ old('date_notification', $sanction->date_notification?->format('Y-m-d')) }}" required>
                    @error('date_notification')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_fin" class="form-label">Date de fin (mise a pied)</label>
                    <input type="date" name="date_fin" id="date_fin" class="form-control @error('date_fin') is-invalid @enderror" value="{{ old('date_fin', $sanction->date_fin?->format('Y-m-d')) }}">
                    @error('date_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="niveau_gravite" class="form-label">Niveau de gravite <span class="text-danger">*</span></label>
                    <select name="niveau_gravite" id="niveau_gravite" class="form-select @error('niveau_gravite') is-invalid @enderror" required>
                        <option value="">-- Selectionner --</option>
                        @foreach(\App\Models\Sanction::NIVEAUX as $key => $label)
                            <option value="{{ $key }}" {{ old('niveau_gravite', $sanction->niveau_gravite) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('niveau_gravite')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="1" {{ old('statut', $sanction->statut) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('statut', $sanction->statut) == 0 ? 'selected' : '' }}>Annulee</option>
                        <option value="2" {{ old('statut', $sanction->statut) == 2 ? 'selected' : '' }}>Archivee</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="reaction_employe" class="form-label">Reaction de l'employe</label>
                    <textarea name="reaction_employe" id="reaction_employe" class="form-control @error('reaction_employe') is-invalid @enderror" rows="3">{{ old('reaction_employe', $sanction->reaction_employe) }}</textarea>
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
            <i class="fas fa-save me-1"></i>Mettre a jour
        </button>
    </div>
</form>
@endsection
