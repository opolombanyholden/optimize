@extends('layouts.app')

@section('title', 'Modifier qualification')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.qualifications.index') }}">Qualifications</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Modifier la qualification</h1>
        <p class="text-muted mb-0">{{ $qualification->label }} &mdash; {{ $qualification->employee?->noms }} {{ $qualification->employee?->prenoms }}</p>
    </div>
    <a href="{{ route('rh.qualifications.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.qualifications.update', $qualification) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-graduation-cap me-2 text-muted"></i>Details de la qualification</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="employee_id" class="form-label">Employe</label>
                    <input type="hidden" name="employee_id" value="{{ $qualification->employee_id }}">

                    <select id="employee_id" class="form-select" disabled>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $qualification->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">L'employe ne peut pas etre modifie</small>
                </div>
                <div class="col-12 col-md-6">
                    <label for="label" class="form-label">Libelle <span class="text-danger">*</span></label>
                    <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $qualification->label) }}" required>
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    @php $niveauxAct = \App\Models\Referentiel\NiveauQualification::actif()->orderBy('ordre')->orderBy('libelle')->get(); @endphp
                    <label for="niveau" class="form-label">Niveau de qualification</label>
                    <select name="niveau" id="niveau" class="form-select tom-select-rh @error('niveau') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        @php $curNiveau = old('niveau', $qualification->niveau); @endphp
                        @foreach($niveauxAct as $n)
                            <option value="{{ $n->libelle }}" {{ $curNiveau === $n->libelle ? 'selected' : '' }}>{{ $n->libelle }}</option>
                        @endforeach
                        @if($curNiveau && !$niveauxAct->pluck('libelle')->contains($curNiveau))
                            <option value="{{ $curNiveau }}" selected>{{ $curNiveau }} (legacy)</option>
                        @endif
                    </select>
                    <small class="form-text text-muted">Géré dans <a href="{{ route('admin.referentiel-rh.index', 'niveaux-qualification') }}" target="_blank">Admin › Référentiels</a></small>
                    @error('niveau')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="organisme" class="form-label">Organisme</label>
                    <input type="text" name="organisme" id="organisme" class="form-control @error('organisme') is-invalid @enderror" value="{{ old('organisme', $qualification->organisme) }}">
                    @error('organisme')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="1" {{ old('statut', $qualification->statut) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ old('statut', $qualification->statut) == 0 ? 'selected' : '' }}>Inactif</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="debut" class="form-label">Date de debut</label>
                    <input type="datetime-local" name="debut" id="debut" class="form-control @error('debut') is-invalid @enderror" value="{{ old('debut', $qualification->debut?->format('Y-m-d\TH:i')) }}">
                    @error('debut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="fin" class="form-label">Date de fin</label>
                    <input type="datetime-local" name="fin" id="fin" class="form-control @error('fin') is-invalid @enderror" value="{{ old('fin', $qualification->fin?->format('Y-m-d\TH:i')) }}">
                    @error('fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="introduction" class="form-label">Introduction</label>
                    <textarea name="introduction" id="introduction" class="form-control @error('introduction') is-invalid @enderror" rows="2">{{ old('introduction', $qualification->introduction) }}</textarea>
                    @error('introduction')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description detaillee</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $qualification->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.qualifications.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Mettre a jour
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
