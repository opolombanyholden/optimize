@extends('layouts.app')

@section('title', 'Nouvel evenement de carriere')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.evenements-carriere.index') }}">Evenements de carriere</a></li>
        <li class="breadcrumb-item active">Nouveau</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Nouvel evenement de carriere</h1>
        <p class="text-muted mb-0">Enregistrer un changement de poste, promotion, mutation...</p>
    </div>
    <a href="{{ route('rh.evenements-carriere.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.evenements-carriere.store') }}" method="POST">
    @csrf

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-arrow-trend-up me-2 text-muted"></i>Details de l'evenement</h5>
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
                    <label for="typesevenementscarriere_id" class="form-label">Type d'evenement <span class="text-danger">*</span></label>
                    <select name="typesevenementscarriere_id" id="typesevenementscarriere_id" class="form-select tom-select-rh @error('typesevenementscarriere_id') is-invalid @enderror" required>
                        <option value="">-- Selectionner --</option>
                        @foreach(\App\Models\TypeEvenementCarriere::actif()->orderBy('ordre')->orderBy('libelle')->get() as $type)
                            <option value="{{ $type->id }}" {{ old('typesevenementscarriere_id') == $type->id ? 'selected' : '' }}>{{ $type->libelle }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Géré dans <a href="{{ route('admin.referentiel-rh.index', 'types-evenement') }}" target="_blank">Admin › Référentiels</a></small>
                    @error('typesevenementscarriere_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-8">
                    <label for="libelle" class="form-label">Libelle <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" id="libelle" class="form-control @error('libelle') is-invalid @enderror" value="{{ old('libelle') }}" required>
                    @error('libelle')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_effet" class="form-label">Date d'effet <span class="text-danger">*</span></label>
                    <input type="date" name="date_effet" id="date_effet" class="form-control @error('date_effet') is-invalid @enderror" value="{{ old('date_effet') }}" required>
                    @error('date_effet')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @php $postesAct = \App\Models\Referentiel\Poste::actif()->orderBy('ordre')->orderBy('libelle')->get(); @endphp
                <div class="col-12 col-md-6">
                    <label for="ancien_poste" class="form-label">Ancien poste</label>
                    <select name="ancien_poste" id="ancien_poste" class="form-select tom-select-rh @error('ancien_poste') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        @foreach($postesAct as $p)
                            <option value="{{ $p->libelle }}" {{ old('ancien_poste') === $p->libelle ? 'selected' : '' }}>{{ $p->libelle }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Géré dans <a href="{{ route('admin.referentiel-rh.index', 'postes') }}" target="_blank">Admin › Référentiels</a></small>
                    @error('ancien_poste')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="nouveau_poste" class="form-label">Nouveau poste</label>
                    <select name="nouveau_poste" id="nouveau_poste" class="form-select tom-select-rh @error('nouveau_poste') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        @foreach($postesAct as $p)
                            <option value="{{ $p->libelle }}" {{ old('nouveau_poste') === $p->libelle ? 'selected' : '' }}>{{ $p->libelle }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Géré dans <a href="{{ route('admin.referentiel-rh.index', 'postes') }}" target="_blank">Admin › Référentiels</a></small>
                    @error('nouveau_poste')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description / Commentaire</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.evenements-carriere.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Enregistrer
        </button>
    </div>
</form>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select.tom-select-rh').forEach(function (el) {
        if (el.tomselect) return;
        new TomSelect(el, {
            allowEmptyOption: true,
            maxOptions: 500,
            create: false,
            render: {
                no_results: () => '<div class="no-results">Aucun résultat — gérez les options dans Admin › Référentiels</div>',
            },
        });
    });
});
</script>
@endpush
