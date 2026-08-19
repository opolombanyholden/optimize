@extends('layouts.app')

@section('title', 'Nouvelle evaluation')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.evaluations-performance.index') }}">Evaluations de performance</a></li>
        <li class="breadcrumb-item active">Nouvelle evaluation</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Nouvelle evaluation</h1>
        <p class="text-muted mb-0">Initier un cycle d'evaluation de performance</p>
    </div>
    <a href="{{ route('rh.evaluations-performance.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.evaluations-performance.store') }}" method="POST">
    @csrf

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-id-card me-2 text-muted"></i>Identite</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="employee_id" class="form-label">Employe evalue <span class="text-danger">*</span></label>
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
                    <label for="evaluateur_id" class="form-label">Evaluateur <span class="text-danger">*</span></label>
                    <select name="evaluateur_id" id="evaluateur_id" class="form-select @error('evaluateur_id') is-invalid @enderror" required>
                        <option value="">-- Selectionner --</option>
                        @foreach($evaluateurs as $eval)
                            <option value="{{ $eval->id }}" {{ old('evaluateur_id') == $eval->id ? 'selected' : '' }}>{{ $eval->name }}</option>
                        @endforeach
                    </select>
                    @error('evaluateur_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="periode" class="form-label">Periode <span class="text-danger">*</span></label>
                    <input type="text" name="periode" id="periode" class="form-control @error('periode') is-invalid @enderror" placeholder="Ex: 2026-S1" value="{{ old('periode') }}" required>
                    @error('periode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_evaluation" class="form-label">Date d'evaluation <span class="text-danger">*</span></label>
                    <input type="date" name="date_evaluation" id="date_evaluation" class="form-control @error('date_evaluation') is-invalid @enderror" value="{{ old('date_evaluation') }}" required>
                    @error('date_evaluation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_entretien" class="form-label">Date d'entretien</label>
                    <input type="date" name="date_entretien" id="date_entretien" class="form-control @error('date_entretien') is-invalid @enderror" value="{{ old('date_entretien') }}">
                    @error('date_entretien')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-star me-2 text-muted"></i>Notation</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="note_globale" class="form-label">Note globale</label>
                    <select name="note_globale" id="note_globale" class="form-select @error('note_globale') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        <option value="1" {{ old('note_globale') == 1 ? 'selected' : '' }}>1 etoile - Insuffisant</option>
                        <option value="2" {{ old('note_globale') == 2 ? 'selected' : '' }}>2 etoiles - A ameliorer</option>
                        <option value="3" {{ old('note_globale') == 3 ? 'selected' : '' }}>3 etoiles - Satisfaisant</option>
                        <option value="4" {{ old('note_globale') == 4 ? 'selected' : '' }}>4 etoiles - Tres bien</option>
                        <option value="5" {{ old('note_globale') == 5 ? 'selected' : '' }}>5 etoiles - Excellent</option>
                    </select>
                    @error('note_globale')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        @foreach(\App\Models\EvaluationPerformance::STATUTS as $key => $label)
                            <option value="{{ $key }}" {{ old('statut', 0) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-clipboard me-2 text-muted"></i>Bilan qualitatif</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label for="points_forts" class="form-label">Points forts</label>
                    <textarea name="points_forts" id="points_forts" class="form-control @error('points_forts') is-invalid @enderror" rows="3">{{ old('points_forts') }}</textarea>
                    @error('points_forts')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="axes_amelioration" class="form-label">Axes d'amelioration</label>
                    <textarea name="axes_amelioration" id="axes_amelioration" class="form-control @error('axes_amelioration') is-invalid @enderror" rows="3">{{ old('axes_amelioration') }}</textarea>
                    @error('axes_amelioration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="objectifs_periode_suivante" class="form-label">Objectifs pour la periode suivante</label>
                    <textarea name="objectifs_periode_suivante" id="objectifs_periode_suivante" class="form-control @error('objectifs_periode_suivante') is-invalid @enderror" rows="3">{{ old('objectifs_periode_suivante') }}</textarea>
                    @error('objectifs_periode_suivante')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="commentaire_employe" class="form-label">Commentaire de l'employe</label>
                    <textarea name="commentaire_employe" id="commentaire_employe" class="form-control @error('commentaire_employe') is-invalid @enderror" rows="3">{{ old('commentaire_employe') }}</textarea>
                    @error('commentaire_employe')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="commentaire_manager" class="form-label">Commentaire du manager</label>
                    <textarea name="commentaire_manager" id="commentaire_manager" class="form-control @error('commentaire_manager') is-invalid @enderror" rows="3">{{ old('commentaire_manager') }}</textarea>
                    @error('commentaire_manager')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-graduation-cap me-2 text-muted"></i>Plan de developpement individuel (PDI)</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label for="plan_developpement_individuel" class="form-label">PDI</label>
                    <textarea name="plan_developpement_individuel" id="plan_developpement_individuel" class="form-control @error('plan_developpement_individuel') is-invalid @enderror" rows="4">{{ old('plan_developpement_individuel') }}</textarea>
                    @error('plan_developpement_individuel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-signature me-2 text-muted"></i>Signatures</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="form-check">
                        <input type="hidden" name="signature_employe" value="0">
                        <input class="form-check-input" type="checkbox" name="signature_employe" value="1" id="signature_employe" {{ old('signature_employe') ? 'checked' : '' }}>
                        <label class="form-check-label" for="signature_employe">Signature de l'employe</label>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-check">
                        <input type="hidden" name="signature_manager" value="0">
                        <input class="form-check-input" type="checkbox" name="signature_manager" value="1" id="signature_manager" {{ old('signature_manager') ? 'checked' : '' }}>
                        <label class="form-check-label" for="signature_manager">Signature du manager</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.evaluations-performance.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Enregistrer
        </button>
    </div>
</form>
@endsection
