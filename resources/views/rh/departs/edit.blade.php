@extends('layouts.app')

@section('title', 'Modifier depart')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.departs.index') }}">Departs / Sorties</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Modifier le depart</h1>
        <p class="text-muted mb-0">{{ $depart->employee?->noms }} {{ $depart->employee?->prenoms }}</p>
    </div>
    <a href="{{ route('rh.departs.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.departs.update', $depart) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-door-open me-2 text-muted"></i>Informations sur le depart</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="employee_id" class="form-label">Employe</label>
                    <input type="hidden" name="employee_id" value="{{ $depart->employee_id }}">

                    <select id="employee_id" class="form-select" disabled>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $depart->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">L'employe ne peut pas etre modifie</small>
                </div>
                <div class="col-12 col-md-6">
                    <label for="type_depart" class="form-label">Type de depart <span class="text-danger">*</span></label>
                    <select name="type_depart" id="type_depart" class="form-select @error('type_depart') is-invalid @enderror" required>
                        <option value="">-- Selectionner --</option>
                        @foreach(\App\Models\Depart::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ old('type_depart', $depart->type_depart) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type_depart')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="motif" class="form-label">Motif</label>
                    <input type="text" name="motif" id="motif" class="form-control @error('motif') is-invalid @enderror" value="{{ old('motif', $depart->motif) }}">
                    @error('motif')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description / Commentaires</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $depart->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-calendar-day me-2 text-muted"></i>Dates cles</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="date_notification" class="form-label">Date de notification <span class="text-danger">*</span></label>
                    <input type="date" name="date_notification" id="date_notification" class="form-control @error('date_notification') is-invalid @enderror" value="{{ old('date_notification', $depart->date_notification?->format('Y-m-d')) }}" required>
                    @error('date_notification')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_effet" class="form-label">Date d'effet <span class="text-danger">*</span></label>
                    <input type="date" name="date_effet" id="date_effet" class="form-control @error('date_effet') is-invalid @enderror" value="{{ old('date_effet', $depart->date_effet?->format('Y-m-d')) }}" required>
                    @error('date_effet')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_solde_tout_compte" class="form-label">Date solde de tout compte</label>
                    <input type="date" name="date_solde_tout_compte" id="date_solde_tout_compte" class="form-control @error('date_solde_tout_compte') is-invalid @enderror" value="{{ old('date_solde_tout_compte', $depart->date_solde_tout_compte?->format('Y-m-d')) }}">
                    @error('date_solde_tout_compte')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-money-bill-wave me-2 text-muted"></i>Aspects financiers</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="indemnite_depart" class="form-label">Indemnite de depart (XAF)</label>
                    <input type="number" step="0.01" min="0" name="indemnite_depart" id="indemnite_depart" class="form-control @error('indemnite_depart') is-invalid @enderror" value="{{ old('indemnite_depart', $depart->indemnite_depart) }}">
                    @error('indemnite_depart')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="solde_conges_paye" class="form-label">Solde de conges payes (jours)</label>
                    <input type="number" step="0.5" min="0" name="solde_conges_paye" id="solde_conges_paye" class="form-control @error('solde_conges_paye') is-invalid @enderror" value="{{ old('solde_conges_paye', $depart->solde_conges_paye) }}">
                    @error('solde_conges_paye')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-clipboard-check me-2 text-muted"></i>Procedure de sortie</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="form-check">
                        <input type="hidden" name="preavis_effectue" value="0">
                        <input class="form-check-input" type="checkbox" name="preavis_effectue" value="1" id="preavis_effectue" {{ old('preavis_effectue', $depart->preavis_effectue) ? 'checked' : '' }}>
                        <label class="form-check-label" for="preavis_effectue">Preavis effectue</label>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-check">
                        <input type="hidden" name="entretien_sortie_effectue" value="0">
                        <input class="form-check-input" type="checkbox" name="entretien_sortie_effectue" value="1" id="entretien_sortie_effectue" {{ old('entretien_sortie_effectue', $depart->entretien_sortie_effectue) ? 'checked' : '' }}>
                        <label class="form-check-label" for="entretien_sortie_effectue">Entretien de sortie effectue</label>
                    </div>
                </div>
                <div class="col-12">
                    <label for="notes_entretien_sortie" class="form-label">Notes de l'entretien de sortie</label>
                    <textarea name="notes_entretien_sortie" id="notes_entretien_sortie" class="form-control @error('notes_entretien_sortie') is-invalid @enderror" rows="3">{{ old('notes_entretien_sortie', $depart->notes_entretien_sortie) }}</textarea>
                    @error('notes_entretien_sortie')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="0" {{ old('statut', $depart->statut) == 0 ? 'selected' : '' }}>Annonce</option>
                        <option value="1" {{ old('statut', $depart->statut) == 1 ? 'selected' : '' }}>En cours</option>
                        <option value="2" {{ old('statut', $depart->statut) == 2 ? 'selected' : '' }}>Finalise</option>
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
        <a href="{{ route('rh.departs.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Mettre a jour
        </button>
    </div>
</form>
@endsection
