@extends('layouts.app')

@section('title', 'Modifier rubrique de paie')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.rubriques.index') }}">Rubriques de paie</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Modifier la rubrique</h1>
        <p class="text-muted mb-0">{{ $rubrique->code }} &mdash; {{ $rubrique->libelle }}</p>
    </div>
    <a href="{{ route('rh.rubriques.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.rubriques.update', $rubrique) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-file-invoice-dollar me-2 text-muted"></i>Parametres de la rubrique</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $rubrique->code) }}" maxlength="20" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="libelle" class="form-label">Libelle <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" id="libelle" class="form-control @error('libelle') is-invalid @enderror" value="{{ old('libelle', $rubrique->libelle) }}" required>
                    @error('libelle')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label for="ordre_affichage" class="form-label">Ordre d'affichage</label>
                    <input type="number" min="0" name="ordre_affichage" id="ordre_affichage" class="form-control @error('ordre_affichage') is-invalid @enderror" value="{{ old('ordre_affichage', $rubrique->ordre_affichage) }}">
                    @error('ordre_affichage')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="gain" {{ old('type', $rubrique->type) == 'gain' ? 'selected' : '' }}>Gain</option>
                        <option value="retenue" {{ old('type', $rubrique->type) == 'retenue' ? 'selected' : '' }}>Retenue</option>
                        <option value="cotisation" {{ old('type', $rubrique->type) == 'cotisation' ? 'selected' : '' }}>Cotisation</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="base_calcul" class="form-label">Base de calcul <span class="text-danger">*</span></label>
                    <select name="base_calcul" id="base_calcul" class="form-select @error('base_calcul') is-invalid @enderror" required>
                        <option value="fixe" {{ old('base_calcul', $rubrique->base_calcul) == 'fixe' ? 'selected' : '' }}>Montant fixe</option>
                        <option value="pourcentage" {{ old('base_calcul', $rubrique->base_calcul) == 'pourcentage' ? 'selected' : '' }}>Pourcentage</option>
                        <option value="formule" {{ old('base_calcul', $rubrique->base_calcul) == 'formule' ? 'selected' : '' }}>Formule</option>
                    </select>
                    @error('base_calcul')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="1" {{ old('statut', $rubrique->statut) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ old('statut', $rubrique->statut) == 0 ? 'selected' : '' }}>Inactif</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Champs conditionnels --}}
                <div class="col-12 col-md-6 calcul-pourcentage" style="display:none;">
                    <label for="taux" class="form-label">Taux (%)</label>
                    <input type="number" step="0.01" min="0" name="taux" id="taux" class="form-control @error('taux') is-invalid @enderror" value="{{ old('taux', $rubrique->taux) }}">
                    @error('taux')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6 calcul-fixe" style="display:none;">
                    <label for="montant_fixe" class="form-label">Montant fixe (XAF)</label>
                    <input type="number" step="0.01" min="0" name="montant_fixe" id="montant_fixe" class="form-control @error('montant_fixe') is-invalid @enderror" value="{{ old('montant_fixe', $rubrique->montant_fixe) }}">
                    @error('montant_fixe')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 calcul-formule" style="display:none;">
                    <label for="formule" class="form-label">Formule de calcul</label>
                    <textarea name="formule" id="formule" class="form-control @error('formule') is-invalid @enderror" rows="3" placeholder="Ex: salaire_base * 0.05">{{ old('formule', $rubrique->formule) }}</textarea>
                    <small class="text-muted">Utilisez les variables disponibles (salaire_base, anciennete, ...)</small>
                    @error('formule')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-check mt-2">
                        <input type="hidden" name="imposable" value="0">
                        <input type="checkbox" name="imposable" id="imposable" value="1" class="form-check-input" {{ old('imposable', $rubrique->imposable) ? 'checked' : '' }}>
                        <label for="imposable" class="form-check-label">Imposable</label>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-check mt-2">
                        <input type="hidden" name="cotisable" value="0">
                        <input type="checkbox" name="cotisable" id="cotisable" value="1" class="form-check-input" {{ old('cotisable', $rubrique->cotisable) ? 'checked' : '' }}>
                        <label for="cotisable" class="form-check-label">Cotisable (soumis aux cotisations sociales)</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.rubriques.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Mettre a jour
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function() {
    const select = document.getElementById('base_calcul');
    const blocs = {
        pourcentage: document.querySelector('.calcul-pourcentage'),
        fixe: document.querySelector('.calcul-fixe'),
        formule: document.querySelector('.calcul-formule'),
    };
    function update() {
        Object.values(blocs).forEach(b => { if (b) b.style.display = 'none'; });
        if (blocs[select.value]) blocs[select.value].style.display = '';
    }
    select.addEventListener('change', update);
    update();
})();
</script>
@endpush
